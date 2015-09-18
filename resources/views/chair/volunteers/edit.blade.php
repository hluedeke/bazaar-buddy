@extends('main')
	
@section('submenu')
	@include('chair/_menu', ['active_tab' => 3])	
@stop
	
@section('content')
	<div class="text-center">
		<h1>{{ $vol->name }}</h1>
		<h2>Settings</h2>
	</div>	
	<div class="form-box">
		
		<!-- Errors -->
		<div class="ui-widget">
			<div id="errors" class="ui-state-error ui-corner-all" style="padding: 0.7em; display: none">
				<ul id="error-list"></ul>
			</div>
			<div id="messages" class="ui-state-highlight ui-corner-all" style="display: none">
				<span class="ui-icon ui-icon-check"></span><span id="message"></span>
			</div>
		</div>
		<!-- End errors -->
		
		<h2>Chair Settings</h2>
		@if ($vol->type == 'chair')
		<div style="float:left; width: 100px">
			<div class="form-row">
			Username:
			</div>
			<div class="form-row">
			Password:
			</div>
		</div>
		<div style="float:left; width: 500px">
			<div class="form-row">
			{{ $vol->username }}
			</div>
			<div id="change-text">
				<div class="form-row">
					<a id="change-text-lnk" href="#">Change</a>
				</div>
			</div>
			<div id="password-change-box" style="display: none">
				<div class="form-row">
					<input id="password-1" type="password" name="password"/>
				</div>
				<div class="form-row">
					<input id="password-2" type="password" name="password2"/>
				</div>
				<div class="form-row">
					<button id="cancel-password" name="cancel-password">Cancel</button>
					<button id="change-password" name="change-password">Update</button>
				</div>
			</div>
		</div>
		<div style="clear:both"></div>
		@else
			<div class="form-row">This user is not a chair. </div>
			<button id="make-chair-btn" name="admin">Make Chair</button>
			<div id="make-chair-form" style="display: none">
				<div class="form-row">
					<div class="label-box">Username: </div>
					<input id="username" type="text" name="username" />
				</div>
				<div class="form-row">
					<div class="label-box">Password: </div>
					<input id="password-1" type="password" name="password" />
				</div>
				<div class="form-row">
					<div class="label-box">Confirm Password:</div>
					<input id="password-2" type="password" name="password2" />
				</div>
				<div class="form-row">
					<button id="cancel-password" name="cancel-password">Cancel</button>
					<button id="change-password" name="change-password">Update</button>
				</div>
			</div>
		@endif
	</div>
	<div class="form-box">
		<h2>Other Settings</h2>
		@if($vol->type == 'chair')
		<button id="remove-chair" style="float: left; margin-right: 4px">Remove Chair Status</button>
		@endif
	</div>
@stop
	
@section('scripts')
	@if($vol->type == 'chair')
	<script>
		$(function() {
	
			$("#change-text-lnk").click(function(event) {
				$('#change-text').hide();
				$("#password-change-box").show(200);
			});
			
			$("#cancel-password").click(function() {
				$('#messages, #errors, #password-change-box').hide(200, function() {
					$('#change-text').show(100);	
				});
			});
			
			$('#remove-chair').click(function() {
				$("#messages, #errors").hide(200);
				var c = confirm("Are you sure you want to remove chair rights from {{ $vol->name}}?");
				if(c == true) {
					$.ajax({
						url: '/volunteer/{{$vol->id}}',
						data: {
							'_token': '{{ csrf_token() }}',
							action: 'remove-chair',
						},
						method: 'put'
					}).done(function(data) {
						$('#message').text(data.message);
						$('#messages').show(200);
						
						setTimeout(function() {
							location.reload();
						}, 1500);
						
					}).fail(function(jqXHR) {
						$('#error-list').html('<li><span class="ui-icon ui-icon-alert" ' +
							'style="float: left; margin-right: .3em;"></span></li>' +
							'There was a problem with removing chair rights.');
						$("#errors").show(200);
					});
				}
			});
			
			$("#change-password").click(function() {
				$("#messages, #errors, #password-change-box").hide(200);
				$.ajax({
					url: '/volunteer/{{$vol->id}}',
					data: {
						'_token': '{{ csrf_token() }}',
						action: 'change-password',
						'password': $('#password-1').val(),
						'password_confirmation': $('#password-2').val()
					},
					method: 'put'
				}).done(function(data) {
					$("#messages, #change-text").show(200);
					$('#message').text(data.message);
					
				}).fail(function(jqXHR) {
					var errors = jqXHR.responseJSON;
					$('#error-list').html('');
					
					for(var error in errors) {
						
						var row = '<li><span class="ui-icon ui-icon-alert" ' +
						'style="float: left; margin-right: .3em;"></span></li>' +
						errors[error];
						
						$('#error-list').append(row);
					}

					$("#errors, #password-change-box").show(200);
				});
			});
	
	  	});
	</script>	
	@else
	<script>
		$(function() {
			$("#make-chair-btn").click(function(event) {
				$(this).hide();
				$("#make-chair-form").show(200);
			});
			
			$("#cancel-password").click(function() {
				$("#messages, #errors, #make-chair-form").hide(200, function() {
					$('#make-chair-btn').show(100);	
				});
			});
			
			$("#change-password").click(function() {
				$("#make-chair-form, #messages, #errors").hide(200);
				$.ajax({
					url: '/volunteer/{{$vol->id}}',
					data: {
						'_token': '{{ csrf_token() }}',
						action: 'make-chair',
						'username': $('#username').val(),
						'password': $('#password-1').val(),
						'password_confirmation': $('#password-2').val()
					},
					method: 'put'
				}).done(function(data) {
					$("#messages, #make-chair-btn").show(200);
					$('#message').text(data.message);
					
					setTimeout(function() {
						location.reload();
					}, 1500);
					
				}).fail(function(jqXHR) {
					console.log(jqXHR.responseText);
					var errors = jqXHR.responseJSON;
					$('#error-list').html('');
					
					for(var error in errors) {
						
						var row = '<li><span class="ui-icon ui-icon-alert" ' +
						'style="float: left; margin-right: .3em;"></span></li>' +
						errors[error];
						
						$('#error-list').append(row);
					}

					$("#errors, #make-chair-form").show(200);
				});
			});
	
	  	});
	</script>
	@endif

@stop