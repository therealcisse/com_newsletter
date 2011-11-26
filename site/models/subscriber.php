<?php

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.user.helper');

/**
 * Subscriber model.
 *
 */
class SubscriberModelSubscriber extends JModel
{

    public function getTable($type = 'Subscriber', $prefix = 'JTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected $_formData;
    protected $_itemData;
    protected $_item;

    public function getItemData()
    {
        if (!isset($this->_itemData)) {

            $it = new stdClass;
            if ($item = $this->getItem()) {
                $it->first_name = $item->first_name;
                $it->last_name = $item->last_name;
                $it->email = $item->email;
                $it->categories = $this->_grab_ids($item->categories);
                $it->languages = $this->_grab_ids($item->languages);

            }
            else
            {
                $it->first_name = '';
                $it->last_name = '';
                $it->email = '';
                $it->categories = array();
                $it->languages = array();
            }

            $this->_itemData = &$it;
        }

        return $this->_itemData;
    }

    public function getFormData()
    {
        if (!isset($this->_formData)) {


            $this->_formData = array(
                'return' => $this->getReturn(),
                'categories' => $this->get_categories_by_level_checkboxes(1)
            );

            list($languages, $lang_id) = $this->get_languages_options();
            if ($languages !== false) {
                $this->_formData['languages'] = $languages;
                $this->_formData['show_languages'] = true;
            } else {
                $this->_formData['show_languages'] = false;
                $this->_formData['lang_id'] = $lang_id;
            }
        }

        return $this->_formData;
    }

    public function getCategoriesAll()
    {
        return (boolean)$this->categories_all;
    }

    private $categories_all = false;
    private $languages_all = false;

    public function getLanguagesAll()
    {
        return (boolean)$this->languages_all;
    }

    protected function populateState()
    {

        if ($id = JRequest::getInt('id'))
            $this->setState($this->getName() . '.id', $id);

        // check for return URL from the request first
        if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
            $return = base64_decode($return);
            if (!JURI::isInternal($return)) {
                $return = '';
            }
        }
        // Set the return URL if empty.
        if (empty($return)) {
            $return = 'index.php';
        }

        $this->setState('return', $return);
    }

    public function getItem()
    {
        if (isset($this->_item))
            return $this->_item;

        // Initialise variables.
        $pk = $this->getId();

        if ($pk > 0) {

            $table = &$this->getTable();

            // Attempt to load the row.
            $return = $table->load($pk);

            // Check for a table object error.
            if ($return === false /*&& $table->getError()*/) {
                //$this->setError($table->getError());
                return false;
            }

            $this->_item = $table->normalized();
            return $this->_item;
        }

        return false;
    }

    //TODO: add this to the module
    /*public static function getReturnURI()
    {
        $uri = JFactory::getURI();
        //$return = 'index.php' . $uri->toString(array('query'));
        $return = $uri->toString(array('path', 'query', 'fragment'));
        if (strpos($return, 'index.php?option=com_newsletter') == -1) {
            return ($return);
        } else {
            return ('index.php');
        }
    }*/

    private function getReturn()
    {
        return $this->getState('return');
    }

    protected function get_categories_by_level_checkboxes($level = 0)
    {
        $categories = $this->getItemData()->categories;

        $html = '';
        $i = 1;
        $cats = $this->get_categories_by_level($level);
        $len = count($cats);
        $categories_all = true;
        foreach ($cats as $category) {
            $in_array = in_array($category['id'], $categories);
            $categories_all = $categories_all && $in_array;
            $html .= $this->_buildinput('categories[]', $category['id'], $category['title'], $in_array) . PHP_EOL;
            if (($i != $len) and ((++$i) % 4 === 0))
                $html .= '<br/><br/>';
        }

        $this->categories_all = $categories_all;

        $html .= '<br/>';
        return $html;
    }

    protected function get_categories_by_level($level = 0)
    {
        $db = &$this->getDbo();
        $query = $db->getQuery(true);
        $query->select(array($db->quoteName('id'), $db->quoteName('title'), $db->quoteName('parent_id')))
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('extension') . ' = ' . $db->quote('com_content'))
            ->where($db->quoteName('published') . ' = ' . $db->quote(1)); //make sure the category is published

        if ($level !== 0) {
            $query->where($db->quoteName('level') . ' = ' . $db->quote($level));
            $query->order($db->quote('level'));
        }

        $db->setQuery($query);

        $ret = array();
        foreach ($db->loadObjectList() as $category)
            $ret[] = array(
                "id" => $category->id,
                "title" => $category->title
            );

        return $ret;
    }

    public function getId()
    {
        return (int)$this->getState($this->getName() . '.id');
    }

    protected function get_languages_options()
    {
        if (($len = count($langs = JLanguageHelper::getLanguages())) == 1) // Only one default language
            return array(
                false,
                $langs[0]->lang_id
            );

        $languages = $this->getItemData()->languages;
        $html = '';
        $i = 1;
        $languages_all = true;
        foreach ($langs as $lang) {
            $in_array = in_array($lang->lang_id, $languages);
            $languages_all = $languages_all && $in_array;
            $html .= $this->_buildinput('languages[]', $lang->lang_id, isset($lang->title_native) ? $lang->title_native
                : $lang->title, $in_array or (!$this->getId() and $lang->lang_code == JFactory::getLanguage()->getTag())) . PHP_EOL;
            if (($i != $len) and ((++$i) % 4 === 0))
                $html .= '<br/><br/>';
        }

        $this->languages_all = $languages_all;

        $html .= '<br/>';
        return array($html);
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

    private function _buildinput($name, $val, $txt, $isSelected = 0)
    {
        return JText::sprintf("<ins><input type='checkbox' name=\"%s\" value=\"%d\" %s /> %s</ins>", $name, $val, $isSelected ? 'checked="checked"' : '', $txt);
    }

    private function _grab_ids($array)
    {
        $ret = array();
        foreach ($array as $_ => $val)
            $ret[] = (int)$val['id'];
        return $ret;
    }
}
