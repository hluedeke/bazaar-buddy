(function ($) {
    $.fn.dynamicTable = function (options) {

        var settings = $.extend({
            addRowElements: ""
        }, options);

        /* Adds a dynamic row to the table */
        function addRow(event, dynamicTable) {
            event.preventDefault();
            var prevRowNum = parseInt($(dynamicTable).find(".dynamic-row").last()
                .find(".dynamic-row-num").html());

            // Add the new row
            var newRow = $(dynamicTable).find(".dynamic-row").first().clone().show();

            // Set the row number
            newRow.find(".dynamic-row-num").html(prevRowNum + 1);

            // Clear the cloned value and set the focus
            var i = 0;
            newRow.find('input').not('input[type="checkbox"]').each(function () {
                $(this).val('');
                ++i;
            });

            newRow.appendTo(dynamicTable);

            newRow.find('input:first').focus();

            $(document).trigger("row-added");
        }


        /* Main functionality */
        return this.each(function () {
            var dynamicTable = this;

            if (settings.addRowElements != "")
                $(settings.addRowElements).click(function (event) {
                    addRow(event, dynamicTable);
                });

            $(this).on("keydown", ".dynamic-row-trigger", function (event) {

                if (event.which == 9 || event.which == 13) { // Tab or enter is pressed
                    event.preventDefault();
                    if ($(this).parents(".dynamic-row").is(':last-child')) {	// Check for last row
                        addRow(event, dynamicTable);
                    }
                    else {
                        $(this).parents(".dynamic-row").next().find(".dynamic-row-trigger").first().focus();
                    }
                }
            });

            $(this).on("keydown", 'input[type="text"]', function(event) {
                if(event.which == 13 && !$(this).hasClass(".dynamic-row-trigger")) {
                    event.preventDefault();
                    $(this).closest('td').next('td').find(':input').focus();
                }
            });

            $(this).on("click", ".dynamic-row-remove", function (event) {

                if (confirm("Are you sure you want to delete?")) {

                    // Reset row numbers
                    $(this).parents(".dynamic-row").nextAll().each(function () {
                        var rowNumElem = $(this).find(".dynamic-row-num");
                        rowNumElem.html(parseInt(rowNumElem.html() - 1));
                    });

                    $(this).parents(".dynamic-row").find('input').trigger("blur");

                    // Remove the element
                    if ($(this).parents(".dynamic-row").siblings(".dynamic-row").length > 0) {
                        $(this).parents(".dynamic-row").fadeOut(400, 'swing', function () {
                            $(this).remove();
                        });
                    }
                    else
                        $(this).parents(".dynamic-row").find('input').val('');

                    $(document).trigger("row-removed");
                }
            });
        });

    };

}(jQuery));