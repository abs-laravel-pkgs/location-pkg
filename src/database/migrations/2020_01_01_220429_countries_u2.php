<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CountriesU2 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('countries', function (Blueprint $table) {
			$table->string('mobile_code', 10)->nullable()->after('has_state_list');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('countries', function (Blueprint $table) {
			$table->dropColumn('mobile_code');
		});
	}
}
