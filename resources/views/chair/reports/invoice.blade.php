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
        <h1>{{$bazaar->name}} Vendor Invoice</h1>

        <h2>VENDOR #{{$vendor->vendor_number}} - {{$vendor->name}}</h2>
        <table border="0">
            <thead>
            <tr>
                <th>Date</th>
                <th>Cash Sales</th>
                <th>Credit Card Sales</th>
                <th>{{$fees['credit']}}% Credit <br/>Card Fee</th>
                <th>Layaway</th>
                <th>Total Sales <br/>(US Dollars)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data[$vendor->id] as $date => $row)
                <tr>
                    <td>{{$date}}</td>
                    <td>${{$row['cash']}}</td>
                    <td>${{$row['credit']}}</td>
                    <td>${{$row['cc_fee']}}</td>
                    <td>${{$row['layaway']}}</td>
                    <td>${{$row['total']}}</td>
                </tr>
            @endforeach
            <tr class="totals">
                <td>TOTALS</td>
                <td>${{$totals[$vendor->id]['cash']}}</td>
                <td>${{$totals[$vendor->id]['credit']}}</td>
                <td>${{$totals[$vendor->id]['cc_fee']}}</td>
                <td>${{$totals[$vendor->id]['layaway']}}</td>
                <td>${{$totals[$vendor->id]['total']}}</td>
            </tr>
            </tbody>
        </table>
        <div class="two-col">
            <div class="logo">
                <!--<img src="/images/logo.jpg" alt="AOCSC Logo"/>-->
                <h1>Thank you!</h1>
                <p>
                    The {{ $bazaar->organization or '' }} would like to thank you for your participation
                    in the {{$bazaar->name}}! You help make
                    what we do possible!
                </p>
            </div>
        </div>
        <div class="two-col">
            <h1>{{$bazaar->abbreviation or ''}} DEDUCTIONS</h1>

            <table class="label-group" border="0">
                <tbody>
                <tr>
                    <td>{{$bazaar->abbreviation or ''}} {{$fees['bazaar']}}%</td>
                    <td>${{$totals[$vendor->id]['b_fee']}}</td>
                </tr>
                <tr>
                    <td>${{$fees['credit']}}% Credit Card Fees</td>
                    <td>${{$totals[$vendor->id]['cc_fee']}}</td>
                </tr>
                <tr>
                    <td>Table Fee</td>
                    <td>${{ $totals[$vendor->id]['table_fee'] }}</td>
                </tr>
                <tr>
                    <td>Audit Adjustments</td>
                    <td>${{ $totals[$vendor->id]['audit_adjust'] }}</td>
                </tr>
                <tr class="totals">
                    <td>Total {{$bazaar->abbreviation or ''}} Deductions</td>
                    <td>${{$totals[$vendor->id]['deduct']}}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="total-line">
            <div class="total-owed-label">
                <p>
                    TOTAL AMOUNT OWED TO VENDOR (US DOLLARS):
                </p>
            </div>
            <div class="total-owed">
                <p>${{$totals[$vendor->id]['owed']}}</p>
            </div>
        </div>
        <div class="disclaimer">
            Please Note: Invoice totals are subject to change after the {{$bazaar->name}} Business Office
            conducts quality control audit. Audit will be conducted within 72 hours of the close of the bazaar.
            Vendor will be notified via email if invoice totals are adjusted.
        </div>
    </div>
@endforeach
</body>
</html>