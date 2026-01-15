var weekNumberMain = null;
var dayWeek = null;
var statusDayOnCreated = null;
var receiptEmployees = [];
var currentReceiptPdf = null;
var currentReceiptEmployeeId = null;
var currentReceiptAmount = null;
var currentReceiptLocked = false;

function getDurationColumnSpan(durationHours) {
    var hours = parseInt(durationHours, 10);
    if (isNaN(hours) || hours <= 0) {
        return 1;
    }
    return Math.max(1, Math.min(6, hours / 2));
}

function isCellEmpty(cell) {
    return $.trim(cell.html()) === '';
}

function resetRowSlots(row) {
    var cells = row.find('td');
    if (cells.length === 0) {
        return;
    }
    var statusCell = cells.last();
    cells.not(statusCell).remove();
    for (var i = 1; i <= 6; i++) {
        $('<td class="order-slot" data-slot="' + i + '"></td>').insertBefore(statusCell);
    }
    statusCell.html('');
}

function updateReceiptState(receiptPdf) {
    currentReceiptPdf = receiptPdf || null;
    if (receiptPdf) {
        $('#button-open-receipt').removeClass('hidden');
        $('#button-open-receipt').prop('href', '/upload/files/' + receiptPdf);
        $('#order-finished').prop('disabled', false);
    } else {
        $('#button-open-receipt').addClass('hidden');
        $('#button-open-receipt').prop('href', '');
        $('#order-finished').prop('checked', false);
        $('#order-finished').prop('disabled', true);
    }
}

function setReceiptFormState(receiptPdf, employeeId, amount) {
    currentReceiptEmployeeId = employeeId || null;
    currentReceiptAmount = amount || null;
    var employeeName = '';
    if (currentReceiptEmployeeId) {
        var selected = $('#receipt-employee option[value="' + currentReceiptEmployeeId + '"]');
        if (selected.length > 0) {
            employeeName = selected.text();
        }
    }
    $('#receipt-employee-name').val(employeeName);
    $('#receipt-amount-view').val(amount || '');
}

$(document).on('change', '#receipt-employee', function () {
    setReceiptFormState(currentReceiptPdf, $(this).val(), currentReceiptAmount);
});

function renderReceiptEmployees(employees, selectedId) {
    receiptEmployees = employees || [];
    var select = $('#receipt-employee');
    select.empty();
    select.append('<option value=\"\">Выберите сотрудника</option>');
    receiptEmployees.forEach(function (employee) {
        var option = $('<option></option>')
            .val(employee.id)
            .text(employee.fullName);
        if (selectedId && String(employee.id) === String(selectedId)) {
            option.prop('selected', true);
        }
        select.append(option);
    });
    if (selectedId) {
        setReceiptFormState(currentReceiptPdf, selectedId, currentReceiptAmount);
    }
}

function toggleReceiptOverlay(show) {
    if (show) {
        $('#receipt-overlay').removeClass('hidden');
    } else {
        $('#receipt-overlay').addClass('hidden');
    }
}

function setOrderFormLocked(isLocked) {
    currentReceiptLocked = Boolean(isLocked);
    $('#data-order')
        .find('input, select, textarea, button')
        .not('[data-bs-dismiss]')
        .not('#button-open-pdf, #button-open-jpeg, #button-open-receipt')
        .prop('disabled', currentReceiptLocked);
    if (!currentReceiptLocked) {
        $('#button-create-receipt').prop('disabled', false);
    }
}

function showReceiptModal() {
    var modalEl = document.getElementById('receiptModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

function hideReceiptModal() {
    var modalEl = document.getElementById('receiptModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }
    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
}
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
                let span = getDurationColumnSpan(order.durationHours);
                let placed = false;

                for (let slot = 1; slot <= 6; slot++) {
                    let cell = row.find('td.order-slot[data-slot="' + slot + '"]');
                    if (cell.length === 0 || !isCellEmpty(cell)) {
                        continue;
                    }

                    let canFit = true;
                    for (let offset = 0; offset < span; offset++) {
                        let nextCell = row.find('td.order-slot[data-slot="' + (slot + offset) + '"]');
                        if (nextCell.length === 0 || !isCellEmpty(nextCell)) {
                            canFit = false;
                            break;
                        }
                    }

                    if (!canFit) {
                        continue;
                    }

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
                        "data-bs-toggle='offcanvas' data-bs-target='#offcanvasExample' aria-controls='offcanvasExample' " +
                        "data-officeType='" + officeType + "' " +
                        "data-orderId='" + order.id + "'>" +
                        "Заказ №" + order.number +
                        "<div class='order-hours'>" + (order.durationHours || 2) + " ч</div>" +
                        blockImpotent +
                        "</div>"

                    cell.attr('colspan', span);
                    cell.html(orderHtml);
                    for (let offset = 1; offset < span; offset++) {
                        row.find('td.order-slot[data-slot="' + (slot + offset) + '"]').remove();
                    }
                    placed = true;
                    break;
                }

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
        }
    });
}

function clearTable(table) {
    let rows = table.find('tr');
    rows.each(function () {
        resetRowSlots($(this));
    });
}

function addButtonCreateOrder(table, officeType) {
    let rows = table.find('tr');

    rows.each(function () {
        let date = $(this).find('th span').text();
        for (let slot = 1; slot <= 6; slot++) {
            let cell = $(this).find('td.order-slot[data-slot="' + slot + '"]');
            if (cell.length > 0 && isCellEmpty(cell)) {
                cell.html("<button type='button' class='btn btn-secondary create-order-button' " +
                    "data-office-type='" + officeType + "'" +
                    "data-date='" + date + "'" +
                    "data-bs-toggle='offcanvas' data-bs-target='#offcanvasExample' aria-controls='offcanvasExample'" +
                    ">+</button>");
                break;
            }
        }
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
            if (data.result && data.result.error) {
                Toastify({
                    text: data.result.error,
                    close: true,
                    className: "error",
                    backgroundColor: "#f00"
                }).showToast();
                return;
            }
            let tableNov = $('#table-novokuz');
            let tableAr = $('#table-arbat');
            let tableBar = $('#table-barricad');

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
            $('select[name=durationHours]').val(data.result.durationHours || 2);

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

            renderReceiptEmployees(data.result.employees || [], data.result.receiptEmployeeId);
            $('#receipt-amount').val(data.result.receiptAmount || '');
            updateReceiptState(data.result.receiptPdf);
            setReceiptFormState(data.result.receiptPdf, data.result.receiptEmployeeId, data.result.receiptAmount);
            setOrderFormLocked(data.result.isLocked);

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
    $('select[name=durationHours]').val('2');
    updateReceiptState(null);
    renderReceiptEmployees([], null);
    setReceiptFormState(null, null, null);
    setOrderFormLocked(false);

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
$(document).on('click', '#button-create-receipt', function () {
    if ($('input[name=orderId]').val() === '0') {
        Toastify({
            text: "Сначала сохраните заказ",
            close: true,
            className: "error",
            backgroundColor: "#f00"
        }).showToast();
        return;
    }

    if (receiptEmployees.length === 0) {
        Toastify({
            text: "Нет сотрудников для выбранной мастерской",
            close: true,
            className: "error",
            backgroundColor: "#f00"
        }).showToast();
        return;
    }

    if (currentReceiptLocked) {
        Toastify({
            text: "Заказ закрыт для изменений",
            close: true,
            className: "error",
            backgroundColor: "#f00"
        }).showToast();
        return;
    }

    if (currentReceiptPdf) {
        $('#receipt-employee').val(currentReceiptEmployeeId || '');
        $('#receipt-amount').val(currentReceiptAmount || '');
    } else {
        $('#receipt-employee').val('');
        $('#receipt-amount').val('');
    }
    showReceiptModal();
});
$(document).on('submit', '#receipt-form', function (event) {
    event.preventDefault();

    var orderId = $('input[name=orderId]').val();
    if (orderId === '0') {
        Toastify({
            text: "Сначала сохраните заказ",
            close: true,
            className: "error",
            backgroundColor: "#f00"
        }).showToast();
        return;
    }

    var employeeId = $('#receipt-employee').val();
    var amount = $('#receipt-amount').val();

    var amountValue = parseFloat(amount);
    if (!employeeId || !amount || isNaN(amountValue) || amountValue <= 0) {
        Toastify({
            text: "Выберите сотрудника и укажите сумму",
            close: true,
            className: "error",
            backgroundColor: "#f00"
        }).showToast();
        return;
    }

    toggleReceiptOverlay(true);
    $('#button-save-receipt').prop('disabled', true);

    $.ajax({
        url: '/order/create-receipt',
        method: 'post',
        data: {
            'orderId': orderId,
            'employeeId': employeeId,
            'amount': amount
        },
        success: function (data) {
            if (data.result.error) {
                Toastify({
                    text: data.result.error,
                    close: true,
                    className: "error",
                    backgroundColor: "#f00"
                }).showToast();
                return;
            }

            currentReceiptEmployeeId = employeeId;
            currentReceiptAmount = amount;
            updateReceiptState(data.result.receiptPdf);
            setReceiptFormState(data.result.receiptPdf, employeeId, amount);
            Toastify({
                text: "Чек сформирован",
                close: true,
                className: "success",
                backgroundColor: "#11ff00"
            }).showToast();
            hideReceiptModal();
        },
        error: function () {
            Toastify({
                text: "Не удалось сформировать чек",
                close: true,
                className: "error",
                backgroundColor: "#f00"
            }).showToast();
        },
        complete: function () {
            toggleReceiptOverlay(false);
            $('#button-save-receipt').prop('disabled', false);
        }
    });
});
