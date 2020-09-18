<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StatesU26082020 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('states', function (Blueprint $table) {
			$table->string('e_invoice_state_code', 3)->nullable()->after('code');

			$table->unique(["country_id", "e_invoice_state_code"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('states', function (Blueprint $table) {
			$table->dropColumn('e_invoice_state_code');
		});
	}
}
