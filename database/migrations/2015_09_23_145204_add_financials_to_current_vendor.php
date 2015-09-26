<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFinancialsToCurrentVendor extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
<<<<<<< HEAD
		DB::connection()->getPdo()->exec("ALTER VIEW current_vendors AS
=======
		DB::statement("ALTER VIEW current_vendors AS
>>>>>>> 9923a9a6313719f4a4d78ae14970753272df55bf
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
			) AS checkout,
			(
				SELECT table_fee
				FROM bazaar_vendor AS bv
				WHERE bv.vendor_id = v.id AND bv.`bazaar_id` = (
					SELECT setting
					FROM app_settings AS a
					WHERE a.`name` = 'current_bazaar'
				)
			) AS table_fee,
			(
				SELECT audit_adjust
				FROM bazaar_vendor AS bv
				WHERE bv.vendor_id = v.id AND bv.`bazaar_id` = (
					SELECT setting
					FROM app_settings AS a
					WHERE a.`name` = 'current_bazaar'
				)
<<<<<<< HEAD
			) AS audit_adjust, (
				SELECT checked_out
				FROM bazaar_vendor AS bv
				WHERE bv.vendor_id = v.id AND bv.`bazaar_id` = (
					SELECT setting
					FROM app_settings AS a
					WHERE a.`name` = 'current_bazaar'
				)
			) AS checked_out FROM vendors AS v
=======
			) AS audit_adjust FROM vendors AS v
>>>>>>> 9923a9a6313719f4a4d78ae14970753272df55bf
			WHERE v.id IN (
				SELECT vendor_id FROM bazaar_vendor WHERE bazaar_id = (
					SELECT setting FROM app_settings AS a WHERE a.`name` = 'current_bazaar'
			));");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
<<<<<<< HEAD
		DB::connection()->getPdo()->exec("ALTER VIEW current_vendors AS
			SELECT *, (
				SELECT vendor_number
				FROM bazaar_vendor AS bv
				WHERE bv.vendor_id = v.id AND bv.`bazaar_id` = (
					SELECT setting
=======
		DB::statement("ALTER VIEW current_vendors AS
			SELECT *, (
	SELECT vendor_number
				FROM bazaar_vendor AS bv
				WHERE bv.vendor_id = v.id AND bv.`bazaar_id` = (
	SELECT setting
>>>>>>> 9923a9a6313719f4a4d78ae14970753272df55bf
					FROM app_settings AS a
					WHERE a.`name` = 'current_bazaar'
				)
			) AS vendor_number,
			 (
<<<<<<< HEAD
				SELECT checkout
				FROM bazaar_vendor AS bv
				WHERE bv.vendor_id = v.id AND bv.`bazaar_id` = (
					SELECT setting
=======
			 SELECT checkout
				FROM bazaar_vendor AS bv
				WHERE bv.vendor_id = v.id AND bv.`bazaar_id` = (
	SELECT setting
>>>>>>> 9923a9a6313719f4a4d78ae14970753272df55bf
					FROM app_settings AS a
					WHERE a.`name` = 'current_bazaar'
				)
			) AS checkout FROM vendors AS v
			WHERE v.id IN (
<<<<<<< HEAD
				SELECT vendor_id FROM bazaar_vendor WHERE bazaar_id = (
					SELECT setting FROM app_settings AS a WHERE a.`name` = 'current_bazaar'
=======
		SELECT vendor_id FROM bazaar_vendor WHERE bazaar_id = (
	SELECT setting FROM app_settings AS a WHERE a.`name` = 'current_bazaar'
>>>>>>> 9923a9a6313719f4a4d78ae14970753272df55bf
			));");
	}

}
