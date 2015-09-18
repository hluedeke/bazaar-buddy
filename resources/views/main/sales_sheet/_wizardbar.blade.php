<div class="wizard-bar-container">
	<ul class="wizard-bar">
		@if($active_tab == 1)
			<li class="active">
		@else
			<li class="visited">
		@endif
				<a href="{{ action('SalesSheetController@createInfo') }}">Sheet Info</a>
			</li>
		
		@if($active_tab == 3)
			<li class="visited">
		@elseif($active_tab == 2)
			<li class="active">
		@else
			<li>
		@endif
		
		@if($active_tab >= 2)
				<a href="{{ action('SalesSheetController@createSales') }}">Sales</a>
		@else
				Sales
		@endif
			</li>
			
		@if($active_tab == 3)
			<li class="active">
		@else
			<li>
		@endif
		
		@if($active_tab == 3)
				<a href="#">Finalize</a>
		@else
				Finalize
		@endif
			</li>
	</ul>
</div>