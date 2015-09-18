@extends('main')

@section('submenu')
    @include('chair/_menu', ['active_tab' => 4])
@stop

@section('content')
    <div class="text-center">
        <h1>New Bazaar</h1>
    </div>

    @include('errors._list')

    {!! Form::open(['action' => 'BazaarController@store']) !!}
    <div class="form-box">
        <div class="form-row">
            <label for="name">Name: </label>
            <input type="text" name="name" size="60" value="{{ Session::get('name') or '' }}"/>
        </div>
        <div class="form-row">
            <label for="name">Start Date: </label>
            <input id="start-date" type="text" name="start_date"
                   value="{{ $start_date }}"/>
        </div>
        <div class="form-row">
            <label for="name">End Date: </label>
            <input id="end-date" type="text" name="end_date"
                   value="{{ $end_date }}"/>
        </div>
    </div>

    <input type="submit" class="next" value="Save"/>
    {!! Form::close() !!}
    <a href="{{ action('BazaarController@index') }}">
        <button class="cancel">Cancel</button>
    </a>
@stop

@section('scripts')
    <script type="text/javascript">
        $(function () {

            $("#start-date, #end-date").datepicker();

        });
    </script>
@stop