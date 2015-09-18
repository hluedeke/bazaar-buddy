@if($errors->any())
	<div class="ui-widget">
		<div class="ui-state-error ui-corner-all" style="padding: 0.7em">
			<ul>
			@foreach($errors->all() as $error)
				<li>
					<span class="ui-icon ui-icon-alert" style="float: left; margin-right: 									.3em;"></span>
					{{ $error }}
				</li>
			@endforeach
			</ul>
		</div>
	</div>
@endif