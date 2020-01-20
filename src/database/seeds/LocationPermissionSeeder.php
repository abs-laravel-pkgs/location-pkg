<?php
namespace Abs\LocationPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class StatePermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//COUNTRY
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'countries',
				'display_name' => 'Countries',
			],
			[
				'display_order' => 1,
				'parent' => 'countries',
				'name' => 'add-country',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'countries',
				'name' => 'delete-country',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'countries',
				'name' => 'delete-country',
				'display_name' => 'Delete',
			],

			//STATE
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'states',
				'display_name' => 'States',
			],
			[
				'display_order' => 1,
				'parent' => 'states',
				'name' => 'add-state',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'states',
				'name' => 'delete-state',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'states',
				'name' => 'delete-state',
				'display_name' => 'Delete',
			],

			//CITY
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'cities',
				'display_name' => 'Cities',
			],
			[
				'display_order' => 1,
				'parent' => 'cities',
				'name' => 'add-city',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'cities',
				'name' => 'delete-city',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'cities',
				'name' => 'delete-city',
				'display_name' => 'Delete',
			],

			//REGION
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'regions',
				'display_name' => 'Regions',
			],
			[
				'display_order' => 1,
				'parent' => 'regions',
				'name' => 'add-region',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'regions',
				'name' => 'delete-region',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'regions',
				'name' => 'delete-region',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}