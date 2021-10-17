function confirmAction(viewName, action, title, message, cancel, confirm) {
    bootbox.confirm({
        title: title,
        message: message,
        closeButton: false,
        buttons: {
            cancel: {
                label: '<i class="fas fa-times"></i> ' + cancel
            },
            confirm: {
                label: '<i class="fas fa-check"></i> ' + confirm,
                className: "btn-warning"
            }
        },
        callback: function (result) {
            if (result) {
                $("#form" + viewName + " :input[name=\"action\"]").val(action);
                $("#form" + viewName).submit();
            }
        }
    });
}
function hideNeoSidebar() {
    $("#sidebar").fadeOut('fast');
    $("#sidebar-overlay").fadeOut('slow');
}
function showNeoSidebar() {
    $("#sidebar-overlay").fadeIn('slow');
    $("#sidebar").fadeIn('fast');
}
$(document).ready(function () {
    $(".datepicker").datepicker({
        dateFormat: "dd-mm-yy",
        firstDay: 1,
        beforeShow: function () {
            setTimeout(function () {
                $(".ui-datepicker").css("z-index", 99999999999999);
            }, 0);
        }
    });
    $(".clickableRow").mousedown(function (event) {
        if (event.which === 1 || event.which === 2) {
            var href = $(this).attr("data-href");
            var target = $(this).attr("data-target");
            if (typeof href !== typeof undefined && href !== false) {
                if (typeof target !== typeof undefined && target === "_blank") {
                    window.open($(this).attr("data-href"));
                } else if (event.which === 2) {
                    window.open($(this).attr("data-href"));
                } else {
                    parent.document.location = $(this).attr("data-href");
                }
            }
        }
    });
    $(".cancelClickable").mousedown(function (event) {
        event.preventDefault();
        event.stopPropagation();
    });
});