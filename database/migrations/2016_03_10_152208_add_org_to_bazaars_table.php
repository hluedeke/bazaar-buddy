<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrgToBazaarsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('bazaars', function ($table) {
			$table->string('organization')->default('Aviano Officers & Civilians Spouses Club')->after('name');
			$table->string('abbreviation')->default('BBB')->after('organization');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('bazaars', function ($table) {
			if(Schema::hasColumn('bazaars', 'organization'))
				$table->dropColumn('organization');
			if(Schema::hasColumn('bazaars', 'abbreviation'))
				$table->dropColumn('abbreviation');
		});
	}

}
