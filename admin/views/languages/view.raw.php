<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class NewsletterViewLanguages extends JView
{
    function display($tpl = null)
    {
        $languages = $this->get('languages');
        $this->assignRef('languages', &$languages);
        parent::display($tpl);
    }
}