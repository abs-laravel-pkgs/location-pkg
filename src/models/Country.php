<?php

namespace Abs\LocationPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model {
	use SeederTrait;
	use SoftDeletes;

	protected $table = 'countries';
	protected $fillable = [
		'code',
		'name',
	];

	public static function getCountries() {
		$query = Country::select('id', 'code', 'name', 'has_state_list')->orderBy('name');
		$country_list = $query->get();

		return $country_list;
	}

	public static function createMultipleFromArray($records) {
		foreach ($records as $key => $data) {
			try {
				$data = $data->toArray();
				$record = self::firstOrNew([
					'code' => $data['code'],
				]);
				$record->fill($data);
				$record->save();
			} catch (\Exception $e) {
				dump($data, $e->getMessage());
			}
		}
	}

	public static function createFromId($company_id) {
		$company = self::find($company_id);
		if ($company) {
			dd('Company already exists');
		}

		$record = new self([
			'id' => $company_id,
		]);
		$record->code = 'c' . $company_id;
		$record->name = 'Company ' . $company_id;
		$record->address = 'SL No :10, Jawahar Road, Chokkikulam, Madurai';
		$record->cin_number = 'C' . $company_id . 'CIN1';
		$record->gst_number = 'C' . $company_id . 'GST1';
		$record->customer_care_email = 'customer-care@c' . $company_id;
		$record->customer_care_phone = $company_id . '0000000001';
		$record->reference_code = $record->code;
		$record->save();

		$record->createDefaultAdmin($record->customer_care_phone);
		return $record;
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
