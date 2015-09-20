@extends('main')

@section('submenu')
    @include('chair._menu', ['active_tab' => 0])
@stop

@section('content')

    <div class="text-center">
        <h1>{{$vendor->name}} Sales Sheets</h1>
    </div>

    <div class="form-box">
        <table id="sort-table" border="0">
            <thead>
            <tr>
                <th>Sheet Number</th>
                <th>Vendor</th>
                <th>Last Updated</th>
                <th>Created By</th>
                <th>Validated By</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sheets as $i => $sheet)
                <tr>
                    <td>
                        <a href="{{action('ChairController@review', ['id' => $sheet->sheet_number])}}">{{$sheet->sheet_number}}</a>
                    </td>
                    <td>{{$vendor->name}}</td>
                    <td>{{$sheet->updated_at}}</td>
                    <td>{{$sheet->createdBy->name or 'N/A'}}</td>
                    <td>{{$sheet->validatedBy->name or 'N/A' }}</td>
                    <td>{{$sheet->getValidationStatus()}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
                    $("#sort-table").tablesorter({
                        sortList: [[0, 0]],
                    });
                }
        );
    </script>
@stop