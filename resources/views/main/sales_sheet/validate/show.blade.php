@extends('main')

@section('content')
	<div class="text-center">
		<h1>Sales Sheet {{ $sheet->sheet_number }}</h1>
	</div>

	<div>
		<p>Vendor: {{ $sheet->vendor->name}}</p>
		<p>Date of Sales: {{ $sheet->date_of_sales->format('m/d/Y') }}</p>
		<p>Total Sales: <span class="dollar-amount">{{ $sheet->totalSales() }}</span></p>
	</div>

	<div class="form-box">
		@include('/main/sales_sheet/_salestable', array('sales'=> $sheet->sales, 'status' =>
		'true' ))
	</div>

	<a href="{{ action('MainController@index') }}">
		<button class="cancel">&lt; Back</button>
	</a>
@stop