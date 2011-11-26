<?php

defined('_JEXEC') or die;

$lang = explode('-', JFactory::getLanguage()->getTag());

?>


<div id="com_newsletter">
    <div id="success">
    </div>
    <div id="top">
        <div class="title"><h1><?php echo JText::_(isset($this->item->id) ? 'Edit subscription' : 'Subscribe'); ?></h1>
        </div>
        <div id="msg"></div>
    </div>
    <div id="form">
        <form action="<?php echo JURI::base(true) . '/index.php' . ($lang[0] ? ('/' . $lang[0]) : ''); ?>" method="post"
              id='subscribeform'>
            <?php echo JHtml::_('form.token'); ?>
            <?php if (isset($this->item->id)) : ?>
            <input type="hidden" name="id" value="<?php  echo $this->item->id; ?>"/>
            <?php endif ?>
            <input type="hidden" name="option" value="com_newsletter"/>
            <input type="hidden" name="task" value="subscriber.save"/>
            <input type="hidden" name="format" value="raw"/>
            <input type="hidden" name="return" value="<?php echo base64_encode($this->form['return']); ?>"/>
            <fieldset>
                <legend><h6>Details</h6></legend>
                <div class="clearfix">
                    <label for="first_name"><?php echo JText::_('First name'); ?> : </label>

                    <div class="input">
                        <input autofocus class="xlarge" id="first_name" value="<? echo $this->item->first_name; ?>"
                               name="subscriber[first_name]" size="30" type="text">
                    </div>
                </div>
                <div class="clearfix">
                    <label for="last_name"><?php echo JText::_('Last name'); ?> : </label>

                    <div class="input">
                        <input class="xlarge" id="last_name" value="<? echo $this->item->last_name; ?>"
                               name="subscriber[last_name]" size="30" type="text">
                    </div>
                </div>
                <div class="clearfix">
                    <label for="email"><?php echo JText::_('Email'); ?> : </label>

                    <div class="input">
                        <div class="input-prepend">
                            <span class="add-on">@</span>
                            <input class="xlarge" id="email" value="<? echo $this->item->email; ?>"
                                   name="subscriber[email]" style="width: 243px;"
                                   size="30" type="text">
                        </div>
                    </div>
                </div>
            </fieldset>
            <?php if ($this->form['show_languages']) /* TODO: Maybe thsi is a bug */ { ?>
                <fieldset>
                    <legend><h6><?php echo JText::_('Languages'); ?></h6></legend>
                    <div id="languages">
                        <ins><input id="languages_all" type="checkbox"
                                    name="languages_all" <?php if ($this->languages_all) echo 'checked="checked"';  ?>
                                    value="all"> <em><?php echo JText::_('Select all'); ?></em></ins>
                        <?php echo $this->form['languages']; ?>
                    </div>
                </fieldset>
            <?php } else { ?>
                <input id="languages_once" type='hidden' name="languages[]" value="<?php  echo $this->form['lang_id']; ?>"  />
            <?php }  ?>
            <fieldset>
                <legend><h6><?php echo JText::_('Categories'); ?></h6></legend>
                <div id="categories">
                    <ins><input id="categories_all" type="checkbox"
                                name="categories_all" <?php if ($this->categories_all) echo 'checked="checked"';  ?>
                                value="all"> <em><?php echo JText::_('Select all'); ?></em></ins>
                    <?php echo $this->form['categories']; ?>
                </div>
            </fieldset>
            <div id="submit" class="actions clearfix">
                <div class="QapTcha" style="display: inline-block;"></div>
                <script type="text/javascript">
                    jQuery(function qaptcha() {
                        jQuery('.QapTcha').QapTcha({
                            url:baseurl + (lang ? ('/' + lang) : ''),
                            data:{
                                option:'com_newsletter',
                                task:'qaptcha',
                                format:'raw'
                            },
                            form:jQuery('#subscribeform')
                        });
                    });
                </script>
                <button type="submit" class="btn primary pull-right" data-loading-text="Creating subscription..." name="subscriber_form_submit"
                        style="margin-top: 18px;"><?php echo JText::_('Subscribe'); ?></button>
                <!-- &nbsp;
                <input type="reset" class="btn" value="<?php /*echo JText::_('Cancel'); */?>"/>-->
            </div>
        </form>
    </div>
</div>