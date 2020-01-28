<?php

namespace Abs\LocationPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model {
	use SeederTrait;
	use SoftDeletes;

	protected $table = 'states';
	protected $fillable = [
		'code',
		'name',
		'country_id',
	];

	public function country() {
		return $this->belongsTo('Abs\LocationPkg\Country');
	}
	public function region() {
		return $this->hasMany('Abs\LocationPkg\Region');
	}

	public static function getStates($params) {
		$query = State::select('id', 'code', 'name', 'country_id')->orderBy('name');
		if ($params['country_id']) {
			$query->where('country_id', $params['country_id']);
		}
		$state_list = $query->get();

		return $state_list;
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
