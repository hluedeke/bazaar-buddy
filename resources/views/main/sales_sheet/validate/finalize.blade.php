@extends('main')
	
@section('content')
	@include('main\sales_sheet\validate\_wizardbar', array('active_tab' => 3))
	
<div class="text-center">
	<h1>Sheet {{ $sheet->sheet_number }}</h1>
	<h2>Finalize</h2>
</div>

<div>
	<p>Vendor: {{ $sheet->vendor->name }}</p>
</div>
<div class="form-box">
	
	@if($failed)
	<div class="ui-widget">
		<div class="ui-state-error ui-corner-all" style="padding: 0.7em">
			<span class="ui-icon ui-icon-alert" style="float: left; margin-right: 				.3em;"></span>WARNING: This sheet has incorrect or missing information
			and will need to be reviewed by a business office chair before being validated.
		</div>
	</div>
	@endif

	<p>
		Credit Card Total: {{$cc_total}}<br />
		Sheet Total: {{$sheet_total}}<br />
	</p>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Date</th>
				<th>Receipt Number</th>
				<th>Sale Amount</th>
				<th>Terminal ID</th>
				<th>Sequence Number</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			@forelse($sales as $i => $sale)
			<tr>
				<td>{{ $i+1 }}</td>
				<td>{{ $sheet->date_of_sales->format('m/d/Y') }}</td>
				<td>{{ $sale->receipt_number }}</td>
				<td class="dollar-amount">{{ $sale->amount }}</td>
				<td>{{ $sale->terminal_id }}</td>
				<td>{{ $sale->sequence_id }}</td>
				@if($sale->validated != Validated::CORRECT)
				<td class="error">{{ $sale->validated }}</td>
				@else
				<td>{{ $sale->validated }}</td>
				@endif
			</tr>
			@empty
			<tr>
				<td colspan="6">No sales entered.</td>
			</tr>
			@endforelse
		</tbody>
	</table>
</div>

{!! Form::open() !!}
{!! Form::submit('Finish', ['class' => 'next']) !!}
{!! Form::close() !!}
<a href="{{ action('ValidationController@destroySession') }}">
	<button class="cancel">Cancel</button>
</a>
@stop