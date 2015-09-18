@extends('main')
	
@section('submenu')
	@include('chair/_menu', ['active_tab' => 4])	
@stop
	
@section('content')
	<div class="text-center">
		<h1>{{ $bazaar->name }}</h1>
		<h2>{{ $bazaar->dates() }}</h2>
	</div>
	
	<div class="form-box">
		
		<!-- Errors -->
		<div class="ui-widget">
			@if(Session::has('errors'))
			<div id="errors" class="ui-state-error ui-corner-all" style="padding: 0.7em;">
				<ul id="error-list">
					@foreach($errors->all() as $error)
						<li>
							<span class="ui-icon ui-icon-alert" style="float: left; margin-right: 									.3em;"></span>
							{{ $error }}
						</li>
					@endforeach
				</ul>
			</div>
			@endif
			@if(Session::has('message'))
			<div id="messages" class="ui-state-highlight ui-corner-all">
				<span class="ui-icon ui-icon-check"></span><span id="message">{{ Session::get('message') }}</span>
			</div>
			@endif
		</div>
		<!-- End errors -->
		
		<h2>Vendors</h2>

		<table style="display: none">
		<tr class="dynamic-row">
			<td><input type="text" name="number[]" /></td>
			<td><input class="dynamic-row-trigger vendor-name" type="text" name="name[]" /></td>
			<td>
				<select name="currency[]">
					<option value="USD">US Dollars</option>
					<option value="EUR">Euros</option>
					<option value="GBP">British Pound</option>
				</select>
			</td>
			<td><input type="text" name="checkout[]" /></td>
			<td></td>
			<td><a class="vendor-del" href="#">
						<span class="ui-icon ui-icon-circle-close">
				</a></td>
		</tr>
		</table>

		{!! Form::open(['action' => ['BazaarController@update', $bazaar->id]]) !!}
		<table id="vendor-table" border="0">
			<thead>
				<tr>
					<th>Vendor Number</th>
					<th>Name</th>
					<th>Currency</th>
					<th>Checkout Order</th>
					<th>Edit</th>
					<th>Remove</th>
				</tr>
			</thead>
			<tbody>
				@forelse ($bazaar->vendors as $vendor)
				<tr>
					<td class="number">{{ $vendor->vendorNumber($bazaar) }}</td>
					<td class="vendor-name">{{ $vendor->name }}</td>
					<td class="payment">{{ $vendor->payment }}</td>
					<td class="checkout-number">{{$vendor->checkout($bazaar)}}</td>
					<td><a class="vendor-edit" href="#">
						<span class="ui-icon ui-icon-pencil"></span>
					</a></td>
					<td><a class="vendor-del" href="#">
						<span class="ui-icon ui-icon-circle-close">
					</a></td>
				</tr>
				@empty
				<tr>
					<td id="empty" colspan="5">This bazaar doesn't have any vendors yet.</td>
				</tr>
				@endforelse

			</tbody>
		</table>
		<div id="add-vendors-lnk" class="ui-widget-content left">
			<a href="#">
				<span class="ui-icon ui-icon-plusthick"></span> Add A Vendor
			</a>
		</div>
	</div>
	<input type="submit" class="next" value="Save" />
	{!! Form::close() !!}
	<a id="cancel-btn" href="{{ action('BazaarController@index')}}">
		<button class="cancel">Cancel</button>
	</a>
	
@stop
	
@section('scripts')
<script>
	
	$("#vendor-table").dynamicTable();
	
	$(function() {
		
		$("#vendor-table").tablesorter({
			headers: { 
				4: { sorter: false },
				5: { sorter: false }
			},
			sortList: [
				[0,0]
			]
		});
		
		$("#add-vendors-lnk").click(function() {
			$('#empty').hide();
			var element = $(".dynamic-row:first").clone().show().appendTo('#vendor-table tbody');
			vendorAC($(element).find('.vendor-name'));
		});
		
		$('.vendor-edit').click(function() {
			var newRow = $(".dynamic-row:first").clone().show();
			var oldRow = $(this).closest('tr').replaceWith(newRow);
			
			newRow.find('input[name="number\[\]"]').val(oldRow.find('.number').text());
			newRow.find('.vendor-name').val(oldRow.find('.vendor-name').text());
			newRow.find('select[name="currency\[\]"]').val(oldRow.find('.payment').text());
			newRow.find('input[name="checkout\[\]"]').val(oldRow.find('.checkout-number').text());
			
		});
		
		$('.vendor-del').click(function() {
			var del = confirm('Are you sure you want to remove?');
			var el = $(this).closest('tr').find('.vendor-name');
			var data = '';
			if(el.is('input')) {
				data = el.val();
			}
			else if(el.is('td')) {
				data = el.text();
			}
			if(del == true) {
				$.get("{{ action('BazaarController@removeVendor', $bazaar) }}", {
					input: data,
					}, function (data) {
						alert(data);
						el.closest('tr').fadeOut(300, function() {
							el.detach();
						});
				});
			}
		});
		
		function vendorAC(selector) { 
			$(selector).autocomplete({
				source: function (request, response) {
				$.get("{{ action('HelperController@acVendorName') }}", {
					input: request.term
					}, function (data) {
						response(data);
					});
				},
				delay: 120
			});
		}

  	});
</script>	
@stop