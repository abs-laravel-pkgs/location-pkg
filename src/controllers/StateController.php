<?php

namespace Abs\LocationPkg;
use Abs\LocationPkg\Region;
use Abs\LocationPkg\State;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class StateController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getStateFilter() {
		$this->data['country_list'] = collect(Country::select('id', 'name')->get()->prepend(['id' => '', 'name' => 'Select Country']));
		return response()->json($this->data);
	}

	public function getStateList(Request $request) {
		$states = State::withTrashed()
			->select(
				'states.id',
				'states.name',
				'states.code',
				'countries.name as country_name',
				'countries.code as country_code',
				DB::raw('COUNT(regions.id) as regions'),
				DB::raw('IF(states.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->join('countries', 'states.country_id', 'countries.id')
			->leftjoin('regions', 'states.id', 'regions.state_id')
			->where(function ($query) use ($request) {
				if (!empty($request->state_code)) {
					$query->where('states.code', 'LIKE', '%' . $request->state_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->state_name)) {
					$query->where('states.name', 'LIKE', '%' . $request->state_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->country_id)) {
					$query->where('states.country_id', $request->country_id);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('states.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('states.deleted_at');
				}
			})
			->groupBy('states.id')
			->orderBy('states.id', 'desc')
		// ->get()
		;

		return Datatables::of($states)
			->addColumn('name', function ($state) {
				$status = $state->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $state->name;
			})
			->addColumn('action', function ($state) {
				$edit = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$edit_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$view = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$view_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');

				$action = '';
				if (Entrust::can('edit-state')) {
					$action .= '<a href="#!/location-pkg/state/edit/' . $state->id . '">
						<img src="' . $edit . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $edit_active . '" onmouseout=this.src="' . $edit . '" >
					</a>';
				}
				if (Entrust::can('view-state')) {
					$action .= '<a href="#!/location-pkg/state/view/' . $state->id . '">
						<img src="' . $view . '" alt="View" class="img-responsive" onmouseover=this.src="' . $view_active . '" onmouseout=this.src="' . $view . '" >
					</a>';

				}
				if (Entrust::can('delete-state')) {
					$action .= '<a href="javascript:;" data-toggle="modal" data-target="#delete_state"
					onclick="angular.element(this).scope().deleteState(' . $state->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete . '" alt="Delete" class="img-responsive" onmouseover=this.src="' . $delete_active . '" onmouseout=this.src="' . $delete . '" >
					</a>
					';
				}
				return $action;
			})
			->make(true);
	}

	public function getStateFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$state = new State;
			$action = 'Add';
			$this->data['region_list'] = [];
		} else {
			$state = State::withTrashed()->find($id);
			$action = 'Edit';
			$this->data['region_list'] = Region::withTrashed()->where('state_id', $request->id)->get();
		}
		$this->data['country_list'] = collect(Country::select('id', 'name')->get()->prepend(['id' => '', 'name' => 'Select Country']));
		$this->data['state'] = $state;
		$this->data['theme'];
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function viewState(Request $request) {
		$this->data['state'] = $state = State::withTrashed()->with([
			'country',
		])->find($request->id);
		$this->data['regions'] = $regions = Region::withTrashed()->where('state_id', $request->id)->get();
		$this->data['action'] = 'View';
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveState(Request $request) {
		// dd($request->all());
		try {
			if (!empty($request->removed_region_id)) {
				$remove_region_ids = json_decode($request->removed_region_id);
				$remove_region = Region::withTrashed()->whereIn('id', $remove_region_ids)->forceDelete();
			}

			//VALIDATION FOR UNIQUE
			if (!empty($request->regions)) {
				$regions_name = array_column($request->regions, 'name');
				$regions_code = array_column($request->regions, 'code');
				$regions_name_count = count($regions_name);
				$region_name_unique_count = count(array_unique($regions_name));
				$regions_code_count = count($regions_code);
				$region_code_unique_count = count(array_unique($regions_code));
				if (($regions_name_count != $region_name_unique_count) || ($regions_code_count != $region_code_unique_count)) {
					return response()->json(['success' => false, 'errors' => ['Remove Duplicate Value!']]);
				}
			}

			$error_messages = [
				'code.required' => 'State Code is Required',
				'code.max' => 'State Code Maximum 3 Characters',
				'code.min' => 'State Code Minimum 1 Characters',
				'code.unique' => 'State Code is already taken',
				'name.required' => 'State Name is Required',
				'name.max' => 'State Name Maximum 191 Characters',
				'name.min' => 'State Name Minimum 3 Characters',
				'name.unique' => 'State Name is already taken',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required:true',
					'max:3',
					'min:1',
					'unique:states,code,' . $request->id . ',id,country_id,' . $request->country_id,
				],
				'name' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:states,name,' . $request->id . ',id,country_id,' . $request->country_id,
				],
				'country_id' => 'required',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			//REGIONS VALIDATION
			$error_messages1 = [
				'code.required' => 'Region Code is Required',
				'code.max' => 'Region Code Maximum 4 Characters',
				'code.min' => 'Region Code Minimum 1 Characters',
				'code.unique' => 'Region Code is already taken',
				'name.required' => 'Region Name is Required',
				'name.max' => 'Region Name Maximum 191 Characters',
				'name.min' => 'Region Name Minimum 3 Characters',
				'name.unique' => 'Region Name is already taken',
			];
			if (!empty($request->regions)) {
				foreach ($request->regions as $region) {
					$validator = Validator::make($region, [
						'code' => [
							'required:true',
							'max:4',
							'min:1',
							'unique:regions,code,' . $region['id'] . ',id,state_id,' . $request->id,
							'unique:regions,code,' . $region['id'] . ',id,state_id,' . $request->id . ',company_id,' . Auth::user()->company_id,
						],
						'name' => [
							'required:true',
							'max:191',
							'min:3',
							'unique:regions,name,' . $region['id'] . ',id,state_id,' . $request->id,
							'unique:regions,name,' . $region['id'] . ',id,state_id,' . $request->id . ',company_id,' . Auth::user()->company_id,
						],
					], $error_messages1);
					if ($validator->fails()) {
						return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
					}
				}
			}

			DB::beginTransaction();
			if (!$request->id) {
				$state = new State;
				$state->created_by_id = Auth::user()->id;
				$state->created_at = Carbon::now();
				$state->updated_at = NULL;
			} else {
				$state = State::withTrashed()->find($request->id);
				$state->updated_by_id = Auth::user()->id;
				$state->updated_at = Carbon::now();
			}
			$state->fill($request->all());
			if ($request->status == 'Inactive') {
				$state->deleted_at = Carbon::now();
				$state->deleted_by_id = Auth::user()->id;
			} else {
				$state->deleted_by_id = NULL;
				$state->deleted_at = NULL;
			}
			$state->save();

			//ADD REGIONS
			// dd($request->regions);
			if (!empty($request->regions)) {
				foreach ($request->regions as $region_data) {

					if (!$region_data['id']) {
						$region = new Region;
						$region->created_by_id = Auth::user()->id;
						$region->created_at = Carbon::now();
						$region->updated_at = NULL;
					} else {
						$region = Region::withTrashed()->find($region_data['id']);
						$region->updated_by_id = Auth::user()->id;
						$region->updated_at = Carbon::now();
					}
					if ($region_data['status'] == 'Inactive') {
						$region->deleted_by_id = Auth::user()->id;
						$region->deleted_at = Carbon::now();
					} else {
						$region->deleted_by_id = NULL;
						$region->deleted_at = NULL;
					}
					$region->name = $region_data['name'];
					$region->code = $region_data['code'];
					$region->company_id = Auth::user()->company_id;
					$region->state_id = $state->id;
					$region->save();
				}
			}

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['State Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['State Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteState(Request $request) {
		$delete_status = State::withTrashed()->where('id', $request->id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}
