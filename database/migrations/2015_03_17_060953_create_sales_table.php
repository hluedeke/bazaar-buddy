<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sales', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('sales_sheet_id')->unsigned();
			$table->string('sales_type');
			$table->string('validated');
			$table->integer('receipt_number')->nullable();
			$table->integer('terminal_id')->nullable();
			$table->integer('sequence_id')->nullable();
			$table->decimal('amount', 10, 2);
			$table->timestamps();
			
			$table->foreign('sales_sheet_id')->references('id')->on('sales_sheets');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sales');
	}

}
