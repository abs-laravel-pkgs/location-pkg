<?php

namespace Abs\LocationPkg;
use Abs\LocationPkg\Country;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class CountryController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getCountryList(Request $request) {
		$countries = Country::withTrashed()
			->select(
				'countries.id',
				'countries.code',
				'countries.name',
				DB::raw('IF(countries.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->where(function ($query) use ($request) {
				if (!empty($request->country_code)) {
					$query->where('countries.code', 'LIKE', '%' . $request->country_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->country_name)) {
					$query->where('countries.name', 'LIKE', '%' . $request->country_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('countries.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('countries.deleted_at');
				}
			})
			->orderby('countries.id', 'desc');

		return Datatables::of($countries)
			->addColumn('name', function ($countries) {
				$status = $countries->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $countries->name;
			})
			->addColumn('action', function ($countries) {
				$edit = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$edit_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$view = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$view_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');

				$action = '';
				if (Entrust::can('edit-country')) {
					$action .= '<a href="#!/location-pkg/country/edit/' . $countries->id . '">
						<img src="' . $edit . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $edit_active . '" onmouseout=this.src="' . $edit . '" >
					</a>';
				}
				if (Entrust::can('view-country')) {
					$action .= '<a href="#!/location-pkg/country/view/' . $countries->id . '">
						<img src="' . $view . '" alt="View" class="img-responsive" onmouseover=this.src="' . $view_active . '" onmouseout=this.src="' . $view . '" >
					</a>';

				}
				if (Entrust::can('delete-country')) {
					$action .= '<a href="javascript:;" data-toggle="modal" data-target="#delete_country"
					onclick="angular.element(this).scope().deleteCountry(' . $countries->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete . '" alt="Delete" class="img-responsive" onmouseover=this.src="' . $delete_active . '" onmouseout=this.src="' . $delete . '" >
					</a>
					';
				}
				return $action;
			})
			->make(true);
	}

	public function getCountryFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$country = new Country;
			$action = 'Add';
		} else {
			$country = Country::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['country'] = $country;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function viewCountry(Request $request) {
		$this->data['country'] = $country = Country::withTrashed()->find($request->id);
		$this->data['action'] = 'View';
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveCountry(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Country Code is Required',
				'code.max' => 'Maximum 3 Characters',
				'code.min' => 'Minimum 1 Characters',
				'code.unique' => 'Country Code is already taken',
				'name.required' => 'Country Name is Required',
				'name.max' => 'Maximum 64 Characters',
				'name.min' => 'Minimum 3 Characters',
				'name.unique' => 'Country Name is already taken',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required:true',
					'max:3',
					'min:1',
					'unique:countries,code,' . $request->id . ',id',
				],
				'name' => [
					'required:true',
					'max:64',
					'min:3',
					'unique:countries,name,' . $request->id . ',id',
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$country = new Country;
				$country->created_by_id = Auth::user()->id;
				$country->created_at = Carbon::now();
				$country->updated_at = NULL;
			} else {
				$country = Country::withTrashed()->find($request->id);
				$country->updated_by_id = Auth::user()->id;
				$country->updated_at = Carbon::now();
			}
			$country->fill($request->all());
			if ($request->status == 'Inactive') {
				$country->deleted_at = Carbon::now();
				$country->deleted_by_id = Auth::user()->id;
			} else {
				$country->deleted_by_id = NULL;
				$country->deleted_at = NULL;
			}
			$country->iso_code = $request->code;
			$country->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Country Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Country Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteCountry(Request $request) {
		$delete_status = Country::withTrashed()->where('id', $request->id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}
