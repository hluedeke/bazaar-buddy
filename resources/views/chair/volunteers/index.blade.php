@extends('main')
	
@section('submenu')
	@include('chair/_menu', ['active_tab' => 3])	
@stop
	
@section('content')
	<div class="text-center"><h1>Manage Volunteers</h1></div>
	<div class="text-center" style="display: none"><h2 style="padding-top: 40px;">Search for a Volunteer</h2></div>
	<div class="search-box" style="display: none">
		{!! Form::open() !!}
		<div class="text-center">
			{!! Form::text('vol-search') !!}
			<div class="ui-corner-all">
				<input type="submit" class="ui-icon ui-icon-search" />
			</div>
		</div>
		{!! Form::close() !!}
	</div>
	
	<div class="text-center"><h2 style="margin-top: 40px;">All Volunteers</h2></div>
	<div class="form-box">
		<table border="0">
			<thead>
				<th>Volunteer</th>
				<th>Status</th>
				<th>Settings</th>
			</thead>
			<tbody>
				@forelse ($volunteers as $vol)
				<tr>
					<td><a href="/volunteer/{{ $vol->id }}">{{ $vol->name }}</a></td>
					<td>
						@if ($vol->username && $vol->password)
							Chair
						@else
							Volunteer
						@endif
					</td>
					<td>
						<a href="/volunteer/{{$vol->id}}/edit"><span class="ui-icon ui-icon-gear"></a>
					</td>
				</tr>
				@empty
				<tr>
					<td colspan="2">There aren't any volunteers. <a href="#">Add one</a> now.</td>
				<tr>
				@endforelse
			</tbody>
		</table>
	</div>
@stop