@if(Session::has('message'))
    <div class="ui-state-highlight ui-corner-all">
        <span class="ui-icon ui-icon-check"></span><span>{{ Session::get('message') }}</span>
    </div>
@endif