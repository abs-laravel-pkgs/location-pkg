<?php
namespace Abs\LocationPkg\Database\Seeds;

use Abs\LocationPkg\City;
use Abs\LocationPkg\Country;
use Abs\LocationPkg\District;
use Abs\LocationPkg\State;
use Abs\LocationPkg\SubDistrict;
use Excel;
use Illuminate\Database\Seeder;

class ExcelLocationImportPkgSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		// dd('dshjdgsfhjdsg');
		$states = Excel::selectSheetsByIndex(0)->load('public/excel-imports/locations.xlsx', function ($reader) {
			$reader->limitRows(100);
			$reader->limitColumns(3);
			$records = $reader->get();
			foreach ($records as $key => $record) {
				try {
					$errors = [];
					if (empty($record->name)) {
						$errors[] = 'State Name is Empty';
					}
					if (empty($record->code)) {
						$errors[] = 'State Code is Empty';
					} else {
						$state = strlen($record->code);
						if ($state > 2) {
							$errors[] = 'State Code should not exceed more than 2 Character: ' . $record->code;
						}
					}
					if (empty($record->country)) {
						$errors[] = 'Country is Empty';
					} else {
						$country = Country::where([
							'code' => $record->country,
						])->first();
						if (!$country) {
							$errors[] = 'Country Code not found: ' . $record->country;
						}
					}

					if (!empty($errors)) {
						dump($key + 1, $errors, $record);
						continue;
					}

					$state = State::firstOrNew([
						'code' => $record->code,
					]);
					$state->country_id = $country->id;
					$state->name = $record->name;
					$state->code = $record->code;
					$state->save();

				} catch (\Exception $e) {
					dump($record, $e->getMessage());
				}
			}
		});

		$cities = Excel::selectSheetsByIndex(1)->load('public/excel-imports/locations.xlsx', function ($reader) {
			$reader->limitRows(6000);
			$reader->limitColumns(4);
			$records = $reader->get();
			foreach ($records as $key => $record) {
				try {
					$errors = [];
					if (empty($record->state)) {
						$errors[] = 'State is Empty';
					} else {
						$state = strlen($record->state);
						if ($state > 2) {
							$errors[] = 'State Code should not exceed more than 2 Character: ' . $record->state;
						}
						$states = State::where([
							'code' => $record->state,
						])->first();
						if (!$states) {
							$errors[] = 'State Code not found: ' . $record->state;
						}
					}
					if (empty($record->district)) {
						$errors[] = 'District is Empty';
					}
					if (empty($record->sub_district)) {
						$errors[] = 'Sub District is Empty';
					}
					if (empty($record->name)) {
						$errors[] = 'Name is Empty';
					}

					if (!empty($errors)) {
						dump($key + 1, $errors, $record);
						continue;
					}
					//District
					$district = District::firstOrNew([
						'name' => $record->district,
					]);
					$district->state_id = $states->id;
					$district->name = $record->district;
					$district->save();

					//Sub-District
					$sub_district = SubDistrict::firstOrNew([
						'name' => $record->sub_district,
					]);
					$sub_district->district_id = $district->id;
					$sub_district->name = $record->sub_district;
					$sub_district->save();

					//Cities
					$cities = City::firstOrNew([
						'name' => $record->name,
					]);
					$cities->name = $record->name;
					$cities->state_id = $states->id;
					$cities->sub_district_id = $sub_district->id;
					$cities->save();

				} catch (\Exception $e) {
					dump($record, $e->getMessage());
				}
			}
		});
	}
}