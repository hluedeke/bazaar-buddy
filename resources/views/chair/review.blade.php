@extends('main')
	
@section('submenu')
	@include('chair/_menu', ['active_tab' => 1])	
@stop
	
@section('content')
	<div class="text-center"><h1>Review</h1>
		<h2>Sheet {{ $sheet->sheet_number }}</h2></div>
		
		
	{!! Form::open() !!}
	<div class="form-box">
		
		@if(Session::has('message')) 
		<div class="ui-state-highlight ui-corner-all">
			<span class="ui-icon ui-icon-check"></span></span>{{ Session::get('message') }}</span>
		</div>
		@endif
		
		@if(Session::has('error')) 
		<div class="ui-state-error ui-corner-all">
			<span class="ui-icon ui-icon-alert"></span></span>{{ Session::get('error') }}</span>
		</div>
		@endif
		
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
		<table id="review-sales">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th>Receipt Number</th>
					<th>Sale Amount</th>
					<th>Terminal ID</th>
					<th>Sequence Number</th>
					<th>Valid</th>
					<th>Type</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				@foreach($sheet->sales as $i => $sale)
				<tr class="disabling dynamic-row">
					<td class="dynamic-row-num">{{ $i + 1 }}</td>
					<td>
						<input type="text" name="receipt_number[]" value="{{ $sale->receipt_number }}"
							class="left hideable" />
						<input type="hidden" name="sales[]" value="{{ $sale->id }}"></td>
					<td>
						<input type="text" name="amount[]" value="{{ $sale->amount }}"
							class="left dollar-amount" /></td>
					<td>
						<input type="text" name="term_id[]" value="{{ $sale->terminal_id }}"
							class="left hideable" /></td>
					<td>
						<input type="text" name="seq_num[]" value="{{ $sale->sequence_id }}"
							class="left hideable" /></td>
					<td>
						@if(Validated::isValid($sale->validated))
						<input name="valid[]" type="checkbox" value="{{ $sale->id }}" 
							checked="checked" class="validbox" />
						@else
						<input name="valid[]" type="checkbox" value="{{ $sale->id }}"  
							class="validbox" />
						@endif
					</td>
					<td>{!! Form::select('sales_type[]', SalesType::values(), $sale->sales_type) !!}</td>
					<td>
						<a class="dynamic-row-remove" href="#">
							<span class="ui-icon ui-icon-circle-close left"></span>
						</a>
					</td>
				</tr>	
				@endforeach
			</tbody>
		</table>
		<a id="sales-add-row" href="#">
			<span class="ui-icon ui-icon-plusthick left"></span>Add Another Sale
		</a>
	</div>
	{!! Form::submit('Save', ['class' => 'next']) !!}
	{!! Form::close() !!}
	<a href="{{ action('ChairController@index') }}">
		<button class="cancel">Cancel</button>
	</a>
@stop
	
@section('scripts')
	<script>

	$("#review-sales").dynamicTable({addRowElements: "#sales-add-row"});

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
		
		// This disables the fields if a non-credit sale type is selected
		function disableOptFields(rows) {
			return rows.each(function() { 
				
				// Receipt Number
				
				var sales_type = $(this).find(":input[name='sales_type[]']").val();
				if(sales_type != '{{ SalesType::CARD }}') {
					$(this).find('.hideable').each(function() {
						$(this).css('visibility', 'hidden');
					});
				}
				else {
					$(this).find('.hideable').each(function() {
						$(this).css('visibility', 'visible');
					});
				}
				
				// Checkboxes
				if(sales_type != '{{ SalesType::CARD }}' && sales_type != '{{ SalesType::LAYAWAY }}') {
					$(this).find('.validbox').each(function() {
						$(this).css('visibility', 'hidden');
					});
				}
				else {
					$(this).find('.validbox').each(function() {
						$(this).css('visibility', 'visible');
					});
				}

			});
		}
		
		disableOptFields($("tr"));
		
		$(document).on("change", ':input[name="sales_type[]"]', function(event) {
			disableOptFields($(this).closest("tr"));
		});
		
	});


	</script>
@stop