$(document).ready(function () {
    //$('#block-spinner').hide();
    function uploadFilesRequest(){
        let formData = new FormData($('#filesUpload')[0]);
        $('#block-spinner').show();
        $.ajax({
            url: '/exchange/catalog',
            method: 'post',
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function (data) {
                if (typeof data.data !== 'undefined'){
                    $('#block-message').html('');
                    data.data.errors.forEach(function(entry) {
                        $('#block-message').append('<div class="text-bg-danger text-white p-1">' + entry + '</div>')
                    });
                    data.data.warning.forEach(function(entry) {
                       $('#block-message').append('<div class="text-bg-warning text-white p-1">' + entry + '</div>')
                    });
                    data.data.success.forEach(function(entry) {
                        $('#block-message').append('<div class="text-bg-success text-white p-1">' + entry + '</div>')
                    });
                }
                $('#block-spinner').hide();
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

    $(document).on('click', '#button-upload', function () { uploadFilesRequest() });
});
