@extends('main')
	
@section('submenu')
	@include('chair/_menu', ['active_tab' => 2])	
@stop
	
@section('content')
<div class="text-center">
	<h1>Vendor Totals</h1>
	
	@foreach($data as $date => $sheet)
	<div class="form-box">
		<h2>{{ $date }}</h2>
		<table border="0">
			<thead>
				<tr>
					@foreach($sheet[0] as $header => $ignore)
					<th></th>
					@endforeach
				</tr>
			</thead>
			<tbody>
				@foreach($sheet as $row)
				<tr>
					@foreach($row as $cell)
					<td>{{$cell}}</td>
					@endforeach
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@endforeach
	<a href="{{ action('ReportController@index') }}"><button class="next">&lt; Back</button></a>
</div>

@stop