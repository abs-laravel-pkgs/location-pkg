<?php

namespace Abs\LocationPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use Abs\LocationPkg\State;
use App\Config;
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
		return $this->belongsTo('Abs\LocationPkg\State');
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

		$type = Config::where('name', $record_data->type)->where('config_type_id', 89)->first();
		if (!$type) {
			$errors[] = 'Invalid Tax Type : ' . $record_data->type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->tax_name,
		]);
		$record->type_id = $type->id;
		$record->created_by_id = $admin->id;
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
}
