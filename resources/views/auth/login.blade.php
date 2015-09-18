@extends('main')
	
@section('content')

{!! Form::open() !!}
<div class="form-box">
	@include('errors/_list')
	
	<div class="form-row">
		{!! Form::label('username', 'Username: ') !!}
		{!! Form::text('username', NULL, ['style' => 'width: 200px;', 'max_length' => 20]) !!}
	</div>	
	<div class="form-row">
		{!! Form::label('password', 'Password: ') !!}
		{!! Form::password('password', ['style' => 'width: 200px;', 'max_length' => 25]) !!}
	</div>
	
</div>
{!! Form::submit('Login', ['class' => 'next']) !!}

{!! Form::close() !!}

@stop