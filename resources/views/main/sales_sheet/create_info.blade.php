@extends('main')

@section('content')
	@include('main/sales_sheet/_wizardbar', ['active_tab' => 1])
	
	<div class="text-center">
		<h1>New Sales Sheet</h1>
		<h2>Sheet Info</h2>
	</div>
	
	{!! Form::open() !!}
	<div class="form-box">
			
			@include('errors/_list')
			
			<div class="form-row">
				{!! Form::label('date_of_sales', 'Date') !!}
				{!! Form::text('date_of_sales', $curr_date, ['id' => 'datepicker']) !!}
			</div>
			<div class="form-row">
				{!! Form::label('sheet_number', 'Sheet Number') !!}
				{!! Form::text('sheet_number', $sheet->sheet_number) !!}
			</div>
			<div class="form-row">
				{!! Form::label('vendor', 'Vendor') !!}
				{!! Form::text('vendor', $sheet->vendor->name, ['id' => 'vendor']) !!}
			</div>
	</div>
	{!! Form::submit('Next &gt;', ['class' => 'next']) !!}
	{!! Form::close() !!}
		<a href="{{ action('SalesSheetController@destroySession') }}">
			<button class="cancel">Cancel</button>
		</a>
@stop

@section('scripts')
	<script type="text/javascript">
  	$(function() {
		$( "#datepicker" ).datepicker();
		$( "#vendor" ).autocomplete({
		      source: function (request, response) {
        		  $.get("{{ action('HelperController@acVendor') }}", {
            		  input: request.term
        		  }, function (data) {
            		  response(data);
        		  });
    		  },
			  delay: 120
		    });
	});
	</script>
@stop