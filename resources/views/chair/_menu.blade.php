<div class="menu">
    <ul>
        <li>
            @if($active_tab == 1)
                <a class="active" href="{{ action('ChairController@index') }}">Review</a>
            @else
                <a href="{{ action('ChairController@index') }}">Review</a>
            @endif
        </li>
        |
        <li>@if($active_tab == 2)
                <a class="active" href="{{ action('ReportController@index') }}">Report</a>
            @else
                <a href="{{ action('ReportController@index') }}">Report</a>
            @endif
        </li>
        |
        <li>@if($active_tab == 3)
                <a class="active" href="{{ action('VolunteerController@index') }}">Volunteers</a>
            @else
                <a href="{{ action('VolunteerController@index') }}">Volunteers</a>
            @endif
        </li>
        |
        <li>
            @if($active_tab == 4)
                <a class="active" href="{{ action('BazaarController@index') }}">Bazaars</a>
            @else
                <a href="{{ action('BazaarController@index') }}">Bazaars</a>
            @endif
        </li>
        |
    </ul>

    <div class="search-inline">
        <input type="text" name="universal-search" id="universal-search" />

        <div class="ui-corner-all">
            <button id="universal-search-btn" type="submit" class="ui-icon ui-icon-search"/>
        </div>
    </div>
</div>