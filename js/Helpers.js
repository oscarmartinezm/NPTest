Helpers = {
    dataTable: function (selector, noOrderableCols, addColumnSearch, columnOrder, paging, searching, noSearchableCols) {
        var ordering;
        if (columnOrder === false) {
            ordering = false;
        } else if (columnOrder) {
            ordering = true;
        } else {
            ordering = true;
            columnOrder = 0;
        }
        if (paging !== false) {
            paging = true;
        }
        if (searching !== false) {
            searching = true;
        }
        if (!noOrderableCols) {
            noSearchableCols = []
        }
        if (!noSearchableCols) {
            noSearchableCols = []
        }
        if (addColumnSearch) {
            var thead = $(selector + ' thead tr').clone();
            $('th', thead).each(function () {
                var ele = $(this);
                if (ele.hasClass('column-icon')) {
                    ele.text('');
                } else {
                    ele.removeClass();
                }
            });
            $(selector).append($('<tfoot>').append(thead));
        }
        var options = {
            order: [[columnOrder, 'asc']],
            ordering: ordering,
            paging: paging,
            searching: searching,
            columnDefs: [{targets: noOrderableCols, orderable: false}, {targets: noSearchableCols, searchable: false}],
            oSearch: {bSmart: false, bRegex: true, sSearch: '', bCaseInsensitive: true}
        };
        if (addColumnSearch && !searching) {
            options['searching'] = true;
            options['sDom'] = '<"col-sm-12"l><"col-sm-12"t><"col-sm-5"i><"col-sm-7"p>';
        }
        var grid = $(selector).DataTable(options);
        if (addColumnSearch) {
            $(selector + ' tfoot th:not(.column-icon)').each(function () { // Setup - add a text input to each footer cell
                //var title = $(this).text();
                //$(this).html('<input type="text" style="width: 95%; margin: 0 2.5%; text-indent: 5px;"  placeholder="Search ' + title + '" />');
                $(this).html('<input type="text" style="width: 95%; margin: 0 2.5%; text-indent: 5px;" />');
            });
            $(selector + ' tfoot tr').insertAfter($(selector + ' thead tr'));
            grid.columns().every(function () { // Apply the search
                var that = this;
                $('input', this.footer()).on('keyup change', function () {
                    if (that.search() !== this.value) {
                        that.search(this.value).draw();
                    }
                });
            });
        }
        return grid;
    },
    validator: function (selector, fields) {
        var validator = $(selector).bootstrapValidator({
            feedbackIcons: {
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: fields
        });
        return validator;
    },
};
