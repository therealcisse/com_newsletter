/************************************************************************
 *************************************************************************
 @Name :           QapTcha - jQuery Plugin
 @Revison :        3.0
 @Date :         20/09/2011
 @Author:          ALPIXEL - (www.myjqueryplugins.com - www.alpixel.fr)
 @License :         Open Source - MIT License : http://www.opensource.org/licenses/mit-license.php

 **************************************************************************
 *************************************************************************/
jQuery.QapTcha = {
    build:function (options) {
        var defaults = {
            txtLock:'Locked : form can\'t be submited',
            txtUnlock:'Unlocked : form can be submited',
            disabledSubmit:true,
            autoRevert:false,
            url:'php/Qaptcha.jquery.php',
            data:{},
            form:'form'
        };

        if (this.length > 0)
            return jQuery(this).each(function (i) {
                /** Vars **/
                var
                    opts = jQuery.extend(defaults, options),
                    $this = jQuery(this),
                    form = jQuery(opts.form).has($this),
                    Clr = jQuery('<div>', {'class':'clr'}),
                    bgSlider = jQuery('<div>', {id:'bgSlider'}),
                    Slider = jQuery('<div>', {id:'Slider'}),
                    Icons = jQuery('<div>', {id:'Icons'}),
                    TxtStatus = jQuery('<div>', {id:'TxtStatus', 'class':'dropError', text:opts.txtLock}),
                    inputQapTcha = jQuery('<input>', {name:'iQapTcha', value:generatePass(), type:'hidden'});

                /** Disabled submit button **/
                if (opts.disabledSubmit) form.find('button[type="submit"]').attr('disabled', 'disabled');

                /** Construct DOM **/
                bgSlider.appendTo($this);
                Icons.insertAfter(bgSlider);
                Clr.insertAfter(Icons);
                TxtStatus.insertAfter(Clr);
                inputQapTcha.appendTo($this);
                Slider.appendTo(bgSlider);
                $this.show();

                Slider.draggable({
                    revert:function () {
                        if (opts.autoRevert) {
                            return parseInt(Slider.css("left")) <= 150;
                        }
                    },
                    containment:bgSlider,
                    axis:'x',
                    stop:function (event, ui) {
                        if (ui.position.left > 150) {
                            // set the SESSION iQaptcha in PHP file
                            jQuery.ajax({
                                url:opts.url,
                                data:opts.data,
                                dataType:'json',
                                success:function (data) {
                                    if (!data.error) {
                                        Slider.draggable('disable').css('cursor', 'default');
                                        inputQapTcha.val("");
                                        TxtStatus.text(opts.txtUnlock).addClass('dropSuccess').removeClass('dropError');
                                        Icons.css('background-position', '-16px 0');
                                        form.find('button[type="submit"]').removeAttr('disabled');
                                    }
                                }
                            });
                        }
                    }
                });

                function generatePass() {
                    var chars = 'azertyupqsdfghjkmwxcvbn23456789AZERTYUPQSDFGHJKMWXCVBN';
                    var pass = '';
                    for (i = 0; i < 32; i++) {
                        var wpos = Math.round(Math.random() * chars.length);
                        pass += chars.substring(wpos, wpos + 1);
                    }
                    return pass;
                }

            });
    }
};
jQuery.fn.QapTcha = jQuery.QapTcha.build;