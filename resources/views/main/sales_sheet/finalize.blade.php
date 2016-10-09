@extends('main')
	
@section('content')
	@include('/main/sales_sheet/_wizardbar', ['active_tab' => 3])

	<div class="text-center">
		<h1>Sheet {{ $sheet->sheet_number }}</h1>
		<h2>Finalize</h2>
	</div>
	
	<div>
		<p>Vendor: {{ $sheet->vendor->name }}</p>
		<p>Date: {{ $sheet->date_of_sales->format('m/d/Y') }}</p>
		<p>Total Sales: <span class="dollar-amount">{{ $sheet->totalSales($sales) }}</span></p>
	</div>
	
	<div class="form-box">
		@include('/main/sales_sheet/_salestable', array('sales'=> $sales ))
	</div>
	
	{!! Form::open() !!}
	{!! Form::submit('Finish', ['class' => 'next']) !!}
	{!! Form::close() !!}
	
	<a href="{{ action('SalesSheetController@destroySession') }}">
		<button class="cancel">Cancel</button>
	</a>
	
@stop