<?php
use Illuminate\Database\Seeder;

class ExcelLocationImportSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$this->call(Abs\LocationPkg\Database\Seeds\ExcelLocationImportPkgSeeder::class);
	}
}