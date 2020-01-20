<?php

namespace Abs\LocationPkg;
use Abs\LocationPkg\Country;
use App\Address;
use App\Country;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class CountryController extends Controller {

	public function __construct() {
	}

	public function getCountryList(Request $request) {
		$states = Country::withTrashed()
			->select(
				'states.id',
				'states.code',
				'states.name',
				DB::raw('IF(states.mobile_no IS NULL,"--",states.mobile_no) as mobile_no'),
				DB::raw('IF(states.email IS NULL,"--",states.email) as email'),
				DB::raw('IF(states.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->where('states.company_id', Auth::user()->company_id)
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
				if (!empty($request->mobile_no)) {
					$query->where('states.mobile_no', 'LIKE', '%' . $request->mobile_no . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->email)) {
					$query->where('states.email', 'LIKE', '%' . $request->email . '%');
				}
			})
			->orderby('states.id', 'desc');

		return Datatables::of($states)
			->addColumn('code', function ($state) {
				$status = $state->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $state->code;
			})
			->addColumn('action', function ($state) {
				$edit_img = asset('public/theme/img/table/cndn/edit.svg');
				$delete_img = asset('public/theme/img/table/cndn/delete.svg');
				return '
					<a href="#!/location-pkg/state/edit/' . $state->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_state"
					onclick="angular.element(this).scope().deleteCountry(' . $state->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getCountryFormData($id = NULL) {
		if (!$id) {
			$state = new Country;
			$address = new Address;
			$action = 'Add';
		} else {
			$state = Country::withTrashed()->find($id);
			$address = Address::where('address_of_id', 24)->where('entity_id', $id)->first();
			if (!$address) {
				$address = new Address;
			}
			$action = 'Edit';
		}
		$this->data['country_list'] = $country_list = Collect(Country::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Country']);
		$this->data['state'] = $state;
		$this->data['address'] = $address;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveCountry(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Country Code is Required',
				'code.max' => 'Maximum 255 Characters',
				'code.min' => 'Minimum 3 Characters',
				'code.unique' => 'Country Code is already taken',
				'name.required' => 'Country Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
				'gst_number.required' => 'GST Number is Required',
				'gst_number.max' => 'Maximum 191 Numbers',
				'mobile_no.max' => 'Maximum 25 Numbers',
				// 'email.required' => 'Email is Required',
				'address_line1.required' => 'Address Line 1 is Required',
				'address_line1.max' => 'Maximum 255 Characters',
				'address_line1.min' => 'Minimum 3 Characters',
				'address_line2.max' => 'Maximum 255 Characters',
				// 'pincode.required' => 'Pincode is Required',
				// 'pincode.max' => 'Maximum 6 Characters',
				// 'pincode.min' => 'Minimum 6 Characters',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required:true',
					'max:255',
					'min:3',
					'unique:states,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'name' => 'required|max:255|min:3',
				'gst_number' => 'required|max:191',
				'mobile_no' => 'nullable|max:25',
				// 'email' => 'nullable',
				'address' => 'required',
				'address_line1' => 'required|max:255|min:3',
				'address_line2' => 'max:255',
				// 'pincode' => 'required|max:6|min:6',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$state = new Country;
				$state->created_by_id = Auth::user()->id;
				$state->created_at = Carbon::now();
				$state->updated_at = NULL;
				$address = new Address;
			} else {
				$state = Country::withTrashed()->find($request->id);
				$state->updated_by_id = Auth::user()->id;
				$state->updated_at = Carbon::now();
				$address = Address::where('address_of_id', 24)->where('entity_id', $request->id)->first();
			}
			$state->fill($request->all());
			$state->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$state->deleted_at = Carbon::now();
				$state->deleted_by_id = Auth::user()->id;
			} else {
				$state->deleted_by_id = NULL;
				$state->deleted_at = NULL;
			}
			$state->gst_number = $request->gst_number;
			$state->axapta_location_id = $request->axapta_location_id;
			$state->save();

			if (!$address) {
				$address = new Address;
			}
			$address->fill($request->all());
			$address->company_id = Auth::user()->company_id;
			$address->address_of_id = 24;
			$address->entity_id = $state->id;
			$address->address_type_id = 40;
			$address->name = 'Primary Address';
			$address->save();

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
	public function deleteCountry($id) {
		$delete_status = Country::withTrashed()->where('id', $id)->forceDelete();
		if ($delete_status) {
			$address_delete = Address::where('address_of_id', 24)->where('entity_id', $id)->forceDelete();
			return response()->json(['success' => true]);
		}
	}
}
