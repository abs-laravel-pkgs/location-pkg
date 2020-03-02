<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CityU1 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('cities', function (Blueprint $table) {
			$table->unsignedInteger('sub_district_id')->nullable()->after('state_id');
			$table->foreign('sub_district_id')->references('id')->on('sub_districts')->onDelete('CASCADE')->onUpdate('cascade');
			$table->unique(["name", "sub_district_id"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {

		Schema::table('cities', function (Blueprint $table) {
			$table->dropForeign('cities_sub_district_id_foreign');
			$table->dropUnique('cities_name_sub_district_id_unique');
			$table->dropColumn('sub_district_id');
		});

	}
}
