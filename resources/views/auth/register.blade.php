@extends('main')

@section('content')

<div class="form-box">
	@include('errors/_list')
	
	<form class="form-horizontal" role="form" method="POST" action="{{ url('/auth/register') }}">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">

		<label>Name</label>
		<input type="text" name="name" value="{{ old('name') }}">


		<label>Username</label>
		<input type="text" name="username" value="{{ old('email') }}">

		<label>Password</label>
		<input type="password" class="form-control" name="password">

		<label>Confirm Password</label>
		<input type="password" name="password_confirmation">

</div>
		<button type="submit" class="next">
			Register
		</button>
	</form>

@endsection
