@extends('main')
	
@section('content')
	<div class="text-center">
		<h1>Edit Sales Sheet {{ $sheet->sheet_number }}</h1>
	</div>
	
	{!! Form::open() !!}
	<div class="form-box">
		<div class="form-row">
			{!! Form::label('date_of_sales', 'Date') !!}
			{!! Form::text('date_of_sales', $sheet->date_of_sales->format('m/d/Y'), ['id' => 'datepicker']) !!}
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
	
	<div class="form-box">
		<table>
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th>Receipt Number</th>
					<th>Sale Amount</th>
					<th>Terminal ID</th>
					<th>Sequence Number</th>
					<th>Type</th>
				</tr>
			</thead>
			<tbody>
				@foreach($sheet->sales as $i => $sale)
				<tr>
					<td>{{ $i + 1 }}</td>
					<td>
						@if($sale->sales_type == SalesType::CARD)
						<input type="text" name="receipt_number[]" value="{{ $sale->receipt_number }}"
							class="left" /></td>
						@else
						N/A
						@endif
					<td>
						<input type="text" name="amount[]" value="{{ $sale->amount }}"
							class="left dollar-amount" /></td>
					<td>
						@if($sale->sales_type == SalesType::CARD)
						<input type="text" name="term_id[]" value="{{ $sale->terminal_id }}"
							class="left" /></td>
						@else
						N/A
						@endif
					<td>
						@if($sale->sales_type == SalesType::CARD)
						<input type="text" name="seq_num[]" value="{{ $sale->sequence_id }}"
							class="left" /></td>
						@else
						N/A
						@endif
					<td>{!! Form::select('sales_type', SalesType::values()) !!}</td>
				</tr>	
				@endforeach
			</tbody>
		</table>
	</div>
	{!! Form::close() !!}
	
	<a href="{{ action('MainController@index') }}">
		<button class="cancel">Cancel</button>
	</a>
@stop
	
@section('scripts')
	<script>

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
			
		// Run our dollar-formatting algorithm on dollar-related input fields
		$(document).on("blur", ".dollar-amount", function(event) {
			var dollarAmount = formatDollar(this.value);
			this.value = dollarAmount;
		});
	});
	
	
	</script>
@stop