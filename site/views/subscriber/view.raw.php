<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class SubscriberViewSubscriber extends JView
{
    function display($tpl = null)
    {

        if (count($errors = $this->get('errors'))) {
            JResponse::setHeader('Content-Type', 'application/json', true);
            if (!($error = $errors[0]) or !$error->key)
            {
                $error = new stdClass;
                $error->field = null;
                $error->msg = 'unknown error';
            }

            $error->success = false;
            echo json_encode($error);
            return false;
        }

        $item = &$this->get('item');
        $this->assignRef('item', $item);
        parent::display($tpl);
    }
}