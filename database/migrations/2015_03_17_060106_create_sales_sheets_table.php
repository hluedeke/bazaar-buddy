<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Validated;

class CreateSalesSheetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sales_sheets', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('bazaar_id')->unsigned();
			$table->integer('vendor_id')->unsigned();
			$table->timestamp('date_of_sales');
			$table->integer('sheet_number')->unsigned();
			$table->integer('created_by')->unsigned();
			$table->integer('validated_by')->unsigned()->nullable();
			$table->timestamps();
			
			$table->foreign('bazaar_id')->references('id')->on('bazaars')->onDelete('cascade');
			$table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
			$table->foreign('validated_by')->references('id')->on('users')->onDelete('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sales_sheets');
	}

}
