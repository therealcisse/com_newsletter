<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class NewsletterViewSubscriber extends JView
{
    function display($tpl = null)
    {
        if (count($errors = $this->get('errors'))) {
            JResponse::setHeader('Content-Type', 'application/json', true);
            if (!($error = $errors[0]) or !$error->key) {
                $error = new stdClass;
                $error->key = null;
                $error->msg = 'unknown error';
            }

            $ret = new stdClass;

            $ret->error = $error;
            $ret->success = false;

            echo json_encode($ret);

            return false;
        }

        $operation_type = $this->get('operationType');
        switch ($operation_type) {
            case 'GET':
            case 'SAVE':
            case 'ACTIVATE':
                $this->item = $this->get('item');

                if ($this->item === false) { //important for gets
                    JError::raiseError(404, 'NotFound');
                    return false;
                }

                break;

            case 'REMOVE':
            case 'PUBLISH':
                header('SUCCESS', true, 204);
                return true; // Not content
        }

        return parent::display($tpl);
    }
}