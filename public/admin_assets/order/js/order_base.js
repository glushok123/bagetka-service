var weekNumberMain = null;
var dayWeek = null;
var statusDayOnCreated = null;

function getCollectionOrder(officeType = null, page = null) {
    $.ajax({
        url: '/order/get-collection',
        method: 'get',
        data: {
            'officeType': officeType,
            'weekNumber': Number(weekNumberMain),
        },
        async: false,
        success: function (data) {
            if (officeType === 'Новокузнецкая') {
                var tableID = '#table-novokuz';
            }
            if (officeType === 'Арбатская') {
                var tableID = '#table-arbat';
            }
            if (officeType === 'Баррикадная') {
                var tableID = '#table-barricad';
            }

            for (let order of data.result) {
                let row = $(tableID + " span:contains(" + order.createdAt + ")").parents('tr')
                let columns = row.children('td');

                columns.each(function () {
                    if ($(this).html() === "" || $(this).html() === " ") {
                        let classOrder = ''
                        let blockImpotent = ''

                        if (order.isCreateManager === true) {
                            classOrder = 'create-manager'
                        }
                        if (order.isFinished === true) {
                            classOrder = 'create-finished'
                        }
                        if (order.isImportant === true) {
                            blockImpotent = "<div class='ribbon-5'><span>*</span></div>"
                        }
                        if (order.isExpired === true && order.isFinished !== true) {
                            classOrder = 'isExpired'
                        }

                        let orderHtml = "" +
                            "<div class='show-order order " + classOrder + "' " +
                            "data-bs-toggle='offcanvas' data-bs-target='#offcanvasExample' aria-controls='offcanvasExample'" +
                            "data-officeType='" + officeType + "' " +
                            "data-orderId='" + order.id + "'>" +
                            "Заказ №" + order.number + "" +
                            blockImpotent +
                            "</div>"
                        $(this).html(orderHtml)
                        return false;
                    }
                });

                //columns[0].text(order.number)

            }
        },
        error: function (jqXHR, exception) {
            Toastify({
                text: jqXHR.responseJSON.errors,
                close: true,
                className: "error",
                backgroundColor: "#f00"
            }).showToast();
            $('#block-spinner').hide();
        }
    });
}

function getCollectionWeek(weekNumber = null) {
    $('#block-spinner').show();
    $.ajax({
        url: '/order/get-collection-week',
        method: 'get',
        data: {
            'weekNumber': weekNumber,
        },
        async: false,
        success: function (data) {
            dayWeek = data.result.days
            weekNumberMain = data.result.weekNumber
        },
        error: function (jqXHR, exception) {
            Toastify({
                text: jqXHR.responseJSON.errors,
                close: true,
                className: "error",
                backgroundColor: "#f00"
            }).showToast();
            $('#block-spinner').hide();
        }
    });
}

function clearTable(table) {
    let columns = table.find('td');
    columns.each(function () {
        $(this).html(" ")
    })
}

function addButtonCreateOrder(table, officeType) {
    let rows = table.find('tr');

    rows.each(function () {
        let columns = $(this).find('td');
        let date = $(this).find('th span').text();

        columns.each(function () {
            if ($(this).html() === "" || $(this).html() === " ") {
                $(this).parent('tr').find('but-update-status-day')

                $(this).html("<button type='button' class='btn btn-secondary create-order-button' " +
                    "data-office-type='" + officeType + "'" +
                    "data-date='" + date + "'" +
                    "data-bs-toggle='offcanvas' data-bs-target='#offcanvasExample' aria-controls='offcanvasExample'" +
                    ">+</button>")
                return false;
            }
        })
    })
}

function validOrder() {
    if ($('input[name=number]').val().length === 0) {
        Toastify({
            text: "Заполните номер заказа",
            close: true,
            className: "error",
            backgroundColor: "#f00"
        }).showToast();
        return false;
    }

    /*if ($('input[name=phone]').val().length === 0) {
        Toastify({
            text: "Заполните номер телефона",
            close: true,
            className: "error",
            backgroundColor: "#f00"
        }).showToast();
        return false;
    }*/

    return true;
}

function checkStatusDay(officeType, date){
    $.ajax({
        url: '/order/check-status-day',
        method: 'post',
        data: {
            'officeType': officeType,
            'day': date,
        },
        async: false,
        success: function (data) {
            statusDayOnCreated = data.result.status
        },
    });
}
function saveOrder() {
    if (validOrder() === false) {
        return false;
    }

    let formData = new FormData($('#data-order')[0])

    if ($('input[name=orderId]').val() === '0') {
        url = '/order/create';

        checkStatusDay($('select[name=officeType]').val(), $('input[name=date]').val())
        if(statusDayOnCreated === true){
            Toastify({
                text: "День закрыт.",
                close: true,
                className: "error",
                backgroundColor: "#f00"
            }).showToast();
            return false;
        }
    } else {
        url = '/order/update';
    }

    $.ajax({
        url: url,
        method: 'post',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        success: function (data) {
            let tableNov = $('#table-novokuz');
            let tableAr = $('#table-arbat');
            let tableBar = $('#table-barricad');

            $('#block-spinner').show();

            clearTable(tableNov)
            clearTable(tableAr)
            clearTable(tableBar)

            getCollectionWeek(weekNumberMain);

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

            Toastify({
                text: "Заказ добавлен",
                close: true,
                className: "success",
                backgroundColor: "#11ff00"
            }).showToast();
        },
        error: function (jqXHR, exception) {
            if (jqXHR.status === 0) {
                alert('Not connect. Verify Network.');
            } else if (jqXHR.status == 404) {
                alert('Requested page not found (404).');
            } else if (jqXHR.status == 500) {
                alert('Internal Server Error (500).');
            } else if (exception === 'parsererror') {
                alert('Requested JSON parse failed.');
            } else if (exception === 'timeout') {
                alert('Time out error.');
            } else if (exception === 'abort') {
                alert('Ajax request aborted.');
            } else {
                alert('Uncaught Error. ' + jqXHR.responseText);
            }
        }
    });
}

function showOrder(orderId) {
    $.ajax({
        url: '/order/get',
        method: 'get',
        data: {
            'id': orderId,
        },
        async: false,
        success: function (data) {
            $('input[name=number]').val(data.result.number)
            $('input[name=phone]').val(data.result.phone)
            $('input[name=orderId]').val(data.result.id)
            $('textarea[name=comment]').val(data.result.comment ?? '');
            var euro_date = data.result.createdAt;
            euro_date = euro_date.split('.');
            var us_date = euro_date.reverse().join('-');

            $('input[name=date]').val(us_date)

            //$("select[name=officeType] option[value=Новокузнецкая]").prop('selected', false);
            //$("select[name=officeType] option[value=Арбатская]").prop('selected', false);
            //$("option[value=Баррикадная]").removeAttr('selected');
            //$("option[value=Баррикадная]").prop('selected', -1);
            //$("option[value=Баррикадная]")[0].prop('selected', false);
            //$("select[name=officeType] option:selected").prop("selected", false)
            $("select[name=officeType]").val([]);
            $('select[name=officeType]').val('')
            $("select[name=officeType] option").prop("selected", false);
            //$("select[name=officeType] option[value=" + data.result.officeType + "]").prop('selected', true);
            //$("select[name=officeType] option[value=Баррикадная]").prop('selected', false);
            $("select[name=officeType]").val(data.result.officeType);

            if (data.result.isImportant === true) {
                $('input[name=isImportant]').prop('checked', true);
            } else {
                $('input[name=isImportant]').prop('checked', false);
            }

            if (data.result.isFinished === true) {
                $('input[name=isFinished]').prop('checked', true);
            } else {
                $('input[name=isFinished]').prop('checked', false);
            }

            if(data.result.pdf === null){
                $('#button-open-pdf').addClass('hidden');
            }else{
                $('#button-open-pdf').removeClass('hidden');
                $('#button-open-pdf').prop('href', '/upload/files/' + data.result.pdf)
            }

            if (data.result.jpeg === null) {
                $('#button-open-jpeg').addClass('hidden');
                $('#jpeg-thumbnail-wrapper').hide();
            } else {
                const jpegUrl = '/upload/files/' + data.result.jpeg;

                $('#button-open-jpeg').removeClass('hidden');
                $('#button-open-jpeg').prop('href', jpegUrl);

                $('#jpeg-thumbnail').prop('src', jpegUrl);
                $('#jpeg-thumbnail-wrapper').show();
            }
        },
        error: function (jqXHR, exception) {
            Toastify({
                text: jqXHR.responseJSON.errors,
                close: true,
                className: "error",
                backgroundColor: "#f00"
            }).showToast();
            $('#block-spinner').hide();
        }
    });
}

function clearFormOrder(officeType, date) {
    $('input[name=number]').val('')
    $('input[name=orderId]').val('0')
    $('input[name=phone]').val('')
    $('input[name=isFinished]').prop('checked', false);
    $('input[name=isImportant]').prop('checked', false);
    $('#button-open-pdf').removeClass('hidden');

    var euro_date = date;
    euro_date = euro_date.split('.');
    var us_date = euro_date.reverse().join('-');

    $('input[name=date]').val(us_date)

    $("select[name=officeType]").val([]);
    $('select[name=officeType]').val('')
    $("select[name=officeType] option").prop("selected", false);
    //$("select[name=officeType] option[value=" + data.result.officeType + "]").prop('selected', true);
    //$("select[name=officeType] option[value=Баррикадная]").prop('selected', false);
    $("select[name=officeType]").val(officeType);
    $('#jpeg-thumbnail-wrapper').hide();
    $('#jpeg-thumbnail').prop('src', '');
}

function deleteOrder() {
    if (confirm('Вы уверены что хотите удалить заказ?') === true) {
        $.ajax({
            url: '/order/remove',
            method: 'post',
            data: {
                'orderId': $('input[name=orderId]').val(),
            },
            async: false,
            success: function (data) {
                location.reload()
            },
            error: function (jqXHR, exception) {
                Toastify({
                    text: jqXHR.responseJSON.errors,
                    close: true,
                    className: "error",
                    backgroundColor: "#f00"
                }).showToast();
                $('#block-spinner').hide();
            }
        });
    }
}

function updateStatusDay(type, officeType, date) {
    let q = '';
    let typeDay = '';
    console.log(type)
    if (type === "close"){
        q = 'Открыть день?'
        typeDay = false
        console.log(123)
    }

    if (type === "open"){
        q = 'Закрыть день?'
        typeDay = true
        console.log(456)
    }
    if (confirm(q) === true) {
        console.log(typeDay)
        $.ajax({
            url: '/order/update-status-day',
            method: 'post',
            data: {
                'officeType': officeType,
                'day': date,
                'typeDay': typeDay,
            },
            async: false,
            success: function (data) {
                location.reload()
            },
            error: function (jqXHR, exception) {
                Toastify({
                    text: jqXHR.responseJSON.errors,
                    close: true,
                    className: "error",
                    backgroundColor: "#f00"
                }).showToast();
                $('#block-spinner').hide();
            }
        });
    }
}

function setDateTable(table, officeType) {
    table.find('.mo').text(dayWeek[0].mo.date)
    table.find('.tu').text(dayWeek[1].tu.date)
    table.find('.we').text(dayWeek[2].we.date)
    table.find('.th').text(dayWeek[3].th.date)
    table.find('.fr').text(dayWeek[4].fr.date)
    table.find('.sa').text(dayWeek[5].sa.date)
    table.find('.su').text(dayWeek[6].su.date)

    table.find('.mo-2').text(dayWeek[7].mo.date)
    table.find('.tu-2').text(dayWeek[8].tu.date)
    table.find('.we-2').text(dayWeek[9].we.date)
    table.find('.th-2').text(dayWeek[10].th.date)
    table.find('.fr-2').text(dayWeek[11].fr.date)
    table.find('.sa-2').text(dayWeek[12].sa.date)
    table.find('.su-2').text(dayWeek[13].su.date)


    if (officeType === 'Новокузнецкая') {
        var butMo = dayWeek[0].mo.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[0].mo.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[0].mo.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTu = dayWeek[1].tu.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[1].tu.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[1].tu.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butWe = dayWeek[2].we.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[2].we.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[2].we.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTh = dayWeek[3].th.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[3].th.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[3].th.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butFr = dayWeek[4].fr.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[4].fr.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[4].fr.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSa = dayWeek[5].sa.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[5].sa.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[5].sa.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSu = dayWeek[6].su.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[6].su.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[6].su.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";

        var butMo2 = dayWeek[7].mo.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[7].mo.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[7].mo.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTu2 = dayWeek[8].tu.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[8].tu.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[8].tu.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butWe2 = dayWeek[9].we.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[9].we.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[9].we.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTh2 = dayWeek[10].th.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[10].th.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[10].th.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butFr2 = dayWeek[11].fr.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[11].fr.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[11].fr.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSa2 = dayWeek[12].sa.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[12].sa.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[12].sa.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSu2 = dayWeek[13].su.statusNov === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[13].su.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[13].su.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
    }
    if (officeType === 'Арбатская') {
        var butMo = dayWeek[0].mo.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[0].mo.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[0].mo.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTu = dayWeek[1].tu.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[1].tu.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[1].tu.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butWe = dayWeek[2].we.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[2].we.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[2].we.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTh = dayWeek[3].th.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[3].th.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[3].th.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butFr = dayWeek[4].fr.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[4].fr.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[4].fr.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSa = dayWeek[5].sa.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[5].sa.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[5].sa.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSu = dayWeek[6].su.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[6].mo.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[6].su.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";

        var butMo2 = dayWeek[7].mo.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[7].mo.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[7].mo.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTu2 = dayWeek[8].tu.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[8].tu.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[8].tu.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butWe2 = dayWeek[9].we.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[9].we.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[9].we.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTh2 = dayWeek[10].th.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[10].th.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[10].th.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butFr2 = dayWeek[11].fr.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[11].fr.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[11].fr.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSa2 = dayWeek[12].sa.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[12].sa.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[12].sa.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSu2 = dayWeek[13].su.statusArbat === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[13].su.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[13].su.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
    }

    if (officeType === 'Баррикадная') {
        var butMo = dayWeek[0].mo.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[0].mo.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[0].mo.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTu = dayWeek[1].tu.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[1].tu.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[1].tu.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butWe = dayWeek[2].we.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[2].we.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[2].we.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTh = dayWeek[3].th.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[3].th.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[3].th.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butFr = dayWeek[4].fr.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[4].fr.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[4].fr.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSa = dayWeek[5].sa.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[5].sa.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[5].sa.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSu = dayWeek[6].su.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[6].su.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[6].su.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";

        var butMo2 = dayWeek[7].mo.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[7].mo.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[7].mo.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTu2 = dayWeek[8].tu.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[8].tu.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[8].tu.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butWe2 = dayWeek[9].we.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[9].we.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[9].we.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butTh2 = dayWeek[10].th.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[10].th.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[10].th.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butFr2 = dayWeek[11].fr.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[11].fr.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[11].fr.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSa2 = dayWeek[12].sa.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[12].sa.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[12].sa.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
        var butSu2 = dayWeek[13].su.statusBar === true ? "<button data-type='close' data-office-type='" + officeType + "' data-date='" + dayWeek[13].su.date + "' type='button' class='but-update-status-day btn btn-info'>Закрыт</button>" : "<button data-type='open' data-office-type='" + officeType + "' data-date='" + dayWeek[13].su.date + "' type='button' class='but-update-status-day btn btn-success'>Открыт</button>";
    }

    table.find('.status-mo').html(butMo)
    table.find('.status-tu').html(butTu)
    table.find('.status-we').html(butWe)
    table.find('.status-th').html(butTh)
    table.find('.status-fr').html(butFr)
    table.find('.status-sa').html(butSa)
    table.find('.status-su').html(butSu)

    table.find('.status-mo-2').html(butMo2)
    table.find('.status-tu-2').html(butTu2)
    table.find('.status-we-2').html(butWe2)
    table.find('.status-th-2').html(butTh2)
    table.find('.status-fr-2').html(butFr2)
    table.find('.status-sa-2').html(butSa2)
    table.find('.status-su-2').html(butSu2)
}

$(document).on('click', '.create-order-button', function () {
    clearFormOrder($(this).data('office-type'), $(this).data('date'));
});
$(document).on('click', '#button-save-order', function () {
    saveOrder();
});
$(document).on('click', '#button-save-delete', function () {
    deleteOrder();
});
$(document).on('click', '.but-update-status-day ', function () {
    updateStatusDay($(this).data('type'), $(this).data('office-type'), $(this).data('date'));
});
$(document).on('click', '.show-order', function () {
    showOrder($(this).data('orderid'));
});