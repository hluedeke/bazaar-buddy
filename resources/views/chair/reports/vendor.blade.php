@extends('main')

@section('submenu')
    @include('chair/_menu', ['active_tab' => 2])
@stop

@section('content')
    <div class="text-center">
        <h1>Reports</h1>

        <h2>{{ $vendor->name }}</h2>
    </div>

    <div id="tabs">
        <ul class="tab-bar">
            <li><a href="#tab1">By Sales Sheet</a></li>
            <li><a href="#tab2">By Date</a></li>
            @foreach($dates as $date => $data)
                <li><a href="#{{$data['id']}}">{{$data['day']}} Summary</a></li>
            @endforeach
        </ul>
        <div id="tab1" class="tab-box">
            @include('errors._warning')
            <table border="0" class="numeric">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Sheet Number</th>
                    <th>Cash Totals</th>
                    <th>Credit Card Totals</th>
                    <th>Layaway Totals</th>
                    <th>Total Sales</th>
                    <th>Valid</th>
                </tr>
                </thead>
                <tbody>
                @forelse($vendor->salesSheets() as $sheet)
                    <tr>
                        <td>{{$sheet->date_of_sales->format('m/d/Y')}}</td>
                        <td><a href="{{action('ChairController@review', ['id' => $sheet->sheet_number])}}">
                            {{$sheet->sheet_number}}</a></td>
                        <td>{{Money::format(($sheet->cash()))}}</td>
                        <td>{{Money::format(($sheet->credit()))}}</td>
                        <td>{{Money::format(($sheet->layaway()))}}</td>
                        <td>{{Money::format(($sheet->totalSales()))}}</td>
                        @if(($status = $sheet->getValidationStatus()) == Validated::CORRECT)
                            <td>{{ $status }}</td>
                        @else
                            <td class="error">{{ $status }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">There are no sales sheets entered.</td>
                    </tr>
                @endforelse
                @if(!$vendor->salesSheets()->isEmpty())
                    <tr>
                        <th colspan="7">Totals</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>{{Money::format(($cash = $vendor->cash()))}}</td>
                        <td>{{Money::format(($credit = $vendor->credit()))}}</td>
                        <td>{{Money::format(($layaway = $vendor->layaway()))}}</td>
                        <td>{{Money::format(($totalSales = $vendor->totalSales()))}}</td>
                        @if(($valid = $vendor->isValidated()))
                            <td>yes</td>
                        @else
                            <td class="error">no</td>
                        @endif
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
        <div id="tab2" class="tab-box">
            @include('errors._warning')
            <table border="0" class="numeric">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Cash Totals</th>
                    <th>Credit Card Totals</th>
                    <th>Layaway Totals</th>
                    <th>Total Sales</th>
                </tr>
                </thead>
                <tbody>
                @forelse($dates as $date => $data)
                    <tr>
                        <td>{{$date}}</td>
                        <td>{{Money::format($data[SalesType::CASH])}}</td>
                        <td>{{Money::format($data[SalesType::CARD])}}</td>
                        <td>{{Money::format($data[SalesType::LAYAWAY])}}</td>
                        <td>{{Money::format($data['total'])}}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">There are no dates for this bazaar and vendor.</td>
                    </tr>
                @endforelse
                @if(!empty($dates))
                    <tr>
                        <th colspan="6">Totals</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td>{{Money::format($cash)}}</td>
                        <td>{{Money::format($credit)}}</td>
                        <td>{{Money::format($layaway)}}</td>
                        <td>{{Money::format($totalSales)}}</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
        @foreach($dates as $date => $data)
            <div id="{{$data['id']}}" class="tab-box">
                @include('errors._warning')
                <table border="0" class="numeric">
                    <thead>
                    <tr>
                        <th>Sheet Number</th>
                        <th>Cash Totals</th>
                        <th>Credit Card Totals</th>
                        <th>Layaway Totals</th>
                        <th>Total Sales</th>
                        <th>Valid</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($data['sheets'] as $number => $sheet)
                        <tr>
                            <td>
                                <a href="{{action('ChairController@review', ['id' => $number]) }}">
                                {{$number}}
                                </a>
                            </td>
                            <td>{{Money::format($sheet[SalesType::CASH])}}</td>
                            <td>{{Money::format($sheet[SalesType::CARD])}}</td>
                            <td>{{Money::format($sheet[SalesType::LAYAWAY])}}</td>
                            <td>{{Money::format($sheet['total'])}}</td>
                            @if($sheet['status'] == Validated::CORRECT)
                                <td>{{ $sheet['status'] }}</td>
                            @else
                                <td class="error">{{ $sheet['status'] }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">There are no sales sheets entered.</td>
                        </tr>
                    @endforelse
                    @if(!empty($data['sheets']))
                        <tr>
                            <th colspan="6">Totals</th>
                        </tr>
                        <tr>
                            <td></td>
                            <td>{{Money::format($data[SalesType::CASH])}}</td>
                            <td>{{Money::format($data[SalesType::CARD])}}</td>
                            <td>{{Money::format($data[SalesType::LAYAWAY])}}</td>
                            <td>{{Money::format($data['total'])}}</td>
                            @if($data['status'] == 'yes')
                                <td>{{$data['status']}}</td>
                            @else
                                <td class="error">{{$data['status']}}</td>
                            @endif
                        </tr>
                    @endif
                    </tbody>
                </table>

                <a href="{{action('ReportController@daily', ['id' => $vendor->vendor_number])}}?date={{$data['daily-date']}}" target="_blank">
                    <button style=" margin-top: 4px; float: right">Print Daily</button>
                </a>
                <div style="clear: both"></div>
            </div>
        @endforeach
    </div>
    <a id="print-invoice" href="{{ action('ReportController@invoice', [$vendor->vendor_number]) }}" target="_blank">
        <button class="next">Print Invoice</button>
    </a>
    <a href="{{ action('ReportController@vendorExcel', [$vendor->vendor_number]) }}">
        <button class="cancel">Download Excel</button>
    </a>
    <a href="{{ action('ReportController@index') }}"><button class="cancel">&lt; Back</button></a>
@stop

@section('scripts')
    <script type="text/javascript">
        $(function (ready) {

            $('#tabs').tabs();

        });
    </script>
@stop