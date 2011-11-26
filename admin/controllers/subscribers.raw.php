<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Subscriber controller class.
 *
 */
class NewsletterControllerSubscribers extends JController
{
    public function __construct($config = array())
    {
        $this->default_view = 'subscribers'; //Just to make it clear
        parent::__construct($config);
    }


    public function getLanguages()
    {
        JRequest::setVar('view', 'languages');
        return $this->display();
    }

    public function getCategories()
    {
        JRequest::setVar('view', 'categories');
        return $this->display();
    }
}