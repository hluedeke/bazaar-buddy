@extends('main')

@section('submenu')
    @include('chair/_menu', ['active_tab' => 2])
@stop

@section('content')
    <div class="text-center">
        <h1>Reports</h1>

        <h2>{{$bazaar->name}}</h2>
    </div>

    <div class="form-box">
        {!! Form::open(array('action' => 'ReportController@report')) !!}
        <label for="vendor">Vendor: </label>
        <input id="vendor2" type="text" name="vendor"/> ** Leave blank for all vendors/rollup
        <input type="submit" name="search" class="next" value="Search"/>
        @if($print == 'daily')
            <input type="submit" name="print_daily" class="cancel" value="Print Daily"/>
        @elseif($print == 'invoice')
            <input type="submit" name="print_invoice" class="cancel" value="Print Invoice"/>
        @endif
        {!! Form::close() !!}
    </div>

@stop

@section('scripts')
    <script type="text/javascript">
        $(function () {
            $("#datepicker").datepicker();

            $("#vendor, #vendor2").autocomplete({
                source: function (request, response) {
                    $.get("{{ action('HelperController@acVendor') }}", {
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