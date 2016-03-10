@extends('main')

@section('submenu')
    @include('chair/_menu', ['active_tab' => 4])
@stop

@section('content')
    <div class="text-center">
        <h1>Bazaars</h1>
    </div>
    <div class="text-center" style="line-height: 1.6em; margin-bottom: 2em">
        Current Bazaar: <a href="{{action('BazaarController@show', $curr)}}">
            {{ $curr->name or 'None' }}
        </a><br/>
        @if(isset($curr))
            Dates: {{ $curr->dates() }}
        @endif
    </div>
    <div class="form-box">
        <h2>Past Bazaars</h2>
        <table border="0" cellspacing="5" cellpadding="5">
            <thead>
            <tr>
                <th>Bazaar</th>
                <th>Dates</th>
                <th>Make Current Bazaar</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($bazaars as $bazaar)
                <tr>
                    <td><a href="{{action('BazaarController@show', $bazaar)}}">{{ $bazaar->name }}</a></td>
                    <td>{{ $bazaar->dates() }}</td>
                    <td><a href="{{ action('BazaarController@current', $bazaar)}}"> Make Current Bazaar</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">There are no past bazaars.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="ui-widget-content">
            <a href="{{action('BazaarController@create')}}">
                <span class="ui-icon ui-icon-plusthick"></span>Add a Bazaar
            </a>
        </div>
    </div>
@stop