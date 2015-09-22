@extends('main')

@section('submenu')
    @include('chair/_menu', ['active_tab' => 2])
@stop

@section('content')
    <div class="text-center">
        <h1>Reports</h1>

        <h2>{{ $bazaar }}</h2>
    </div>

    <div id="bazaar-tabs">
        <ul class="tab-bar">
            @foreach($data as $id => $tab)
                <li><a href="#{{$id}}">{{ $tab['title'] }}</a></li>
            @endforeach
            <li><a href="#bazaar-tab-rollup">Roll Up</a></li>
        </ul>
        @foreach($data as $id => $tab)
            <div id="{{$id}}" class="tab-box">
                @include('errors._warning')
                <div class="text-center"><h2>{{ $tab['title'] }}</h2></div>
                <table border="0">
                    <thead>
                    <tr>
                        <th>Vendor Number</th>
                        <th>Cash</th>
                        <th>Credit Card</th>
                        <th>Layaway</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tab['data'] as $row)
                        <tr>
                            <td>{{ $row['Vendor Number'] }}</td>
                            <td class="dollar-amount">{{ $row['Cash'] }}</td>
                            <td class="dollar-amount">{{ $row['Credit Card'] }}</td>
                            <td class="dollar-amount">{{ $row['Layaway'] }}</td>
                            <td class="dollar-amount">{{ $row['Total'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                @if(isset($tab['date']))
                    <a href="{{action('ReportController@daily')}}?date={{$tab['date']}}">
                        <button style=" margin-top: 4px; float: right">Print Daily</button>
                    </a>
                    <div style="clear: both"></div>
                @endif

            </div>
            @endforeach

                    <!-- Roll Up -->
            <div id="bazaar-tab-rollup" class="tab-box">
                @include('errors._warning')
                <div class="text-center"><h2>Roll Up</h2></div>
                <table border="0">
                    <thead>
                    <tr>
                        @foreach($rollup[0] as $title=>$data)
                            <th>{{$title}}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rollup as $row)
                        <tr>
                            @foreach($row as $title => $data)
                                @if(in_array($title, $rollupCols))
                                    <td class="dollar-amount">{{ $data }}</td>
                                @else
                                    <td>{{ $data }}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                    <tr>
                        @foreach($rollup[0] as $title=>$data)
                            <th>{{$title}}</th>
                        @endforeach
                    </tr>
                </table>
            </div>
    </div>
    <a href="{{action('ReportController@rollupExcel')}}">
        <button class="next">Download Excel</button>
    </a>

    <div class="text-center"><h2 style="margin-top: 20px">Reports by Vendor</h2></div>
    <div class="form-box" style="margin: 20px 0">
        {!! Form::open(array('action' => 'ReportController@vendor')) !!}
        <label for="vendor">Vendor: </label>
        <input id="vendor2" type="text" name="vendor"/>
        <input type="submit" class="next" value="Search"/>
        {!! Form::close() !!}
    </div>

@stop

@section('scripts')
    <script type="text/javascript">
        $(function () {

            $('#bazaar-tabs').tabs();

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