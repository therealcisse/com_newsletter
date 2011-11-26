jQuery(function ($) {

    // Very cool stuff, because of this we can parse the body even if the response is an error, nice for receiving validation errors in getJSON
    $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
        if (options.parseError) {
            $.Deferred(
                function (defer) {
                    jqXHR.done(defer.resolve)
                        .fail(function (jqXHR, statusText, errorMsg) {
                            if (jqXHR.statusCode() == 500) //TODO: test case where statusCode==500
                                defer.rejectWith(this, [ jqXHR, statusText, errorMsg ]);
                            else
                                defer.rejectWith(this, [ $.parseJSON(jqXHR.responseText), "success", jqXHR ]);
                        });
                }).promise(jqXHR);
            jqXHR.success = jqXHR.done;
            jqXHR.error = jqXHR.fail;
        }
    });

    $.each(["languages", "categories"], function (index, id) {
        $('#' + id + '_all').click(function () {
            if (this.checked)
                $('#' + id).find(':checkbox').selected();
            else
                $('#' + id).find(':checkbox:not(.default-checked)').selected(false);
        });

        $('#' + id + ' :checkbox:not(#' + id + '_all)').click(function () {
            if (!this.checked)
                $('#' + id + '_all').selected(false)
        });

        window['is' + (id[0].toUpperCase() + id.substr(1)) + 'Valid'] = function (msg) {
            var isValid = false;
            $('#' + id).find(':checkbox:not(#' + id + '_all)').each(function () {
                isValid = isValid || this.checked;
            });

            if(!isValid)
                isValid = $('#languages_once').length > 0; //maybe we have only the default language

            if (!isValid)
                printErrorMsg(null, msg);
            else
                clearErrorMsg(null);

            return isValid;
        }
    });

    function clearErrorMsg(it) {
        $('#msg .alert-message').alert('close').parent().hide().empty();
        it && it.closest('.clearfix').removeClass('error');
    }

    function printErrorMsg(it, msg) {
        $(errorMsg(msg)).appendTo($('#msg').show()).alert();

        it && window.setTimeout(function () {
            it.closest('.clearfix').addClass('error');
            it.focus();
        }, 200);
    }

    function setUpValidation() {

        function validateEmpty(id, msg) { //todo: add to printErrorMsg
            var it = $(id),
                val = it.val();

            val = val != null ? val.trim() : '';

            if (val == '') {
                printErrorMsg(it, msg);
                return false;
            }

            clearErrorMsg(it);
            return true;
        }

        window.isFirstNameValid = function isFirstNameValid() {
            return validateEmpty('#first_name', "First name is necessary");
        };

        window.isLastNameValid = function isLastNameValid() {
            return validateEmpty('#last_name', "Last name is necessary");
        };

        window.isEmailValid = function isEmailValid() {
            return validateEmpty('#email', "Email is necessary");
        };
    }

    $('#subscribeform').ajaxForm({
        type: 'POST',
        success:function (data) {
            $('#subscribeform button[type=submit]').button('reset');
            if (data.success) {
                $('#com_newsletter').addClass('done');
                $(successMsg()).appendTo($('#success')).alert();
            }
            else if (data.key)
                printErrorMsg($('#' + data.key), data.msg);
            else
                printErrorMsg(null, "Unknown error, please try again");
        },
        parseError:true,
        error:function (error) {
            if (error.key)
                printErrorMsg($('#' + error.key), error.msg);
            else
                printErrorMsg(null, "Unknown error, please try again");
        },
        dataType:'json',
        beforeSubmit:function () {
            clearErrorMsg(null);
            var canSubmit = window.isFirstNameValid()
                && window.isLastNameValid()
                && window.isEmailValid()
                && window.isCategoriesValid('please select at least one category to subscribe')
                && window.isLanguagesValid('please select at least one language to subscribe');

            if (canSubmit)
                $('#subscribeform button[type=submit]').button('loading');

            return canSubmit;
        }
    });

    /*$('#msg').delegate('a.close', 'click', function(){
     clearErrorMsg(null);
     $('.clearfix').removeClass('error');
     return false;
     });*/

    setUpValidation();
});

function successMsg() {
    return [
        "<div class='alert-message block-message success'>",
        "<p><strong>Congratulations!</strong> Your subscription was successfully created.</p>",
        "<p>However, before you can receive our newsletters, you have to activate your subscription.</p>",
        "<p>An activation email has been sent to your email address, please login to activate your subscription.</p>",
        "<p><strong>Thank you for subscribing.</strong></p>",
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
