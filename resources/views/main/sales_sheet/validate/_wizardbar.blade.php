<div class="wizard-bar-container">
	<ul class="wizard-bar">
		@if($active_tab == 1)
		<li class="active">
		@else
		<li class="visited">
		@endif
			<a href="{{ action('ValidationController@select') }}">Choose a Sheet</a>
		</li>
		
		@if($active_tab == 3)
		<li class="visited">
		@elseif($active_tab == 2)
		<li class="active">
		@else
		<li>
		@endif
		
		@if($active_tab >= 2)
			<a href="{{ action('ValidationController@validateSheet') }}">Validate</a>
		@else
			Validate
		@endif
		</li>
		
		@if($active_tab == 3)
		<li class="active">
		@else
		<li>
		@endif
			Finalize</li>
	</ul>
</div>