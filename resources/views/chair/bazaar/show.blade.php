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
                                <span class="ui-icon ui-icon-alert"
                                      style="float: left; margin-right: 									.3em;"></span>
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
                <td><input type="text" name="number[]"/><input type="hidden" name="id[]"/></td>
                <td><input class="vendor-name" type="text" name="name[]"/></td>
                <td>
                    <select name="currency[]">
                        <option value="USD">US Dollars</option>
                        <option value="EUR" selected>Euros</option>
                        <option value="GBP">British Pound</option>
                    </select>
                </td>
                <td class="dynamic-row-trigger"><input type="text" name="checkout[]"/></td>
                <td><input type="text" class="dollar-amount" name="table_fee[]"/></td>
                <td><input type="text" class="dollar-amount" name="audit_adjust[]"/></td>
                <td></td>
                <td><a class="vendor-del" href="#">
                        <span class="ui-icon ui-icon-circle-close"></span>
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
                <th>Table Fees</th>
                <th>Audit Adjustment</th>
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
                    <td class="table dollar-amount">{{$vendor->tableFee($bazaar)}}</td>
                    <td class="audit dollar-amount">{{$vendor->auditAdjust($bazaar)}}</td>
                    <td><a class="vendor-edit" href="#">
                            <span class="ui-icon ui-icon-pencil"></span>
                        </a></td>
                    <td><a class="vendor-del" href="#">
                            <span class="ui-icon ui-icon-circle-close"></span>
                        </a><span class="vendor-id" style="display: none">{{$vendor->id}}</span></td>
                </tr>
            @empty
                <tr>
                    <td id="empty" colspan="6">This bazaar doesn't have any vendors yet.</td>
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
    <input type="submit" class="next" value="Save"/>
    {!! Form::close() !!}
    <a id="cancel-btn" href="{{ Request::url() }}">
        <button class="cancel">Cancel</button>
    </a>

@stop

@section('scripts')
    <script>

        $("#vendor-table").dynamicTable();

        $(function () {

            $("#vendor-table").tablesorter({
                headers: {
                    4: {sorter: false},
                    5: {sorter: false}
                },
                sortList: [
                    [0, 0]
                ]
            });

            $("#add-vendors-lnk").click(function () {
                $('#empty').hide();
                var element = $('.dynamic-row:first').clone().show().appendTo('#vendor-table tbody');
                vendorAC($(element).find('.vendor-name'));
            });

            $('.vendor-edit').click(function () {
                var newRow = $(".dynamic-row:first").clone().show();
                var oldRow = $(this).closest('tr').replaceWith(newRow);

                newRow.find('input[name="number\[\]"]').val(oldRow.find('.number').text());
                newRow.find('.vendor-name').val(oldRow.find('.vendor-name').text());
                newRow.find('select[name="currency\[\]"]').val(oldRow.find('.payment').text());
                newRow.find('input[name="checkout\[\]"]').val(oldRow.find('.checkout-number').text());
                newRow.find('input[name="id\[\]"]').val(oldRow.find('.vendor-id').text());
                newRow.find('input[name="table_fee\[\]"]').val(oldRow.find('.table').text());
                newRow.find('input[name="audit_adjust\[\]"]').val(oldRow.find('.audit').text());
            });

            $('table').on("click", '.vendor-del', function () {

                var el = $(this).closest('tr').find('.vendor-id');
                var data = '';
                if (el.is('input')) {
                    data = el.val();
                }
                else if (el.is('span')) {
                    data = el.text();
                }

                if (!data) {
                    $(this).closest('tr').fadeOut(300, function () {
                        $(this).closest('tr').detach();
                    });
                }

                else {
                    var del = confirm('Are you sure you want to remove?');
                    if (del == true) {

                        $.get("{{ action('BazaarController@removeVendor', $bazaar) }}", {
                            input: data
                        }, function () {
                            el.closest('tr').fadeOut(300, function () {
                                el.closest('tr').detach();
                            });
                        });
                    }
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

            $(document).on('row-added', function() {
                var element = $('.dynamic-row:last');
                vendorAC($(element).find('.vendor-name'));
            });

            // Run our dollar-formatting algorithm on dollar-related input fields
            $(document).on("blur", ".dollar-amount", function(event) {
                var dollarAmount = formatDollar(this.value);
                this.value = dollarAmount;
            });
        });
    </script>
@stop