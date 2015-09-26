<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Bazaar Buddy</title>

    <link href="{{ asset('jquery/jquery-ui-1.11.4.custom/jquery-ui.min.css') }}" rel="stylesheet">
    <link href="{{ asset('jquery/jquery-ui-1.11.4.custom/jquery-ui.structure.min.css') }}" rel="stylesheet">
    <link href="{{ asset('jquery/jquery-ui-1.11.4.custom/jquery-ui.theme.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet" media="screen">
    <link href="{{ asset('/css/print.css') }}" rel="stylesheet" media="print">
    <script src="{{ asset('jquery/jquery-2.1.3.min.js') }}"></script>
    <script src="{{ asset('jquery/jquery-ui-1.11.4.custom/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('jquery/jquery.tablesorter.min.js') }}"></script>
    <script src="{{ asset('jquery/dynamic-table.js') }}"></script>
    <script src="{{ asset('js/helpers.js') }}"></script>
</head>
<body>
<div class="head">
    <h1>Bazaar Buddy</h1>

    @if(Auth::user())
        <div class="welcome_info">
            Welcome, {{ Auth::user()->name }}!<br/>
            <a href="{{ action('MainController@logout') }}">
                <button name="logout">Logout</button>
            </a>
        </div>
    @endif

</div>
@yield('submenu')
<div class="container">
    @yield('content')
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

        @if(Auth::user() && Auth::user()->isChair())

         $.widget("custom.multisearch", $.ui.autocomplete, {
                    _create: function () {
                        this._super();
                        this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
                    },
                    _renderMenu: function (ul, items) {
                        var that = this;
                        var currentCategory = "";

                        $.each(items, function (index, item) {
                            if (item.category != currentCategory) {
                                $('<li/>').addClass('ui-autocomplete-category').html(item.category).appendTo(ul);
                                currentCategory = item.category;
                            }
                            that._renderItemData(ul, item);
                        });
                    }
                });

        $('#universal-search').multisearch({
            source: function (request, response) {
                $.ajax({
                    url: '{{ action('SearchController@acSearch') }}',
                    data: {
                        q: request.term
                    },
                    success: function (data) {
                        response(data);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(xhr.responseText);
                    }
                });
            },
            select: function (event, ui) {
                var url = "{{ action('SearchController@search') }}?";
                for(var i in ui.item) {
                    url += i + "=" + ui.item[i] + "&";
                }
                window.open(url, '_self');
            }
        });
        @endif


    });

</script>

@yield('scripts')
</body>
</html>