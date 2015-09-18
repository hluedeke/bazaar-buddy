@extends('main)

@section('submenu')
    @include('chair/_menu', ['active_tab' => 1])
@stop

@section('content')
    <div class="text-center"><h1>{{ $vendor->name }}</h1>
        <h2>Sheet {{ $sheet->sheet_number }}</h2></div>
@stop

@stop