$(document).ready(function () {
    var tableNov = $('#table-novokuz');
    var tableAr = $('#table-arbat');
    var tableBar = $('#table-barricad');


    //getCollectionOrder();
    //$(document).on('click', '#button-upload', function () { uploadFilesRequest() });

    updateWeekDay()

    function updateWeekDay(weekNumberNew = null){
        $('#block-spinner').show();

        clearTable(tableNov)
        clearTable(tableAr)
        clearTable(tableBar)

        getCollectionWeek(weekNumberNew);

        setDateTable(tableNov, 'Новокузнецкая');
        setDateTable(tableAr, 'Арбатская');
        setDateTable(tableBar, 'Баррикадная');

        getCollectionOrder('Новокузнецкая');
        getCollectionOrder('Арбатская');
        getCollectionOrder('Баррикадная');

        addButtonCreateOrder(tableNov, 'Новокузнецкая');
        addButtonCreateOrder(tableAr, 'Арбатская');
        addButtonCreateOrder(tableBar, 'Баррикадная');

        $('#block-spinner').hide();
    }

    $(document).on('click', '.but-pagination.left', function () {
        updateWeekDay(weekNumberMain - 1)
    });
    $(document).on('click', '.but-pagination.right', function () {
        updateWeekDay(weekNumberMain + 1)
    });
});
