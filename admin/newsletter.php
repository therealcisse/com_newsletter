<?php

defined('_JEXEC') or die;

// Access check.
/*if (!JFactory::getUser()->authorise('core.manage', 'com_users')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}*/

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller = JController::getInstance('Newsletter');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();