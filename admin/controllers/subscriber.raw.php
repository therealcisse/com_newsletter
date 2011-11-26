<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Subscriber controller class.
 *
 */
class NewsletterControllerSubscriber extends JController
{
    private $_model = null;

    /**
     * @var        string    The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_NEWSLETTER_NEWSLETTER';

    public function __construct($config = array())
    {
        $this->default_view = 'subscriber';
        parent::__construct($config);

        $this->registerTask('publish', 'changePublish');
        $this->registerTask('unpublish', 'changePublish');

        $this->registerTask('activate', 'changeActivation');
        $this->registerTask('deactivate', 'changeActivation');

        $this->registerTask('delete', 'remove');
        $this->registerTask('destroy', 'remove');

        $this->registerTask('update', 'save');

        $this->registerTask('check_email', 'check_email');
    }

    public function getModel($name = 'Subscriber', $prefix = 'NewsletterModel', $config = array())
    {
        if (!isset($this->_model))
            $this->_model = &parent::getModel($name, $prefix, $config);
        return $this->_model;
    }

    public function check_email() {

        jimport('joomla.mail.helper');

        $id = JRequest::getInt('id', 0);
        $email = JRequest::getVar('email');

        if(!$email)
            JError::raiseError(500, 'Please provide email');

        if(empty($email) or !JMailHelper::isEmailAddress($email)) {
            echo json_encode(array('success' => false));
            return false;
        }

        $db = &JFactory::getDbo();

        // check for existing emails
        $query = 'SELECT id' . ' FROM #__subscribers ' . ' WHERE email = ' . $db->Quote($email) . ($id !== 0 ? (' AND id <> ' . $db->Quote((int)$id)) : '');
        $db->setQuery($query);
        $xid = intval($db->loadResult());

        JResponse::setHeader('Content-Type', 'application/json; charset=utf8');

        if ($xid) {
            echo json_encode(array('success' => false));
            return false;
        }

        echo json_encode(array('success' => true));
        return true;
    }

    /*
     *  Save or update a record
     *
     * */
    public function save()
    {
        // Check for request forgeries.
        //JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $app = JFactory::getApplication();
        $app->setUserState('operation_type', 'SAVE');

        $model = &$this->getModel();
        $table = $model->getTable();
        $key = $table->getKeyName();

        function getUserData() {
            $ret = array();

            $ret['first_name'] = JRequest::getVar('first_name');
            $ret['last_name'] = JRequest::getVar('last_name');
            $ret['email'] = JRequest::getVar('email');

            if(($var = JRequest::getVar('published', null, 'post')) !== null) //was the published attribute sent?
                $ret['published'] = $var === 'false' ? false : (BOOL)$var;//JRequest::getVar('published', false, 'post', 'boolean');

            return $ret;
        }

        $data = getUserData();
        $data['categories'] = JRequest::getVar('categories', array(), 'post', 'array');
        $data['languages'] = JRequest::getVar('languages', array(), 'post', 'array');

        $recordId = JRequest::getInt($key);

        /*if (!$this->checkEditId($context, $recordId))
          {
              // Somehow the person just went to the form and tried to save it. We don't allow that.
              $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
              $this->setMessage($this->getError(), 'error');
              $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

              return false;
          }*/

        // Populate the row id from the session.
        $data[$key] = $recordId;

        // Access check.
        /*if (!$this->allowSave($data, $key))
          {
              $this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
              $this->setMessage($this->getError(), 'error');
              $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

              return false;
          }*/

        // Attempt to save the data.
        $model->save($data);

        return $this->display();
    }

    public function remove()
    {
        // Check for request forgeries.
        //JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        $app->setUserState('operation_type', 'REMOVE');

        // Get items to remove from the request.
        $ids = JRequest::getVar('id', array(), '', 'array');

        if (!is_array($ids) || count($ids) < 1) {
            JError::raiseError(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
        }
        else
        {
            // Get the model.
            $model = &$this->getModel();

            // Make sure the item ids are integers
            jimport('joomla.utilities.arrayhelper');
            JArrayHelper::toInteger($ids);

            // Remove the items.
            $model->delete($ids);
        }

        //TODO: if this is from the link in the email, then set layout

        return $this->display();
    }

    public function changePublish()
    {
        // Check for request forgeries.
        //JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        $app->setUserState('operation_type', 'PUBLISH');

        // Initialise variables.
        $ids = JRequest::getVar('id', array(), '', 'array');
        $values = array('publish' => 1, 'unpublish' => 0);
        $task = $this->getTask();
        $value = JArrayHelper::getValue($values, $task, 0, 'int');

        if (empty($ids)) {
            JError::raiseError(500, JText::_('COM_USERS_USERS_NO_ITEM_SELECTED'));
        } else {
            // Get the model.
            $model = &$this->getModel();
            $model->publish($ids, $value);
        }

        return $this->display();
    }

    public function changeActivation()
    {
        // Check for request forgeries.
        //JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();
        $app->setUserState('operation_type', 'ACTIVATE');

        // Initialise variables.
        $ids = JRequest::getVar('id', array(), '', 'array');
        $values = array('activate' => true, 'deactivate' => false);
        $task = $this->getTask();
        $todo = JArrayHelper::getValue($values, $task, true, 'boolean');

        if (empty($ids)) {
            JError::raiseError(500, JText::_('COM_USERS_USERS_NO_ITEM_SELECTED'));
        } else {
            // Get the model.
            $model = &$this->getModel();
            $model->activate($ids, $todo);
        }

        //TODO: if this is from the link in the email, then set layout

        return $this->display();
    }

    public function retrieve()
    {
        $app = JFactory::getApplication();
        $app->setUserState('operation_type', 'GET');

        $id = JRequest::getInt('id');

        if ($id > 0) {
            $model = &$this->getModel();
            $model->setState($model->getName() . '.id', $id);
            return $this->display();
        }

        return false;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*protected function allowAdd($data = array())
     {
         $user = JFactory::getUser();
         return ($user->authorise('core.create', $this->option) || count($user->getAuthorisedCategories($this->option, 'core.create')));
         return true;
     }

     protected function allowEdit($data = array(), $key = 'id')
     {
         return JFactory::getUser()->authorise('core.edit', $this->option);
         return true;
     }

     protected function allowSave($data, $key = 'id')
     {
         // Initialise variables.
         $recordId = isset($data[$key]) ? $data[$key] : '0';

         if ($recordId)
         {
             return $this->allowEdit($data, $key);
         }
         else
         {
             return $this->allowAdd($data);
         }
     }*/
}