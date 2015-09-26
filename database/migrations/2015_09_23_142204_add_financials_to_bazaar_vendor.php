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
<<<<<<< HEAD
			$table->boolean('checked_out');
=======
>>>>>>> 9923a9a6313719f4a4d78ae14970753272df55bf
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
<<<<<<< HEAD
			$table->dropColumn(['table_fee', 'audit_adjust', 'checked_out']);
=======
			$table->dropColumn(['table_fee', 'audit_adjust']);
>>>>>>> 9923a9a6313719f4a4d78ae14970753272df55bf
		});
	}

}
