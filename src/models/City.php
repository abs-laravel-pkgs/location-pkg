<?php

namespace Abs\LocationPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model {
	use SeederTrait;
	use SoftDeletes;

	protected $table = 'cities';
	protected $fillable = [
		'name',
		'state_id',
	];

	protected $appends = ['switch_value'];

	public function getSwitchValueAttribute() {
		return !empty($this->attributes['deleted_at']) ? 'Inactive' : 'Active';
	}

	public function state() {
		return $this->belongsTo('Abs\LocationPkg\State');
	}

	public static function getCities($params) {
		$query = City::select('id', 'name', 'state_id')->orderBy('name');
		if ($params['state_id']) {
			$query->where('state_id', $params['state_id']);
		}
		$city_list = collect($query->get()->prepend(['id' => '', 'name' => 'Select City']));

		return $city_list;
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
}
