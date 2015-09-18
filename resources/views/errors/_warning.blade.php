@if(isset($warning))
    <div class="ui-widget">
        <div class="ui-state-error ui-corner-all" style="padding: 0.7em">
            <span class="ui-icon ui-icon-alert"
                  style="float: left; margin-right: 									.3em;"></span>
            {{ $warning }}

        </div>
    </div>
@endif