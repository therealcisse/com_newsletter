<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class SubscriberViewSubscriber extends JView
{
    function display($tpl = null)
    {
        $form = &$this->get('FormData');
        $this->assignRef('form', $form);

        $item = &$this->get('itemData');
        $this->assignRef('item', $item);

        $this->assignRef('languages_all', $this->get('languagesAll'));
        $this->assignRef('categories_all', $this->get('categoriesAll'));

        parent::display($tpl);
    }
}