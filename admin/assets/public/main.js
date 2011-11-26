;

var exports = this;

$(function () {

    $.each(["languages", "categories"], function (index, id) {

        $("#com_newsletter .viewport").delegate('#' + id + '_all', 'click', function () {
            if (this.checked)
                $('#' + id).find(':checkbox').selected();
            else
                $('#' + id).find(':checkbox:not(.default-checked)').selected(false);
        });

        $("#com_newsletter .viewport").delegate('#' + id + ' :checkbox:not(#' + id + '_all)', 'click', function () {
            if (!this.checked)
                $('#' + id + '_all').selected(false)
        });
    });

    $('.viewport [data-rel=popover]').popover({
        live:true,
        content:function () {
            var data = $(this).data('popoverdata');
            if (data) {
                var html = '';
                for (var it in data)
                    html += '<li>' + data[it]['title'] + '</li>';

                return [
                    '<p><ol>',
                    html,
                    '</ol></p>'
                ].join('');
            }
            return '<p></p>';
        },
        html:true,
        placement:'left'

    });

    $('.select-all input').click(function () {
        $('.subscriber-select').click()
    });

    exports.clearMsg = function(el) {
        $('.alert-message').alert('close').parent('#msg').hide().empty();
        el && el.closest('.clearfix').removeClass('error');
    };

    exports.printSuccessMsg = function(msg) {
        $('#msg').show().html(successMsg(msg)).find('.alert-message').alert();
        window.setTimeout(function () {
            exports.clearMsg();
        }, 4000);
    };

    exports.printErrorMsg = function(el, msg) {
        $('#msg').show().html(errorMsg(msg)).find('.alert-message').alert();
        el && window.setTimeout(function () {
            el.closest('.clearfix').addClass('error');
            el.focus();
        }, 200);
    };

    function successMsg(msg) {
        return [
            "<div class='alert-message block-message success'>",
            '<a class="close" href="#">×</a>',
            "<p><strong>" + msg + "</strong></p>",
            "</div>"
        ].join('');
    }

    function errorMsg(msg) {
        return [
            "<div class='alert-message error'>",
            "<p><strong>Error: </strong>" + msg + ".</p>",
            "</div>"
        ].join('')
    }

    exports.App = require("index");
    exports.app = new App({el:'.viewport'});
});
