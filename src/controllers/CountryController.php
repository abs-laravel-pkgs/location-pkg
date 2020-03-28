<?php

namespace Abs\LocationPkg;
use Abs\LocationPkg\Country;
use App\ActivityLog;
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

	public function getCountries(Request $r) {
		// $validator = Validator::make($r->all(), [
		// ]);
		// if ($validator->fails()) {
		// 	return response()->json([
		// 		'success' => false,
		// 		'error' => 'Validation errors',
		// 		'errors' => $validator->errors(),
		// 	], $this->successStatus);
		// }

		$this->data['success'] = 'true';
		$this->data['country_list'] = Country::getCountries();

		return response()->json($this->data);
	}

	public function getCountryPkgList(Request $request) {
		$countries = Country::withTrashed()->select(
			'countries.id',
			'countries.code',
			'countries.name',
			DB::raw('COALESCE(countries.iso_code,"--") as iso_code'),
			DB::raw('COALESCE(countries.mobile_code,"--") as mobile_code'),
			DB::raw('COUNT(states.id) as states'),
			DB::raw('IF(countries.deleted_at IS NULL,"Active","Inactive") as status')
		)
			->leftJoin('states', 'countries.id', 'states.country_id')
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
				if (!empty($request->iso_code)) {
					$query->where('countries.iso_code', 'LIKE', '%' . $request->iso_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('countries.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('countries.deleted_at');
				}
			})
			->groupBy('countries.id')
		// ->orderBy('countries.id', 'desc')
		;

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
					$action .= '<a href="#!/location-pkg/country/edit/' . $countries->id . '" title=""Edit>
						<img src="' . $edit . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $edit_active . '" onmouseout=this.src="' . $edit . '" >
					</a>';
				}
				if (Entrust::can('view-country')) {
					$action .= '<a href="#!/location-pkg/country/view/' . $countries->id . '" title="View">
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

	public function getCountryFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$country = new Country;
			$action = 'Add';
			$state_list = [];
		} else {
			$country = Country::withTrashed()->find($id);
			$action = 'Edit';
			$state_list = State::withTrashed()->where('country_id', $id)->get();
		}
		$this->data['country'] = $country;
		$this->data['state_list'] = $state_list;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function viewCountryPkg(Request $request) {
		$this->data['country'] = $country = Country::withTrashed()->find($request->id);
		$this->data['state_list'] = State::withTrashed()->where('country_id', $request->id)->get();
		$this->data['action'] = 'View';
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function savePkgCountry(Request $request) {
		// dd($request->all());
		try {
			if (!empty($request->removed_state_id)) {
				$remove_state_ids = json_decode($request->removed_state_id);
				$remove_state = State::withTrashed()->whereIn('id', $remove_state_ids)->forceDelete();
			}

			//VALIDATION FOR UNIQUE
			if (!empty($request->states)) {
				$states_name = array_column($request->states, 'name');
				$states_code = array_column($request->states, 'code');
				$states_name_count = count(array_map('strtolower', $states_name));
				$state_name_unique_count = count(array_unique(array_map('strtolower', $states_name)));
				$states_code_count = count(array_map('strtolower', $states_code));
				$state_code_unique_count = count(array_unique(array_map('strtolower', $states_code)));
				if (($states_name_count != $state_name_unique_count) || ($states_code_count != $state_code_unique_count)) {
					return response()->json(['success' => false, 'errors' => ['Remove Duplicate Value in States!']]);
				}
			}
			if ($this->data['theme'] == 'theme2') {
				$error_messages = [
					'code.required' => 'Country Code is Required',
					'code.max' => 'Country Code Maximum 2 Characters',
					'code.min' => 'Country Code Minimum 1 Characters',
					'code.unique' => 'Country Code is already taken',
					'name.required' => 'Country Name is Required',
					'name.max' => 'Country Name Maximum 64 Characters',
					'name.min' => 'Country Name Minimum 3 Characters',
					'name.unique' => 'Country Name is already taken',
					'iso_code.required' => 'ISO Code is Required',
					'iso_code.max' => 'ISO Code Maximum 3 Characters',
					'iso_code.min' => 'ISO Code Minimum 1 Characters',
					'iso_code.unique' => 'ISO Code is already taken',
					'mobile_code.max' => 'Mobile Code Maximum 10 Characters',
				];
				$validator = Validator::make($request->all(), [
					'code' => [
						'required:true',
						'max:2',
						'min:1',
						'unique:countries,code,' . $request->id . ',id',
					],
					'name' => [
						'required:true',
						'max:64',
						'min:3',
						'unique:countries,name,' . $request->id . ',id',
					],
					// 'iso_code' => [
					// 	'required:true',
					// 	'max:3',
					// 	'min:1',
					// 	'unique:countries,iso_code,' . $request->id . ',id',
					// ],
					// 'mobile_code' => 'nullable|max:10',
				], $error_messages);
				if ($validator->fails()) {
					return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
				}
			} else {
				$error_messages = [
					'code.required' => 'Country Code is Required',
					'code.max' => 'Country Code Maximum 2 Characters',
					'code.min' => 'Country Code Minimum 1 Characters',
					'code.unique' => 'Country Code is already taken',
					'name.required' => 'Country Name is Required',
					'name.max' => 'Country Name Maximum 64 Characters',
					'name.min' => 'Country Name Minimum 3 Characters',
					'name.unique' => 'Country Name is already taken',
					'iso_code.required' => 'ISO Code is Required',
					'iso_code.max' => 'ISO Code Maximum 3 Characters',
					'iso_code.min' => 'ISO Code Minimum 1 Characters',
					'iso_code.unique' => 'ISO Code is already taken',
					'mobile_code.max' => 'Mobile Code Maximum 10 Characters',
				];
				$validator = Validator::make($request->all(), [
					'code' => [
						'required:true',
						'max:2',
						'min:1',
						'unique:countries,code,' . $request->id . ',id',
					],
					'name' => [
						'required:true',
						'max:64',
						'min:3',
						'unique:countries,name,' . $request->id . ',id',
					],
					'iso_code' => [
						'required:true',
						'max:3',
						'min:1',
						'unique:countries,iso_code,' . $request->id . ',id',
					],
					'mobile_code' => 'nullable|max:10',
				], $error_messages);
				if ($validator->fails()) {
					return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
				}
			}

			$error_messages1 = [
				'code.required' => 'State Code is Required',
				'code.max' => 'State Code Maximum 2 Characters',
				'code.min' => 'State Code Minimum 1 Characters',
				'code.unique' => 'State Code is already taken',
				'name.required' => 'State Name is Required',
				'name.max' => 'State Name Maximum 191 Characters',
				'name.min' => 'State Name Minimum 3 Characters',
				'name.unique' => 'State Name is already taken',
			];
			if (!empty($request->states)) {
				foreach ($request->states as $state) {
					$validator = Validator::make($state, [
						'code' => [
							'required:true',
							'min:1',
							'max:2',
							'unique:states,code,' . $state['id'] . ',id,country_id,' . $request->id,
						],
						'name' => [
							'required:true',
							'min:3',
							'max:191',
							'unique:states,name,' . $state['id'] . ',id,country_id,' . $request->id,
						],
					], $error_messages1);
					if ($validator->fails()) {
						return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
					}
				}
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
			$country->iso_code = $request->iso_code;
			$country->has_state_list = $request->has_state_list;
			$country->mobile_code = $request->mobile_code;

			$country->fill($request->all());
			if ($request->status == 'Inactive') {
				$country->deleted_at = Carbon::now();

				$country->deleted_by_id = Auth::user()->id;

			} else {

				$country->deleted_by_id = NULL;

				$country->deleted_at = NULL;
			}
			$country->save();

			if (!empty($request->states)) {
				foreach ($request->states as $state_data) {
					if (!$state_data['id']) {
						$state = new State;

						$state->created_by_id = Auth::user()->id;

						$state->created_at = Carbon::now();
						$state->deleted_at = NULL;
					} else {
						$state = State::withTrashed()->find($state_data['id']);
						$state->updated_by_id = Auth::user()->id;
						$state->updated_at = Carbon::now();
					}
					if ($state_data['status'] == 'Inactive') {
						$state->deleted_by_id = Auth::user()->id;
						$state->deleted_at = Carbon::now();
					} else {
						$state->deleted_by_id = NULL;
						$state->deleted_at = NULL;
					}
					$state->country_id = $country->id;
					$state->name = $state_data['name'];
					$state->code = $state_data['code'];
					$state->save();

					$activity = new ActivityLog;
					$activity->date_time = Carbon::now();
					$activity->user_id = Auth::user()->id;
					$activity->module = 'State Master';
					$activity->entity_id = $state->id;
					$activity->entity_type_id = 363;
					$activity->activity_id = $request->id == NULL ? 280 : 281;
					$activity->activity = $request->id == NULL ? 280 : 281;
					$activity->details = json_encode($activity);
					$activity->save();
				}
			}

			$activity = new ActivityLog;
			$activity->date_time = Carbon::now();
			$activity->user_id = Auth::user()->id;
			$activity->module = 'Country Master';
			$activity->entity_id = $country->id;
			$activity->entity_type_id = 362;
			$activity->activity_id = $request->id == NULL ? 280 : 281;
			$activity->activity = $request->id == NULL ? 280 : 281;
			$activity->details = json_encode($activity);
			$activity->save();

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
	public function deleteCountryPkg(Request $request) {
		$delete_status = Country::withTrashed()->where('id', $request->id)->forceDelete();
		if ($delete_status) {
			$activity = new ActivityLog;
			$activity->date_time = Carbon::now();
			$activity->user_id = Auth::user()->id;
			$activity->module = 'Country Master';
			$activity->entity_id = $request->id;
			$activity->entity_type_id = 362;
			$activity->activity_id = 282;
			$activity->activity = 282;
			$activity->details = json_encode($activity);
			$activity->save();
			return response()->json(['success' => true]);
		}
	}
}
