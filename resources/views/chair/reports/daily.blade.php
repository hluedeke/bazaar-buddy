<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
    <title>Invoice</title>
    <link href="{{ asset('/css/print.css') }}" rel="stylesheet" media="all">
    <script src="{{ asset('jquery/jquery-2.1.3.min.js') }}"></script>
    <script src="{{ asset('js/helpers.js') }}"></script>
</head>
<body onload="window.print()">
@foreach($vendors as $vendor)
    <div class="invoice">
        <h1>{{$bazaar->name}} Daily Sales Report</h1>

        <h2>VENDOR #{{$vendor->vendor_number}} - {{$vendor->name}}</h2>
        <table border="0">
            <thead>
            <tr>
                <th>Date</th>
                <th>Sales Sheet Number</th>
                <th>Cash Sales</th>
                <th>Credit Card Sales</th>
                <th>Layaway Sales</th>
                <th>Total Sales <br/>(US Dollars)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($vendor->salesSheets() as $sheet)
                @if($sheet->date_of_sales->isSameDay($date))
                    <tr>
                        <td>{{$date->format('m/d/y')}}</td>
                        <td>{{$sheet->sheet_number}}</td>
                        <td>${{number_format($sheet->cash(), 2)}}</td>
                        <td>${{number_format($sheet->credit(), 2)}}</td>
                        <td>${{number_format($sheet->layaway(), 2)}}</td>
                        <td>${{number_format($sheet->totalSales(), 2)}}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="totals">
                <td colspan="2">DAILY TOTALS</td>
                <td>${{$totals[$vendor->id]['cash']}}</td>
                <td>${{$totals[$vendor->id]['credit']}}</td>
                <td>${{$totals[$vendor->id]['layaway']}}</td>
                <td>${{$totals[$vendor->id]['total']}}</td>
            </tr>
            </tbody>
        </table>
    </div>
@endforeach
</body>
</html>