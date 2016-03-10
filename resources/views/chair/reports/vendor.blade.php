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
            <table border="0">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Sheet Number</th>
                    <th>Cash Totals</th>
                    <th>Credit Card Totals</th>
                    <th>Layaway Totals</th>
                    <th>Total Sales</th>
                </tr>
                </thead>
                <tbody>
                @forelse($vendor->salesSheets() as $sheet)
                    <tr>
                        <td>{{$sheet->date_of_sales->format('m/d/Y')}}</td>
                        <td>{{$sheet->sheet_number}}</td>
                        <td class="dollar-amount">{{$sheet->cash()}}</td>
                        <td class="dollar-amount">{{$sheet->credit()}}</td>
                        <td class="dollar-amount">{{$sheet->layaway()}}</td>
                        <td class="dollar-amount">{{$sheet->totalSales()}}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">There are no sales sheets entered.</td>
                    </tr>
                @endforelse
                @if(!$vendor->salesSheets()->isEmpty())
                    <tr>
                        <th colspan="6">Totals</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="dollar-amount">{{$vendor->cash()}}</td>
                        <td class="dollar-amount">{{$vendor->credit()}}</td>
                        <td class="dollar-amount">{{$vendor->layaway()}}</td>
                        <td class="dollar-amount">{{$vendor->totalSales()}}</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
        <div id="tab2" class="tab-box">
            @include('errors._warning')
            <table border="0">
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
                        <td class="dollar-amount">{{$data[SalesType::CASH]}}</td>
                        <td class="dollar-amount">{{$data[SalesType::CARD]}}</td>
                        <td class="dollar-amount">{{$data[SalesType::LAYAWAY]}}</td>
                        <td class="dollar-amount">{{$data['total']}}</td>
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
                        <td class="dollar-amount">{{$vendor->cash()}}</td>
                        <td class="dollar-amount">{{$vendor->credit()}}</td>
                        <td class="dollar-amount">{{$vendor->layaway()}}</td>
                        <td class="dollar-amount">{{$vendor->totalSales()}}</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
        @foreach($dates as $date => $data)
            <div id="{{$data['id']}}" class="tab-box">
                @include('errors._warning')
                <table border="0">
                    <thead>
                    <tr>
                        <th>Sheet Number</th>
                        <th>Cash Totals</th>
                        <th>Credit Card Totals</th>
                        <th>Layaway Totals</th>
                        <th>Total Sales</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($data['sheets'] as $number => $sheet)
                        <tr>
                            <td>{{$number}}</td>
                            <td class="dollar-amount">{{$sheet[SalesType::CASH]}}</td>
                            <td class="dollar-amount">{{$sheet[SalesType::CARD]}}</td>
                            <td class="dollar-amount">{{$sheet[SalesType::LAYAWAY]}}</td>
                            <td class="dollar-amount">{{$sheet['total']}}</td>
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
                            <td class="dollar-amount">{{$data[SalesType::CASH]}}</td>
                            <td class="dollar-amount">{{$data[SalesType::CARD]}}</td>
                            <td class="dollar-amount">{{$data[SalesType::LAYAWAY]}}</td>
                            <td class="dollar-amount">{{$data['total']}}</td>
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