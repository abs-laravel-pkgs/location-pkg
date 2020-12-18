<?php

namespace Abs\LocationPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model {
	use SeederTrait;
	use SoftDeletes;

	protected $table = 'states';
	protected $fillable = [
		'code',
		'name',
		'e_invoice_state_code',
		'cess_on_gst_coa_code',
		'country_id',
	];

	protected $appends = ['switch_value'];

	public function getSwitchValueAttribute() {
		return !empty($this->attributes['deleted_at']) ? 'Inactive' : 'Active';
	}

	public function country() {
		return $this->belongsTo('App\Country');
	}
	public function regions() {
		return $this->hasMany('App\Region');
	}

	public function cities() {
		return $this->hasMany('App\City');
	}

	public static function getStates($params) {
		$query = State::select('id', 'code', 'name', 'country_id')->orderBy('name');
		if ($params['country_id']) {
			$query->where('country_id', $params['country_id']);
		}
		$state_list = collect($query->get()->prepend(['id' => '', 'name' => 'Select State']));

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

		$errors = [];

		$country = Country::where('code', $record_data->country)->first();
		if (!$country) {
			$errors[] = 'Invalid country : ' . $record_data->country;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'country_id' => $country->id,
			'code' => $record_data->code,
		]);
		$record->name = $record_data->state_name;
		$record->save();
		return $record;
	}

	public static function getDropDownList($params = [], $add_default = true, $default_text = 'Select State') {
		$list = Collect(Self::select([
			'id',
			'name',
		])
				->where(function ($q) use ($params) {
					if (isset($params['country_id'])) {
						$q->where('country_id', $params['country_id']);
					}
				})
				->orderBy('name')
				->get());
		if ($add_default) {
			$list->prepend(['id' => '', 'name' => $default_text]);
		}
		return $list;
	}

}
