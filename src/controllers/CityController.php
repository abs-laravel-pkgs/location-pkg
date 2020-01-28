<?php

namespace Abs\LocationPkg;
use Abs\LocationPkg\City;
use Abs\LocationPkg\State;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class CityController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getCityFilter() {
		$this->data['state_list'] = collect(State::select('id', 'name')->get()->prepend(['id' => '', 'name' => 'Select State']));
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function getCityList(Request $request) {
		$cities = City::withTrashed()
			->select(
				'cities.id',
				'cities.name',
				'states.name as state_name',
				'states.code as state_code',
				'countries.name as country_name',
				'countries.code as country_code',
				DB::raw('IF(cities.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->join('states', 'cities.state_id', 'states.id')
			->leftJoin('countries', 'states.country_id', 'countries.id')
			->where(function ($query) use ($request) {
				if (!empty($request->city_name)) {
					$query->where('cities.name', 'LIKE', '%' . $request->city_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->state_id)) {
					$query->where('cities.state_id', $request->state_id);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('states.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('states.deleted_at');
				}
			})
			->orderby('cities.id', 'desc');

		return Datatables::of($cities)
			->addColumn('name', function ($city) {
				$status = $city->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $city->name;
			})
			->addColumn('action', function ($city) {
				$edit = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$edit_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$view = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$view_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');

				$action = '';
				if (Entrust::can('edit-city')) {
					$action .= '<a href="#!/location-pkg/city/edit/' . $city->id . '">
						<img src="' . $edit . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $edit_active . '" onmouseout=this.src="' . $edit . '" >
					</a>';
				}
				if (Entrust::can('view-city')) {
					$action .= '<a href="#!/location-pkg/city/view/' . $city->id . '">
						<img src="' . $view . '" alt="View" class="img-responsive" onmouseover=this.src="' . $view_active . '" onmouseout=this.src="' . $view . '" >
					</a>';

				}
				if (Entrust::can('delete-city')) {
					$action .= '<a href="javascript:;" data-toggle="modal" data-target="#delete_city"
					onclick="angular.element(this).scope().deleteCity(' . $city->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete . '" alt="Delete" class="img-responsive" onmouseover=this.src="' . $delete_active . '" onmouseout=this.src="' . $delete . '" >
					</a>
					';
				}
				return $action;
			})
			->make(true);
	}

	public function getCityFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$city = new City;
			$action = 'Add';
		} else {
			$city = City::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['state_list'] = $state_list = Collect(State::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select State']);
		$this->data['city'] = $city;
		$this->data['theme'];
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function viewCity(Request $request) {
		$this->data['city'] = $city = City::withTrashed()->with([
			'state',
		])->find($request->id);
		$this->data['action'] = 'View';
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveCity(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'City Name is Required',
				'name.max' => 'City Name Maximum 191 Characters',
				'name.min' => 'City Name Minimum 3 Characters',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:cities,name,' . $request->id . ',id,state_id,' . $request->state_id,
				],
				'state_id' => 'required',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$city = new City;
				$city->created_by_id = Auth::user()->id;
				$city->created_at = Carbon::now();
				$city->updated_at = NULL;
			} else {
				$city = City::withTrashed()->find($request->id);
				$city->updated_by_id = Auth::user()->id;
				$city->updated_at = Carbon::now();
			}
			$city->fill($request->all());
			if ($request->status == 'Inactive') {
				$city->deleted_at = Carbon::now();
				$city->deleted_by_id = Auth::user()->id;
			} else {
				$city->deleted_by_id = NULL;
				$city->deleted_at = NULL;
			}
			$city->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['City Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['City Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteCity(Request $request) {
		$delete_status = City::withTrashed()->where('id', $request->id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}
