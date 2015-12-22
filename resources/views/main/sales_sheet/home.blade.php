@extends('main')

@section('content')

    <h1>Sales Sheets</h1>
    <div class="form-box">
        <div style="width: 100%; text-align: center">
        <a href="{{ action('SalesSheetController@createInfo') }}" style="text-decoration: none; margin-right: 30px;">
            <button>New Sheet</button>
        </a>
        <a href="{{ action('ValidationController@select') }}">
            <button>Validate a Sheet</button>
        </a>
        </div>
    </div>

    <div id="tabs">
        <ul class="tab-bar">
            <li><a href="#tab1">My Input</a></li>
            <li><a href="#tab2">My Validated</a></li>
        </ul>
        <div id="tab1" class="tab-box">
            <h1 class="left">My Inputted Sales Sheets</h1>

            <table id="sales-sheets" class="clear tablesorter">
                <thead>
                <tr>
                    <th class="sort">Sheet Number <a href="#"></a></th>
                    <th class="sort">Vendor <a href="#"></a></th>
                    <th class="sort">Last Updated <a href="#"></a></th>
                </tr>
                </thead>

                <tbody>
                @forelse($salesSheets as $i => $salesSheet)
                    <tr>
                        <td><a href="{{ action('SalesSheetController@show', $salesSheet->sheet_number) }}">
                                {{ $salesSheet->sheet_number }}</a>
                        </td>
                        <td>{{ $salesSheet->vendorNumber() }}
                            - {{ $salesSheet->vendor->name}}</td>
                        <td>{{ $salesSheet->updated_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            You have no inputted sales sheets.
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>
        <div id="tab2" class="tab-box">
            <h1 class="left">My Validated Sales Sheets</h1>

            <table id="validated-sheets" class="clear tablesorter">
                <thead>
                <tr>
                    <th>Sheet Number</th>
                    <th>Vendor</th>
                    <th>Last Updated</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($validated as $i => $salesSheet)
                    <tr>
                        <td><a href="{{ action('SalesSheetController@show', $salesSheet->sheet_number) }}">
                                {{ $salesSheet->sheet_number }}</a>
                        </td>
                        <td>{{ $salesSheet->vendorNumber() }}
                            - {{ $salesSheet->vendor->name}}</td>
                        <td>{{ $salesSheet->updated_at }}</td>
                        <td>
                            @if($salesSheet->getValidationStatus() != Validated::CORRECT && $salesSheet->getValidationStatus() != Validated::LAYAWAY)
                                <a href="{{ action('ValidationController@show', $salesSheet->sheet_number)}}">
                                    {{ $salesSheet->getValidationStatus() }}
                                </a>
                            @else
                                {{ Validated::CORRECT }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">You have no validated sales sheets.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@stop

@section('scripts')
    <script>
        $(function () {

            // JQuery tabs
            $("#tabs").tabs();

            $("#sales-sheets").tablesorter({
                sortList: [2, 0],
                headers: {3: {sorter: false}}
            });

        });
    </script>
@stop