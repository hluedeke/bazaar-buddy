<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBazaarVendorTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bazaar_vendor', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('bazaar_id')->unsigned()->index();
			$table->integer('vendor_id')->unsigned()->index();
			$table->integer('vendor_number')->unsigned();
			$table->integer('checkout')->unsigned();
			$table->timestamps();
			
			$table->foreign('bazaar_id')->references('id')->on('bazaars')->onDelete('cascade');
			$table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bazaar_vendor');
	}

}
