var listNov = $('#block-list-novokuz');
var listAr = $('#block-list-arbat');
var listBar = $('#block-list-barikad');
var tableAll = $('#table-list-materials');

function getCollectionMaterials(officeType = null, page = null) {
    $.ajax({
        url: '/materials/get-collection',
        method: 'get',
        data: {
            'officeType': officeType,
        },
        async: false,
        success: function (data) {
            if (officeType === 'Новокузнецкая') {
                var listCurrent = listNov;
            }

            if (officeType === 'Арбатская') {
                var listCurrent = listAr;
            }

            if (officeType === 'Баррикадная') {
                var listCurrent = listBar;
            }
            if (officeType === null) {
                var listCurrent = tableAll;
            }

            if (listCurrent === tableAll){
                tableAll.find('tbody'). html(' ')

                for (let order of data.result) {
                    let classOrder = ''
                    let classFinish = ''
                    let blockImpotent = ''

                    if (order.isImportant === true) {
                        blockImpotent = "<div class='ribbon-5'><span>*</span></div>"
                    }

                    if (order.isFinished === true) {
                        classFinish= "table-secondary"
                    }

                    let orderHtml = "<tr " +
                        "class='show-order " + classFinish + "'" +
                        "data-bs-toggle='offcanvas' data-bs-target='#offcanvasExample' aria-controls='offcanvasExample'" +
                        "data-orderId='" + order.id + "'" +
                        "data-officeType='" + officeType + "' " +
                        ">" +
                        "                        <td>" + order.date + "</td>" +
                        "                        <td>" + order.text + blockImpotent +"</td>" +
                        "                        <td>" + order.officeType + "</td>" +
                        "                        <td>" + order.comment + "</td>" +
                        "                    </tr>"
                    tableAll.find('tbody').append(orderHtml)
                }
            }else{
                for (let order of data.result) {
                    let classOrder = ''
                    let blockImpotent = ''

                    if (order.isImportant === true) {
                        blockImpotent = "<div class='ribbon-5'><span>*</span></div>"
                    }

                    let orderHtml = "" +
                        "<div class='show-order material-block m-2 p-2 " + classOrder + "' " +
                        "data-bs-toggle='offcanvas' data-bs-target='#offcanvasExample' aria-controls='offcanvasExample'" +
                        "data-officeType='" + officeType + "' " +
                        "data-orderId='" + order.id + "'>" +
                        order.text +
                        blockImpotent +
                        "</div>"
                    listCurrent.append(orderHtml)
                }
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

function clearFormOrder(officeType) {
    $('textarea[name=text]').val('')
    $('input[name=materialId]').val('0')
    $('input[name=isFinished]').prop('checked', false);
    $('input[name=isImportant]').prop('checked', false);

    $("select[name=officeType] option[value=" + officeType + "]").attr('selected', 'selected');
}

function saveOrder() {
    //if (validOrder() === false) {
    //return false;
    //}

    let formData = new FormData($('#data-order')[0])

    if ($('input[name=materialId]').val() === '0') {
        url = '/materials/create';
    } else {
        url = '/materials/update';
    }

    $.ajax({
        url: url,
        method: 'post',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        success: function (data) {
            location.reload()
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

function deleteOrder() {
    if (confirm('Вы уверены что хотите удалить задачу?') === true) {
        $.ajax({
            url: '/materials/remove',
            method: 'post',
            data: {
                'materialId': $('input[name=materialId]').val(),
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

function showOrder(orderId) {
    console.log(orderId)
    $.ajax({
        url: '/materials/get',
        method: 'get',
        data: {
            'id': orderId,
        },
        async: false,
        success: function (data) {
            $('textarea[name=text]').val(data.result.text)
            $('textarea[name=comment]').val(data.result.comment)
            $('input[name=materialId]').val(data.result.id)

            var euro_date = data.result.date;
            euro_date = euro_date.split('.');
            var us_date = euro_date.reverse().join('-');

            $('input[name=date]').val(us_date)

            $("select[name=officeType] option[value=" + data.result.officeType + "]").attr('selected', 'selected');

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
            if (data.result.isWork === true) {
                $('input[name=isWork]').prop('checked', true);
            } else {
                $('input[name=isWork]').prop('checked', false);
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

$(document).on('click', '.but-add-materials', function () {
    clearFormOrder($(this).data('office-type'));
});

$(document).on('click', '#button-save-order', function () {
    saveOrder();
});
$(document).on('click', '#button-save-delete', function () {
    deleteOrder();
});

$(document).on('click', '.show-order', function () {
    showOrder($(this).data('orderid'));
});
