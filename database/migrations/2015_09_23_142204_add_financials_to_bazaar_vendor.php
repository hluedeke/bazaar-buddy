<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFinancialsToBazaarVendor extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('bazaar_vendor', function(Blueprint $table)
		{
			$table->decimal('table_fee', 10, 2)->default(0);
			$table->decimal('audit_adjust', 10, 2)->default(0);
			$table->boolean('checked_out');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('bazaar_vendor', function(Blueprint $table)
		{
			$table->dropColumn(['table_fee', 'audit_adjust', 'checked_out']);
			$table->dropColumn(['table_fee', 'audit_adjust']);
		});
	}

}
