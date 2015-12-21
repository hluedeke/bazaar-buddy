@extends('main')

@section('submenu')
    @include('chair/_menu', ['active_tab' => 2])
@stop

@section('content')
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
                @foreach($tab['data'] as $i => $row)
                    @if($i !== 'totals')
                        <tr>
                            <td>{{ $row['Vendor Number'] }}</td>
                            <td class="dollar-amount">{{ $row[SalesType::CASH] }}</td>
                            <td class="dollar-amount">{{ $row[SalesType::CARD] }}</td>
                            <td class="dollar-amount">{{ $row[SalesType::LAYAWAY] }}</td>
                            <td class="dollar-amount">{{ $row['Total'] }}</td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <th>TOTALS</th>
                    <th>Cash</th>
                    <th>Credit Card</th>
                    <th>Layaway</th>
                    <th>Total</th>
                </tr>
                <tr>
                    <td></td>
                    <td class="dollar-amount">{{ $tab['data']['totals'][SalesType::CASH] }}</td>
                    <td class="dollar-amount">{{ $tab['data']['totals'][SalesType::CARD] }}</td>
                    <td class="dollar-amount">{{ $tab['data']['totals'][SalesType::LAYAWAY] }}</td>
                    <td class="dollar-amount">{{ $tab['data']['totals']['Total'] }}</td>
                </tr>
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
                    <th>Checkout</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rollup as $i => $row)
                    <tr>
                        @foreach($row as $title => $data)
                            @if(in_array($title, $rollupCols))
                                <td class="dollar-amount">{{ $data }}</td>
                            @else
                                <td>{{ $data }}</td>
                            @endif
                        @endforeach
                        @if($row['Checkout Order'] != 'TOTALS')
                            <td>
                                @if(in_array($row['Vendor Number'], $checked_out))
                                    <input class="checked-out" type="checkbox" name="checked-out"
                                           value="{{$row['Vendor Number']}}" checked/>
                                @else
                                    <input class="checked-out" type="checkbox" name="checked-out"
                                           value="{{$row['Vendor Number']}}"/>
                                @endif
                            </td>
                        @else
                            <td></td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
                <tr>
                    @foreach($rollup[0] as $title=>$data)
                        <th>{{$title}}</th>
                    @endforeach
                    <th>Checkout</th>
                </tr>
            </table>
        </div>
</div>
<a href="{{action('ReportController@rollupExcel')}}">
    <button class="next">Download Excel</button>
</a>
@stop

@section('scripts')
    <script type="text/javascript">
        $(function () {

            $('#bazaar-tabs').tabs();

            $('.checked-out').click(function () {
                var self = this;
                $.ajax({
                    type: 'GET',
                    url: '{{action('ReportController@checkout')}}',
                    data: {
                        id: $(this).val(),
                        checked: this.checked
                    },
                    error: function (jqXHR, status) {
                        $(self).prop('checked', false);
                    }
                });
            });
        });
    </script>
@stop