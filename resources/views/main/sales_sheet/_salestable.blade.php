<table>
	<thead>
		<tr>
			<th></th>
			<th>Receipt Number</th>
			<th>Sale Amount</th>
			<th>Terminal ID</th>
			<th>Sequence Number</th>
			<th>Type</th>
			@if(isset($status))
			<th>Status</th>
			@endif
		</tr>
	</thead>
	@forelse($sales as $i => $sale)
	<tr>
		<td>{{ $i+1 }}</td>
		<td>{{ $sale['receipt_number'] or "" }}</td>
		<td class="dollar-amount">{{ $sale['amount'] }}</td>
		<td>{{ $sale['terminal_id'] or "" }}</td>
		<td>{{ $sale['sequence_id'] or "" }}</td>
		<td>{{ $sale['sales_type'] }}</td>
		@if(isset($status))
		@if($sale['sales_type'] === SalesType::CARD || $sale['sales_type'] === SalesType::LAYAWAY)
		@if($sale['validated'] == Validated::MISSING || $sale['validated'] == Validated::INCORRECT)
		<td class="error">{{ $sale['validated'] }}</td>
		@else
		<td>{{ $sale['validated'] }}</td>
		@endif
		@else
		<td>N/A</td>
		@endif
		@endif
	</tr>
	@empty
	<tr>
		<td colspan="6">No sales entered.</td>
	</tr>
	@endforelse
</table>