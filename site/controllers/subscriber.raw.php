<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Subscriber controller class.
 *
 */
class SubscriberControllerSubscriber extends JController
{
    private $_model = null;

    /**
     * @var        string    The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_NEWSLETTER_SUBSCRIBER';

    public function __construct($config = array())
    {
        $this->default_view = 'subscriber';
        parent::__construct($config);
    }

    public function getModel($name = 'Subscriber', $prefix = 'SubscriberModel', $config = array())
    {
        if (!isset($this->_model))
            $this->_model = &parent::getModel($name, $prefix, $config);
        return $this->_model;
    }

    /*public function getView($name = 'Subscriber', $type = 'raw', $prefix = 'SubscriberView', $config = array())
    {
        return parent::getView($name, $type, $prefix, $config);
    }*/


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

        $data = JRequest::getVar('subscriber', array(), 'post', 'array');
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

        JRequest::setVar('layout', 'saved');
        JRequest::setVar('view', 'subscriber');
        return $this->display();
    }

    /*public function retrieve()
    {
        $app = JFactory::getApplication();
        $app->setUserState('operation_type', 'GET');

        $id = JRequest::getInt('id');

        if ($id > 0) {
            $model = &$this->getModel();
            $model->setState($model->getName() . '.id', $id);
            return $this->display();
        }

        JRequest::setVar('layout', 'saved');
        return false;
    }*/
}