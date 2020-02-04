<?php
namespace Abs\LocationPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class LocationPkgPermissionSeeder extends Seeder {
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
				'name' => 'edit-country',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'countries',
				'name' => 'view-country',
				'display_name' => 'View',
			],
			[
				'display_order' => 4,
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
				'name' => 'edit-state',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'states',
				'name' => 'view-state',
				'display_name' => 'View',
			],
			[
				'display_order' => 4,
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
				'name' => 'edit-city',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'cities',
				'name' => 'view-city',
				'display_name' => 'View',
			],
			[
				'display_order' => 4,
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
				'name' => 'edit-region',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'regions',
				'name' => 'view-region',
				'display_name' => 'View',
			],
			[
				'display_order' => 4,
				'parent' => 'regions',
				'name' => 'delete-region',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}