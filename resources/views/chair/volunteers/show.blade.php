@extends('main')
	
@section('submenu')
	@include('chair/_menu', ['active_tab' => 3])	
@stop
	
@section('content')
	<div class="text-center">
		<h1>{{ $vol->name }}</h1>
		@if ($vol->username)
			<h2>Chair ({{$vol->username}})</h2>
		@else
			<h2>Volunteer</h2>
		@endif
	</div>

	<div class="form-box">
		<div class="text-center">
			<h2>Created Sheets</h2>
		</div>
		<table border="0" id="created-sheets">
			<thead>
				<th>Sheet Number</th>
				<th>Date</th>
				<th>Status</th>
			</thead>
			<tbody>
				@forelse ($vol->createdSheets as $sheet)
				<tr>
					<td>
						<a href="/salesSheet/{{ $sheet->sheet_number }}">
							{{ $sheet->sheet_number }}</a>
					</td>
					<td>{{ $sheet->date_of_sales->format('m/d/Y') }}</td>
					<td>{{ $sheet->getValidationStatus() }}</td>
				</tr>
				@empty
				<tr>
					<td colspan="3">{{ $vol->name }} has not created any sales sheets.</td>
				</tr>
				@endforelse
			</tbody>
		</table>
	</div>
	<div class="form-box">
		<div class="text-center">
			<h2>Validated Sheets</h2>
		</div>
		<table border="0" id="validated-sheets">
			<thead>
				<th>Sheet Number</th>
				<th>Date</th>
				<th>Status</th>
			</thead>
			<tbody>
				@forelse ($vol->validatedSheets as $sheet)
				<tr>
					<td>
						<a href="/salesSheet/{{ $sheet->sheet_number }}">
							{{ $sheet->sheet_number }}</a>
					</td>
					<td>{{ $sheet->date_of_sales->format('m/d/Y') }}</td>
					<td>{{ $sheet->getValidationStatus() }}</td>
				</tr>
				@empty
				<tr>
					<td colspan="3">{{ $vol->name }} has not validated any sales sheets.</td>
				</tr>
				@endforelse
			</tbody>
		</table>
	</div>
	<button class="back"><a href="{{ action('VolunteerController@index') }}">&lt; Back</a></button>
@stop
	
@section('scripts')
	<script>
		$(function() {
			
			$("#created-sheets").tablesorter({
				sortList: [[1,0],[0,0]],
				headers: { 2: { sorter: false }}
			});
			
			$("#validated-sheets").tablesorter({
				sortList: [[1,0],[0,0]],
				headers: { 2: { sorter: false }}
			});
	
	  	});
	</script>
@stop