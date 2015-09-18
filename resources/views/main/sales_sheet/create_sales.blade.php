@extends('main')

@section('content')
	@include('main/sales_sheet/_wizardbar', ['active_tab' => 2])
	
	<div class="text-center">
		<h1>Sheet {{ $sheet->sheet_number }}</h1>
		<h2>Sales</h2>
	</div>
	
	@include('errors/_list')
	
	{!! Form::open() !!}
	
	<div id="tabs">
		<ul class="tab-bar">
			<li><a href="#tab1">Cash Sales</a></li>
			<li><a href="#tab2">Credit Card Sales</a></li>
			<li><a href="#tab3">Layaway</a></li>
		</ul>
		<div id="tab1" class="tab-box">
			<div class="aside">
				Cash Sales Total: <span id="cash-total"></span>
			</div>
			<table id="cash-sales-table">
				<tr>
					<th>&nbsp;</th>
					<th>Sale Amount</th>
				</tr>
				@forelse($cash_sales as $i => $sale)
				<tr class="dynamic-row">
					<td class="dynamic-row-num">{{ $i + 1 }}</td>
					<td>
						<input type="text" 
							name="cash_amount[]" class="dynamic-row-trigger dollar-amount left" 
							value="{{ $sale->amount }}" />
						
						<a class="dynamic-row-remove" href="#">
							<span class="ui-icon ui-icon-circle-close left"></span>
						</a>
					</td>
				</tr>
				@empty
				<tr class="dynamic-row">
					<td class="dynamic-row-num">1</td>
					<td>
						<input type="text" name="cash_amount[]" 
							class="dynamic-row-trigger dollar-amount left" />
						<a class="dynamic-row-remove" href="#">
							<span class="ui-icon ui-icon-circle-close left"></span>
						</a>
					</td>
				</tr>
				@endforelse
			</table>
			<a id="cash-sales-add-row" href="#">
				<span class="ui-icon ui-icon-plusthick left"></span>Add Another Sale
			</a>
		</div>
		<div id="tab2" class="tab-box">
			<div class="aside">
				Card Sales Total: <span id="card-total"></span>
			</div>
			<table id="card-sales-table">
				<tr>
					<th>&nbsp;</th>
					<th>Receipt Number</th>
					<th>Sale Amount</th>
					<!--<th>Terminal ID</th>
					<th>Sequence Num</th>-->
					<th></th>
				</tr>
				@forelse($credit_sales as $i => $sale)
				<tr class="dynamic-row">
					<td class="dynamic-row-num">{{ $i + 1 }}</td>
					<td>
						<input type="text" name="receipt_number[]" value="{{ $sale->receipt_number }}"
							class="left" />
					</td>
					<td>
						<input type="text" name="card_amount[]" value="{{ $sale->amount }}"
							class="dynamic-row-trigger dollar-amount left" />
					</td>
					<!--<td>
						<input type="text" name="terminal_id[]" value="{{ $sale->terminal_id }}"
							class="left" size="5" />
					</td>
					<td>
						<input type="text" name="sequence_id[]" value="{{ $sale->sequence_id }}"
							class="dynamic-row-trigger left" size="5" />
					</td>-->
					<td>
						<a class="dynamic-row-remove" href="#">
							<span class="ui-icon ui-icon-circle-close left"></span>
						</a>
					</td>
				</tr>
				@empty
				<tr class="dynamic-row">
					<td class="dynamic-row-num">1</td>
					<td>
						<input type="text" name="receipt_number[]" class="left" />
					</td>
					<td>
						<input type="text" name="card_amount[]"
							class="dollar-amount left dynamic-row-trigger" />
					</td>
					<!--<td>
						<input type="text" name="terminal_id[]"
							class="left"  size="5" />
					</td>
					<td>
						<input type="text" name="sequence_id[]"
							class="dynamic-row-trigger left" size="5" />
					</td>-->
					<td>
						<a class="dynamic-row-remove" href="#">
							<span class="ui-icon ui-icon-circle-close left"></span>
						</a>
					</td>
				</tr>
				@endforelse
			</table>
			<a id="card-sales-add-row" href="#">
				<span class="ui-icon ui-icon-plusthick left"></span>Add Another Sale
			</a>
		</div>
		<div id="tab3" class="tab-box">
			<div class="aside">
				Layaway Sales Total: <span id="layaway-total"></span>
			</div>
			<table id="layaway-sales-table">
				<tr>
					<th>&nbsp;</th>
					<th>Sale Amount</th>
				</tr>
				@forelse($layaway_sales as $i => $sale)
				<tr class="dynamic-row">
					<td class="dynamic-row-num">{{ $i + 1 }}</td>
					<td>
						<input type="text" 
							name="layaway_amount[]" class="dynamic-row-trigger dollar-amount left" 
							value="{{ $sale->amount }}" />
						
						<a class="dynamic-row-remove" href="#">
							<span class="ui-icon ui-icon-circle-close left"></span>
						</a>
					</td>
				</tr>
				@empty
				<tr class="dynamic-row">
					<td class="dynamic-row-num">1</td>
					<td>
						<input type="text" name="layaway_amount[]" 
							class="dynamic-row-trigger dollar-amount left" />
						<a class="dynamic-row-remove" href="#">
							<span class="ui-icon ui-icon-circle-close left"></span>
						</a>
					</td>
				</tr>
				@endforelse
			</table>
			<a id="layaway-sales-add-row" href="#">
				<span class="ui-icon ui-icon-plusthick left"></span>Add Another Sale
			</a>
		</div>
	</div>
	{!! Form::submit('Next &gt;', ['class' => 'next']) !!}
	{!! Form::close() !!}
		<a href="{{ action('SalesSheetController@destroySession') }}">
			<button class="cancel">Cancel</button>
		</a>
@stop

@section('scripts')
	<script>
	    
		$( "#tabs" ).tabs();
		$("#cash-sales-table").dynamicTable({addRowElements: "#cash-sales-add-row"});
		$("#card-sales-table").dynamicTable({addRowElements: "#card-sales-add-row"});
		$("#layaway-sales-table").dynamicTable({addRowElements: "#layaway-sales-add-row"});
		
		$(function() {
			
			function runTotals() {
				var total = 0;
				$('#cash-sales-table .dollar-amount').each(function() {
					var number = Number($(this).val().replace(/[^0-9\.]+/g,""));
					total += number;
				});

				$('#cash-total').html(formatDollar(total, true));
				
				total = 0;
				$('#card-sales-table .dollar-amount').each(function() {
					var number = Number($(this).val().replace(/[^0-9\.]+/g,""));
					total += number;
				});

				$('#card-total').html(formatDollar(total, true));
				
				total = 0;
				$('#layaway-sales-table .dollar-amount').each(function() {
					var number = Number($(this).val().replace(/[^0-9\.]+/g,""));
					total += number;
				});

				$('#layaway-total').html(formatDollar(total, true));
			}
			runTotals();
			
			// Run our dollar-formatting algorithm on dollar-related input fields
			$(document).on("blur", ".dollar-amount", function(event) {
				var dollarAmount = formatDollar(this.value);
				this.value = dollarAmount;
			
				runTotals();
			});
			
			$(document).on("row-removed", function(event) {
				runTotals();
			});
			
		});
		
		
	</script>
@stop