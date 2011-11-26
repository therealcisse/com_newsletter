<?php

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller = JController::getInstance('Subscriber');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();