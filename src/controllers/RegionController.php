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

class RegionController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getRegionFilter() {
		$this->data['state_list'] = collect(State::select('id', 'name')->get()->prepend(['id' => '', 'name' => 'Select State']));
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function getRegionPkgList(Request $request) {
		$regions = Region::withTrashed()
			->select(
				'regions.id',
				'regions.code',
				'regions.name',
				'states.name as state_name',
				'states.code as state_code',
				DB::raw('IF(regions.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->leftJoin('states', 'regions.state_id', 'states.id')
			->where('regions.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->region_code)) {
					$query->where('regions.code', 'LIKE', '%' . $request->region_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->region_name)) {
					$query->where('regions.name', 'LIKE', '%' . $request->region_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->filter_state_id)) {
					$query->where('regions.state_id', $request->filter_state_id);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('states.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('states.deleted_at');
				}
			})
			->orderby('regions.id', 'desc');

		return Datatables::of($regions)
			->addColumn('name', function ($region) {
				$status = $region->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $region->name;
			})
			->addColumn('action', function ($region) {
				$edit = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$edit_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$view = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$view_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');

				$action = '';
				if (Entrust::can('edit-region')) {
					$action .= '<a href="#!/location-pkg/region/edit/' . $region->id . '">
						<img src="' . $edit . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $edit_active . '" onmouseout=this.src="' . $edit . '" >
					</a>';
				}
				if (Entrust::can('view-region')) {
					$action .= '<a href="#!/location-pkg/region/view/' . $region->id . '">
						<img src="' . $view . '" alt="View" class="img-responsive" onmouseover=this.src="' . $view_active . '" onmouseout=this.src="' . $view . '" >
					</a>';

				}
				if (Entrust::can('delete-region')) {
					$action .= '<a href="javascript:;" data-toggle="modal" data-target="#delete_region"
					onclick="angular.element(this).scope().deleteRegion(' . $region->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete . '" alt="Delete" class="img-responsive" onmouseover=this.src="' . $delete_active . '" onmouseout=this.src="' . $delete . '" >
					</a>
					';
				}
				return $action;
			})
			->make(true);
	}

	public function getRegionFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$region = new Region;
			$action = 'Add';
		} else {
			$region = Region::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['state_list'] = $state_list = Collect(State::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select State']);
		$this->data['region'] = $region;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function viewRegionPkg(Request $request) {
		$this->data['region'] = Region::withTrashed()->with([
			'state',
		])->find($request->id);
		$this->data['action'] = 'View';

		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveRegionPkg(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Region Code is Required',
				'code.max' => 'Region Code Maximum 4 Characters',
				'code.min' => 'Region Code Minimum 1 Characters',
				'code.unique' => 'Region Code is already taken',
				'name.required' => 'Region Name is Required',
				'name.max' => 'Region Name Maximum 191 Characters',
				'name.min' => 'Region Name Minimum 3 Characters',
				'name.unique' => 'Region Name is already taken',
				'state_id.required' => 'State is Required',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required:true',
					'max:4',
					'min:1',
					// 'unique:regions,code,' . $request->id . ',id,state_id,' . $request->state_id,
					'unique:regions,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',state_id,' . $request->state_id,
				],
				'name' => [
					'required:true',
					'max:191',
					'min:3',
					// 'unique:regions,name,' . $request->id . ',id,state_id,' . $request->state_id,
					'unique:regions,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',state_id,' . $request->state_id,
				],
				'state_id' => 'required',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$region = new Region;
				$region->created_by_id = Auth::user()->id;
				$region->created_at = Carbon::now();
				$region->updated_at = NULL;
			} else {
				$region = Region::withTrashed()->find($request->id);
				$region->updated_by_id = Auth::user()->id;
				$region->updated_at = Carbon::now();
			}
			$region->fill($request->all());
			$region->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$region->deleted_at = Carbon::now();
				$region->deleted_by_id = Auth::user()->id;

			} else {
				$region->deleted_by_id = NULL;
				$region->deleted_at = NULL;
			}
			$region->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Region Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Region Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteRegionPkg(Request $request) {
		$delete_status = Region::withTrashed()->where('id', $request->id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}
