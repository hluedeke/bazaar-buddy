@extends('main')
	
@section('content')
	
	{!! Form::open() !!}
	<div class="form-box">
		@include('errors/_list')

		<div class="form-row">
			{!! Form::label('name', 'First & Last Name: ') !!}
			{!! Form::text('name', NULL, ['style' => 'width: 300px;']) !!}
		</div>
		
	</div>
	{!! Form::submit('Login', ['class' => 'next']) !!}
	
	{!! Form::close() !!}
@stop