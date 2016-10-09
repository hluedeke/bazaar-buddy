@extends('main')
	
@section('content')
	@include('/main/sales_sheet/validate/_wizardbar', array('active_tab' => 1))
	
	<div class="text-center">
		<h1>Validate A Sheet</h1>
	</div>
	
	{!! Form::open() !!}
	<div class="form-box">
		
		@include('errors/_list')
		
		<div class="form-row">
			{!! Form::label('sheet_number', 'Sheet Number:') !!}
			{!! Form::text('sheet_number', NULL, ['id' => 'sheet-number']) !!}
		</div>
	</div>
	{!! Form::submit('Next &gt;', ['class' => 'next']) !!}
	
	{!! Form::close() !!}
	<a href="{{ action('ValidationController@destroySession') }}">
		<button class="cancel">Cancel</button>
	</a>
@stop
	
@section('scripts')
<script type="text/javascript">
$(function() {
	$( "#sheet-number" ).autocomplete({
	      source: function (request, response) {
    		  $.get("{{ action('HelperController@acSalesSheet') }}", {
        		  input: request.term
    		  }, function (data) {
        		  response(data);
    		  });
		  },
		  delay: 120
	    });
});
</script>
@stop