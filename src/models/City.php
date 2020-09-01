<?php

namespace Abs\LocationPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
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
		return $this->belongsTo('App\State');
	}

	public static function getCities($params) {
		$query = City::select('id', 'name', 'state_id')->orderBy('name');
		if ($params['state_id']) {
			$query->where('state_id', $params['state_id']);
		}
		$city_list = collect($query->get()->prepend(['id' => '', 'name' => 'Select City']));

		return $city_list;
	}

	public static function searchCity($r) {
		$key = $r->key;
		$state = $r->state;
		$list = self::with(['state'])
			->select(
				'id',
				'name',
				'state_id'
			)
			->where(function ($q) use ($key) {
				$q->where('name', 'like', $key . '%')
				;
			});
		if ($state != "") {
			$list = $list->where('state_id', $state);
		}
		$list = $list->get();
		return response()->json($list);
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
			'state_id' => $state->id,
			'name' => $record_data->name,
		]);
		$record->save();
		return $record;
	}

	public static function getDropDownList($params = [], $add_default = true, $default_text = 'Select City') {
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
}
