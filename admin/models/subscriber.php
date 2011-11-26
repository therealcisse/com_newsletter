<?php

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.user.helper');

/**
 * Subscriber model.
 *
 */
class NewsletterModelSubscriber extends JModel
{

    public function getTable($type = 'Subscriber', $prefix = 'JTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function populateState()
    {
        if(($id = JRequest::getInt('id', 0)) !== 0)
            $this->setState($this->getName() . '.id', $id);
    }

    public function getOperationType()
    {
        return JFactory::getApplication()->getUserState('operation_type');
    }

    public function getItem()
    {
        // Initialise variables.
        $pk = (int)$this->getState($this->getName() . '.id');

        if ($pk > 0) {

            $table = $this->getTable();

            // Attempt to load the row.
            $return = $table->load($pk);

            // Check for a table object error.
            if ($return === false /*&& $table->getError()*/) {
                //$this->setError($table->getError());
                return false;
            }

            return $table->normalized();
        }

        return false;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     */
    function publish(&$pks, $value = 1)
    {
        // Initialise variables.
        //$dispatcher = JDispatcher::getInstance();
        $user = JFactory::getUser();
        $table = $this->getTable();
        $pks = (array)$pks;

        // Include the content plugins for the change of state event.
        //JPluginHelper::importPlugin('content');

        // Access checks.
        foreach ($pks as $i => $pk)
        {
            $table->reset();

            if ($table->load($pk)) {
                if (!$this->canEditState($table)) {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
                    return false;
                }
            }
        }

        // Attempt to change the state of the records.
        if (!$table->publish($pks, $value, $user->get('id'))) {
            $this->setError($table->getError());
            return false;
        }

        //TODO: add event dispatching
        /*$context = $this->option . '.' . $this->name;

          // Trigger the onContentChangeState event.
          $result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

          if (in_array(false, $result, true))
          {
              $this->setError($table->getError());
              return false;
          }*/

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     *
     */
    public function save($data)
    {
        // Initialise variables;
        //$dispatcher = JDispatcher::getInstance();
        $table = $this->getTable();
        $key = $table->getKeyName();
        $pk = (!empty($data[$key])) ? $data[$key] : (int)$this->getState($this->getName() . '.id');

        // Include the content plugins for the on save events.
        //JPluginHelper::importPlugin('content');

        // Allow an exception to be thrown.
        try
        {
            // Load the row if saving an existing record.
            if ($pk > 0) {
                $table->load($pk);
            } else {
                $data['activation'] = JUserHelper::genRandomPassword(32);

                if($user = JFactory::getUser())
                    $data['created_user_id'] = $user->id;
            }

            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            /*$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));
               if (in_array(false, $result, true))
               {
                   $this->setError($table->getError());
                   return false;
               }*/

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            //$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());
            return false;
        }

        if (isset($table->$key)) {
            $this->setState($this->getName() . '.id', $table->$key);
        }
        if (!($pk > 0)) { // new account

            // http://muazcisse.org

            $url = JURI::base() . JRoute::_("/index.php?option=com_newsletter&task=subscriber.activate&id=" . $table->get('id'));
            $fn = ucfirst($table->get('first_name'));
            $ln = ucfirst($table->get('last_name'));
            $message = <<<EMAIL

<div>

    <p>Bonjour $fn $ln  </p>

    <p>Vous venez de vous suscrire sur notre site.</p>

    <p>Cliquez à présent sur ce lien pour <a
            href="$url"
            target="_blank">valider votre adresse email et confirmer la création de votre suscription</a>. </p>

    <p>Ce message a été généré automatiquement par notre site. Veuillez ne pas y répondre. Merci</p>
</div>

EMAIL;


            if (!$this->sendActivationEmail(
                'noreply@muazcisse.org',
                $table->get('email'),
                'Muaz Cisse - Activation request',
                $message
            )
            ) {
                //email sending failed
            }
        }

        return true;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    private function sendActivationEmail($mailform, $subscriber, $subject, $message)
    {
        return JFactory::getMailer()->sendMail($mailform, $mailform, $subscriber, $subject, $message, true);
    }

    /**
     * Method to delete one or more records.
     *
     * @param   array  &$pks  An array of record primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     */
    public function delete(&$pks)
    {
        // Initialise variables.
        //$dispatcher = JDispatcher::getInstance();
        //$user = JFactory::getUser();
        $pks = (array)$pks;
        $table = $this->getTable();

        // Include the content plugins for the on delete events.
        //JPluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {

            if ($table->load($pk)) {

                if ($this->canDelete($table)) {

                    /*$context = $this->option . '.' . $this->name;

                         // Trigger the onContentBeforeDelete event.
                         $result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
                         if (in_array(false, $result, true))
                         {
                             $this->setError($table->getError());
                             return false;
                         }*/

                    if (!$table->delete($pk)) {
                        $this->setError($table->getError());
                        return false;
                    }

                    // Trigger the onContentAfterDelete event.
                    //$dispatcher->trigger($this->event_after_delete, array($context, $table));

                }
                else
                {

                    // Prune items that you can't change.
                    unset($pks[$i]);
                    $error = $this->getError();
                    if ($error) {
                        JError::raiseWarning(500, $error);
                        return false;
                    }
                    else
                    {
                        JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
                        return false;
                    }
                }

            }
            else
            {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    function activate(&$pks, $activate = true)
    {
        // Initialise variables.
        //$dispatcher = JDispatcher::getInstance();
        //$user = JFactory::getUser();
        $table = $this->getTable();
        $pks = (array)$pks;

        //JPluginHelper::importPlugin('user');

        // Access checks.
        foreach ($pks as $i => $pk)
        {
            if ($table->load($pk)) {
                /*$old = $table->getProperties();
                $allow = $user->authorise('core.edit.state', 'com_users');*/

                if (($activate && empty($table->activation))
                        or (!$activate && count($table->activation) == 32)) {
                    // Ignore activated accounts.
                    unset($pks[$i]);
                }
                else /*if ($allow)*/ {

                    if ($activate) {

                        // Publish registered (i. e self-created) users when activated
                        if (empty($table->created_user_id)) {
                            $table->published = 1;
                        }

                        $table->activation = '';
                    } else {
                        $table->published = 0;
                        $table->activation = JUserHelper::genRandomPassword(32);
                    }

                    // Allow an exception to be thrown.
                    try
                    {
                        if (!$table->check()) {
                            $this->setError($table->getError());
                            return false;
                        }

                        // Trigger the onUserBeforeSave event.
                        /*$result = $dispatcher->trigger('onUserBeforeSave', array($old, false, $table->getProperties()));
                        if (in_array(false, $result, true)) {
                            // Plugin will have to raise it's own error or throw an exception.
                            return false;
                        }*/

                        // Store the table.
                        if (!$table->store()) {
                            $this->setError($table->getError());
                            return false;
                        }

                        // Fire the onAftereStoreUser event
                        //$dispatcher->trigger('onUserAfterSave', array($table->getProperties(), false, true, null));
                    }
                    catch (Exception $e)
                    {
                        $this->setError($e->getMessage());

                        return false;
                    }
                }
                /*else {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
                }*/
            }
        }

        return true;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
     *
     */
    protected function canDelete($record)
    {
        /*$user = JFactory::getUser();
          return $user->authorise('core.delete', $this->option);*/
        return true;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
     *
     */
    protected function canEditState($record)
    {
        /*$user = JFactory::getUser();
          return $user->authorise('core.edit.state', $this->option);*/
        return true;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Method to activate a user
     *
     * @param   string  $activation  Activation string
     *
     * @return  boolean  True on success
     *
     */
    public static function activateSubscription($activation) //TODO: remove from model
    {
        // Initialize some variables.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Let's get the id of the user we want to activate
        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('activation') . ' = ' . $db->quote($activation));
        $query->where($db->quoteName('published') . ' = 0');
        $db->setQuery($query);
        $id = intval($db->loadResult());

        // Is it a valid user to activate?
        if ($id) {
            $user = JUser::getInstance((int)$id);

            // Publish registered users when activated
            if ($user->get('created_user_id', false) === false)
                $user->set('publish', '1');

            $user->set('activation', '');

            // Time to take care of business.... store the user.
            if (!$user->save()) {
                JError::raiseWarning("SOME_ERROR_CODE", $user->getError());
                return false;
            }
        }
        else
        {
            JError::raiseWarning("SOME_ERROR_CODE", JText::_('JLIB_USER_ERROR_UNABLE_TO_FIND_USER'));
            return false;
        }

        return true;
    }
}