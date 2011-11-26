<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class NewsletterViewCategories extends JView
{
    function display($tpl = null)
    {
        $categories = $this->get('categories');
        $this->assignRef('categories', &$categories);
        parent::display($tpl);
    }
}