@extends('main')
	
@section('submenu')
	@include('chair/_menu', ['active_tab' => 1])
@stop
	
@section('content')

<div class="text-center">
<h1>Sheets for Review</h1>
</div>

<div class="form-box">
	@include('errors._message')
	<table id="sales-sheets" class="clear tablesorter">
		<thead>
			<tr>
				<th class="sort">Sheet Number <a href="#"></a></th>
				<th class="sort">Date<a href="#"></a></th>
				<th class="sort">Vendor<a href="#"></a></th>
				<th class="sort">Status<a href="#"></a></th>
				<th class="sort">Validator<a href="#"></a></th>
				<th>Review</th>
			</tr>
		</thead>
	
		<tbody>
			@forelse ($sheets as $i => $sheet)
			<tr>
				<td>{{ $sheet->sheet_number }}</td>
				<td>{{ $sheet->date_of_sales->format('m/d/Y') }}</td>
				<td>{{ $sheet->vendorNumber() }} 
						- {{ $sheet->vendor->name }}</td>
				<td>{{ $sheet->getValidationStatus() }}</td>
				<td>{{ $sheet->validatedBy->name }}</td>
				<td><a href="{{ action('ChairController@review', $sheet->sheet_number)}}">Review</a></td>
			</tr>
			@empty
			<tr>
				<td colspan="6">No sales sheets currently for review.</td>
			</tr>
			@endforelse
		</tbody>
	
	</table>
</div>
@stop
	
@section('scripts')
	<script>
		$(function() {
		
			$("#sales-sheets").tablesorter({
			});

	  	});
	</script>
@stop