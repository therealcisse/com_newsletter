<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class NewsletterViewSubscribers extends JView
{
    function display($tpl = null)
    {
        $subscribers = $this->get('items');
        $this->assignRef('subscribers', &$subscribers);
        parent::display($tpl);
    }
}