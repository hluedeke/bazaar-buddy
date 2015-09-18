@extends('main')
	
@section('content')
	@include('main\sales_sheet\validate\_wizardbar', ['active_tab' => 2])
	
<div class="text-center">
	<h1>Sheet {{ $sheet->sheet_number }}</h1>
	<h2>Validate</h2>
</div>

{!! Form::open() !!}
<div class="form-box">
    @include('errors._list')
	<table id="validation-table">
		<thead>
			<tr>
				<th>Date</th>
				<th>Receipt Number</th>
				<th>Sale Amount</th>
				<th>Terminal ID</th>
				<th>Sequence Num</th>
				<th>{{ Validated::CORRECT }}</th>
				<th>{{ Validated::INCORRECT }}</th>
				<th>{{ Validated::MISSING }}</th>
			</tr>
		</thead>
		<tbody>
			@forelse($sales as $sale)
			<tr>
				<td>{{ $sheet->date_of_sales->format('m/d/Y') }}</td>
				<td><input type="text" name="receipt_num[{{$sale->id}}]" value="{{ $old['receipt_num'][$sale->id] or $sale->receipt_number }}" /></td>
				<td><input class="dollar-amount" type="text" name="amount[{{$sale->id}}]" value="{{ $old['amount'][$sale->id] or $sale->amount }}" /></td>
				<td><input type="text" name="term_id[{{$sale->id}}]" value="{{ $old['term_id'][$sale->id] or $sale->terminal_id }}" /></td>
				<td><input type="text" name="seq_num[{{$sale->id}}]" class="dynamic-row-trigger" value="{{ $old['seq_num'][$sale->id] or $sale->sequence_id }}"/></td>
				<td>
					{!! Form::radio("status[$sale->id]",
						Validated::CORRECT, $sale->validated == Validated::CORRECT) !!}
				</td>
				<td>{!! Form::radio("status[$sale->id]",
						Validated::INCORRECT, $sale->validated ==Validated::INCORRECT) !!}
				</td>
				<td>{!! Form::radio("status[$sale->id]",
						Validated::MISSING, $sale->validated ==Validated::MISSING) !!}
				</td>
			</tr>
			@empty
			<tr>
				<td colspan="9">No sales entered.</td>
			</tr>
			@endforelse
		</tbody>
	</table>
</div>

{!! Form::submit('Next &gt;', ['class' => 'next']) !!}
{!! Form::close() !!}

<a href="{{ action('ValidationController@destroySession') }}">
	<button class="cancel">Cancel</button>
</a>
@stop

@section('script')
	<script type="text/javascript">
		$("#validation-table").dynamicTable();
	</script>
@stop