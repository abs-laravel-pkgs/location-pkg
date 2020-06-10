<?php

namespace Abs\LocationPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use Abs\LocationPkg\State;
use App\Company;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'regions';
	public $timestamps = true;
	protected $fillable = [
		'code',
		'name',
		'company_id',
		'state_id',
	];

	protected $appends = ['switch_value'];

	public function getSwitchValueAttribute() {
		return !empty($this->attributes['deleted_at']) ? 'Inactive' : 'Active';
	}

	public function state() {
		return $this->belongsTo('App\State');
	}

	public static function createFromObject($record_data) {

		$errors = [];
		$company = Company::where('code', $record_data->company)->first();
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$errors = [];
		$state = State::where('name', $record_data->state)->first();
		if (!$state) {
			$errors[] = 'Invalid state : ' . $record_data->state;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'code' => $record_data->code,
		]);
		$record->name = $record_data->region_name;
		$record->state_id = $state->id;
		$record->save();
		return $record;
	}

	public static function getRegions($request) {
		$state_id = State::find($request->id);
		// dd($state_id);
		if (!$state_id) {
			return response()->json(['success' => false, 'error' => 'State not found']);
		}
		$regions = collect(Region::where('state_id', $state_id->id)->where('company_id', Auth::user()->company_id)->select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Region']);
		return response()->json(['success' => true, 'regions' => $regions]);
	}

	public static function getDropDownList($params = [], $add_default = true, $default_text = 'Select Region') {
		$list = Collect(Self::select([
			'id',
			'name',
		])
				->where(function ($q) use ($params) {
					if (isset($params['state_id'])) {
						$q->where('state_id', $params['state_id']);
					}
				})
				->orderBy('name')
				->get());
		if ($add_default) {
			$list->prepend(['id' => '', 'name' => $default_text]);
		}
		return $list;
	}
	public static function createFromCollection($records) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company) {
					continue;
				}
				$record = self::createFromObject($record_data);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}
}
