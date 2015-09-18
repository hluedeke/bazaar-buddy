<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrentVendorsView extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("CREATE VIEW current_vendors AS
			SELECT *, (
				SELECT vendor_number
				FROM bazaar_vendor AS bv
				WHERE bv.vendor_id = v.id AND bv.`bazaar_id` = (
					SELECT setting
					FROM app_settings AS a
					WHERE a.`name` = 'current_bazaar'
				)
			) AS vendor_number,
			 (
				SELECT checkout
				FROM bazaar_vendor AS bv
				WHERE bv.vendor_id = v.id AND bv.`bazaar_id` = (
					SELECT setting
					FROM app_settings AS a
					WHERE a.`name` = 'current_bazaar'
				)
			) AS checkout FROM vendors AS v
			WHERE v.id IN (
				SELECT vendor_id FROM bazaar_vendor WHERE bazaar_id = (
					SELECT setting FROM app_settings AS a WHERE a.`name` = 'current_bazaar'
			));"
		);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("DROP VIEW current_vendors");
	}

}
