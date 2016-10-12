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
            <table border="0" class="numeric">
                <thead>
                <tr>
                    <th>Vendor Number</th>
                    <th>Cash</th>
                    <th>Credit Card</th>
                    <th>Layaway</th>
                    <th>Total</th>
                    <th>Valid</th>
                </tr>
                </thead>
                <tbody>
                @foreach($tab['data'] as $i => $row)
                    @if($i !== 'totals')
                        <tr>
                            <td>
                                <a href="/chair/reports/vendor/{{ $row['id'] }}" target="vendor">
                                    {{ $row['Vendor Number'] }}
                                </a>
                            </td>
                            <td>{{ Money::format($row[SalesType::CASH]) }}</td>
                            <td>{{ Money::format($row[SalesType::CARD]) }}</td>
                            <td>{{ Money::format($row[SalesType::LAYAWAY]) }}</td>
                            <td>{{ Money::format($row['Total']) }}</td>
                            @if($row['Valid'] == 'no')
                                <td class="error">{{ $row['Valid'] }}</td>
                            @else
                                <td>{{ $row['Valid'] }}</td>
                            @endif
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <th>TOTALS</th>
                    <th>Cash</th>
                    <th>Credit Card</th>
                    <th>Layaway</th>
                    <th>Total</th>
                    <th>Valid</th>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ Money::format($tab['data']['totals'][SalesType::CASH]) }}</td>
                    <td>{{ Money::format($tab['data']['totals'][SalesType::CARD]) }}</td>
                    <td>{{ Money::format($tab['data']['totals'][SalesType::LAYAWAY]) }}</td>
                    <td>{{ Money::format($tab['data']['totals']['Total']) }}</td>
                    @if($tab['data']['totals']['Valid'] == 'no')
                        <td class="error">{{ $tab['data']['totals']['Valid'] }}</td>
                    @else
                        <td>{{ $tab['data']['totals']['Valid'] }}</td>
                    @endif
                </tr>
                </tbody>
            </table>

            @if(isset($tab['date']))
                <a href="{{action('ReportController@daily')}}?date={{$tab['date']}}" target="print">
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
            <table border="0" class="numeric">
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
                            @if($title == 'Valid' && $data == 'no')
                                <td class="error">{{ $data }}</td>
                            @elseif($title == 'Vendor Number')
                                <td>
                                    <a href="/chair/reports/vendor/{{ $data }}" target="vendor">
                                        {{ $data }}
                                    </a></td>
                            @else
                                <td>{{ Money::format($data, true) }}</td>
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