<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
    <title>Invoice</title>
    <link href="{{ asset('/css/print.css') }}" rel="stylesheet" media="all">
    <script src="{{ asset('jquery/jquery-2.1.3.min.js') }}"></script>
    <script src="{{ asset('js/helpers.js') }}"></script>
</head>
<body onload="window.print()">
<div class="invoice">
    <h1>{{$bazaar->name}} Vendor Invoice</h1>

    <h2>VENDOR #{{$vendor->vendor_number}} - {{$vendor->name}}</h2>
    <table border="0">
        <thead>
        <tr>
            <th>Date</th>
            <th>Cash Sales</th>
            <th>Credit Card Sales</th>
            <th>{{$fees['credit']}}% Credit <br />Card Fee</th>
            <th>Layaway</th>
            <th>Total Sales <br />(US Dollars)</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $date => $row)
        <tr>
            <td>{{$date}}</td>
            <td class="dollar-amount">{{$row['cash']}}</td>
            <td class="dollar-amount">{{$row['credit']}}</td>
            <td class="dollar-amount">{{$row['cc_fee']}}</td>
            <td class="dollar-amount">{{$row['layaway']}}</td>
            <td class="dollar-amount">{{$row['total']}}</td>
        </tr>
        @endforeach
        <tr class="totals">
            <td>TOTALS</td>
            <td class="dollar-amount">{{$vendor->cash()}}</td>
            <td class="dollar-amount">{{$vendor->credit()}}</td>
            <td class="dollar-amount">{{$vendor->credit() * $fees['credit']}}</td>
            <td class="dollar-amount">{{$vendor->layaway()}}</td>
            <td class="dollar-amount">{{$vendor->totalSales()}}</td>
        </tr>
        </tbody>
    </table>
    <div class="two-col">
        <div class="logo">
            <img src="/images/logo.jpg" alt="AOCSC Logo"/>

            <p>
                The Aviano Officers & Civilians Spouses'
                Club would like to thank you for your participation
                in the 2014 Bella Befana Bazaar! You help make
                what we do possible!
            </p>
        </div>
    </div>
    <div class="two-col">
        <h1>BBB DEDUCTIONS</h1>

        <table class="label-group" border="0">
            <tbody>
            <tr>
                <td>BBB {{$fees['bazaar']}}%</td>
                <td class="dollar-amount">{{$vendor->totalSales() * $fees['bazaar']}}</td>
            </tr>
            <tr>
                <td>{{$fees['credit']}}% Credit Card Fees</td>
                <td class="dollar-amount">{{$vendor->totalSales() * $fees['credit']}}</td>
            </tr>
            <tr>
                <td>On Site Table/Bench Fees</td>
                <td class="dollar-amount">0</td>
            </tr>
            <tr>
                <td>Audit Adjustments</td>
                <td class="dollar-amount">0</td>
            </tr>
            <tr class="totals">
                <td>Total BBB Deductions</td>
                <td class="dollar-amount">0</td>
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
            <p class="dollar-amount">0</p>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $('.dollar-amount').each(function () {
            if (this.nodeName == 'INPUT') {
                var dollarAmount = formatDollar(this.value);
                this.value = dollarAmount;
            }
            else {
                var dollarAmount = formatDollar(this.innerHTML);
                this.innerHTML = dollarAmount;
            }
        });
    });
</script>
</body>
</html>