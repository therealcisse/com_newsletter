<?php

defined('_JEXEC') or die;

/**
 * Subscribers table
 *
 */
class JTableSubscriber extends JTable
{

    /**
     * Associative array of user names => category ids
     *
     * @var    array
     */
    var $categories;

    /**
     * Associative array of user names => language ids
     *
     * @var    array
     */
    var $languages;

    /**
     * Constructor
     *
     * @param   database  &$db  A database connector object.
     *
     */
    function __construct(&$db)
    {
        parent::__construct('#__subscribers', 'id', $db);

        // Initialise.
        $this->id = 0;
    }

    public function normalized()
    {
        $properties = $this->getProperties(1);
        $item = JArrayHelper::toObject($properties, 'JObject');

        if (!isset($item->first_name)) $item->first_name = '';
        if (!isset($item->last_name)) $item->last_name = '';
        if (!isset($item->email)) $item->email = '';

        if ($item->created_user_id) {
            $createdBy = JFactory::getUser($item->created_user_id);
            unset($item->created_user_id);

            if ($createdBy and $createdBy->name)
                $item->created_username = $createdBy->name;
        }

        $item->published = $item->published === '0' ? false : true;
        $item->registerDate = gmDate("Y-m-d\TH:i:s\Z", strtotime($item->registerDate)); //date('c', strtotime($item->registerDate));
        $item->categories = array_values($this->categories ? $this->categories : array());
        $item->languages = array_values($this->languages ? $this->languages : array());

        return $item;
    }

    /**
     * Method to load a user, user groups, and any other necessary data
     * from the database so that it can be bound to the user object.
     *
     * @param   integer  $subscriberId  An optional user id.
     * @param   boolean  $reset   False if row not found or on error
     * (internal error state set in that case).
     *
     * @return  boolean  True on success, false on failure.
     *
     */
    function load($subscriberId = null, $reset = true)
    {
        // Get the id to load.
        if ($subscriberId !== null) {
            $this->id = $subscriberId;
        }
        else
        {
            $subscriberId = $this->id;
        }

        // Check for a valid id to load.
        if ($subscriberId === null) {
            return false;
        }

        // Reset the table.
        if ($reset)
            $this->reset();

        // Load the user data.
        $this->_db->setQuery('SELECT *' . ' FROM #__subscribers' . ' WHERE id = ' . $this->_db->Quote((int)$subscriberId));
        $data = (array)$this->_db->loadAssoc();

        // Check for an error message.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!count($data)) {
            return false;
        }
        // Bind the data to the table.
        $return = $this->bind($data);

        if ($return !== false) {
            // Load the user categories.
            $this->_db->setQuery(
                'SELECT g.id AS id, g.title AS title, g.alias AS alias' . ' FROM #__categories AS g' . ' JOIN #__subscriber_category_map AS m ON m.category_id = g.id'
                    . ' WHERE m.subscriber_id = ' . (int)$subscriberId
            );
            // Add the groups to the user data.
            $this->categories = $this->_db->loadAssocList('title');

            // Check for an error message.
            if ($this->_db->getErrorNum()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }

            /////////////////////////////////////////////////////////////////////////////////////////////////////////

            // Load the user languages. //TODO: implement title_native
            $this->_db->setQuery(
                'SELECT g.lang_id id, g.title AS title, g.title_native AS title_native, g.sef AS sef, g.lang_code AS code' . ' FROM #__languages AS g' . ' JOIN #__subscriber_language_map AS m ON m.language_id = g.lang_id'
                    . ' WHERE m.subscriber_id = ' . (int)$subscriberId
            );
            // Add the langaues to the user data.
            $this->languages = $this->_db->loadAssocList('title');

            // Check for an error message.
            if ($this->_db->getErrorNum()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }

        return $return;
    }

    /**
     * Method to bind the user, user groups, and any other necessary data.
     *
     * @param   array  $array   The data to bind.
     * @param   mixed  $ignore  An array or space separated list of fields to ignore.
     *
     * @return  boolean  True on success, false on failure.
     *
     */
    function bind($array, $ignore = '')
    {

        // Attempt to bind the data.
        $return = parent::bind($array, $ignore);


        if ($return) {
            // Load the real category data based on the bound ids.
            if (!empty($this->categories)) { // Set the category ids.
                JArrayHelper::toInteger($this->categories);

                // Get the titles for the user categories.
                $this->_db->setQuery(
                    'SELECT ' . $this->_db->quoteName('id') . ', ' . $this->_db->quoteName('title') . ', ' . $this->_db->quoteName('alias') . ' FROM '
                        . $this->_db->quoteName('#__categories') . ' WHERE ' . $this->_db->quoteName('id') . ' = '
                        . implode(' OR ' . $this->_db->quoteName('id') . ' = ', $this->categories)
                );
                // Set the titles for the user categories.
                $this->categories = $this->_db->loadAssocList('title');

                // Check for a database error.
                if ($this->_db->getErrorNum()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }
            }

            //////////////////////////////////////////////////////////////////////////////////

            // Load the real language data based on the bound ids.
            if (!empty($this->languages)) { // Set the language ids.
                JArrayHelper::toInteger($this->languages);

                // Get the titles for the user langauges.
                $this->_db->setQuery(
                    'SELECT ' . $this->_db->quoteName('lang_id') . ' AS id, ' . $this->_db->quoteName('title') . ', ' . $this->_db->quoteName('title_native') . ', ' . $this->_db->quoteName('lang_code') . ', ' . $this->_db->quoteName('sef') . ' FROM '
                        . $this->_db->quoteName('#__languages') . ' WHERE ' . $this->_db->quoteName('lang_id') . ' = '
                        . implode(' OR ' . $this->_db->quoteName('lang_id') . ' = ', $this->languages)
                );
                // Set the titles for the user categories.
                $this->languages = $this->_db->loadAssocList('title');

                // Check for a database error.
                if ($this->_db->getErrorNum()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }
            }
        }

        return $return;
    }

    /**
     * Validation and filtering
     *
     * @return  boolean  True is satisfactory
     *
     */
    function check()
    {
        jimport('joomla.mail.helper');

        // Validate user information
        if (trim($this->first_name) == '') {
            $error = new stdClass();
            $error->key = 'fist_name';
            $error->msg = 'First name is invalid';
            return false;
        }

        if (trim($this->last_name) == '') {
            $error = new stdClass();
            $error->key = 'last_name';
            $error->msg = 'Last name is invalid';
            $this->setError($error);
            return false;
        }

        if ((trim($this->email) == '') || !JMailHelper::isEmailAddress($this->email)) {
            $error = new stdClass();
            $error->key = 'email';
            $error->msg = 'Email is invalid';
            $this->setError($error);
            return false;
        }

        // Set the registration timestamp
        if ($this->registerDate == null || $this->registerDate == $this->_db->getNullDate()) {
            $this->registerDate = JFactory::getDate()->toMySQL();
        }

        // check for existing emails
        $query = 'SELECT id' . ' FROM #__subscribers ' . ' WHERE email = ' . $this->_db->Quote($this->email) . ($this->id !== 0 ? (' AND id <> ' . $this->_db->Quote((int)$this->id)) : '');
        $this->_db->setQuery($query);
        $xid = intval($this->_db->loadResult());

        if ($xid /*&& $xid != intval($this->id)*/) {
            $error = new stdClass();
            $error->key = 'email';
            $error->msg = 'This email has already been registered';
            $this->setError($error);
            return false;
        }

        return true;
    }

    /**
     * Method to store a row in the database from the JTable instance properties.
     * If a primary key value is set the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * a new row will be inserted into the database with the properties from the
     * JTable instance.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @link    http://docs.joomla.org/JTable/store
     */
    function store($updateNulls = false)
    {
        // Get the table key and key value.
        $k = $this->_tbl_key;
        $key = $this->$k;

        // TODO: This is a dumb way to handle the groups.
        // Store categories and languages locally so as to not update directly.
        $categories = $this->categories;
        unset($this->categories);

        $languages = $this->languages;
        unset($this->languages);

        // Insert or update the object based on presence of a key value.
        if ($key) {
            // Already have a table key, update the row.
            $return = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
        }
        else
        {
            // Don't have a table key, insert the row.
            $return = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        // Handle error if it exists.
        if (!$return) {
            $this->setError(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', strtolower(get_class($this)), $this->_db->getErrorMsg()));
            return false;
        }

        // Reset categories and languages to the local object.
        $this->categories = $categories;
        unset($categories);

        $this->languages = $languages;
        unset($languages);

        // Store the category and language data if the user data was saved.
        if ($return) {
            if (is_array($this->categories)) { // Delete the old user categroy maps.
                $this->_db->setQuery(
                    'DELETE FROM ' . $this->_db->quoteName('#__subscriber_category_map') .
                        ' WHERE ' . $this->_db->quoteName('subscriber_id') . ' = ' . (int)$this->id
                );
                $this->_db->query();

                // Check for a database error.
                if ($this->_db->getErrorNum()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }

                // Set the new user category maps.
                if (count($this->categories)) {

                    $this->_db->setQuery(
                        'INSERT INTO ' . $this->_db->quoteName('#__subscriber_category_map') . ' (' . $this->_db->quoteName('subscriber_id') . ', '
                            . $this->_db->quoteName('category_id') . ')' . ' VALUES (' . $this->id . ', '
                            . implode('), (' . $this->id . ', ', $this->_to_ids($this->categories)) . ')'
                    );

                    $this->_db->query();

                    // Check for a database error.
                    if ($this->_db->getErrorNum()) {
                        $this->setError($this->_db->getErrorMsg());
                        return false;
                    }
                }
            }

            /////////////////////////////////////////////////////////////////////////////

            if (is_array($this->languages)) { // Delete the old user language maps.
                $this->_db->setQuery(
                    'DELETE FROM ' . $this->_db->quoteName('#__subscriber_language_map') .
                        ' WHERE ' . $this->_db->quoteName('subscriber_id') . ' = ' . (int)$this->id
                );
                $this->_db->query();

                // Check for a database error.
                if ($this->_db->getErrorNum()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }

                // Set the new user language maps.
                if (count($this->languages)) {
                    $this->_db->setQuery(
                        'INSERT INTO ' . $this->_db->quoteName('#__subscriber_language_map') . ' (' . $this->_db->quoteName('subscriber_id') . ', '
                            . $this->_db->quoteName('language_id') . ')' . ' VALUES (' . $this->id . ', '
                            . implode('), (' . $this->id . ', ', $this->_to_ids($this->languages)) . ')'
                    );
                    $this->_db->query();

                    // Check for a database error.
                    if ($this->_db->getErrorNum()) {
                        $this->setError($this->_db->getErrorMsg());
                        return false;
                    }
                }
            }

            return true;
        }
    }

    /**
     * Method to delete a user, user categories, languages from the database.
     *
     * @param   integer  $userId  An optional user id.
     *
     * @return  boolean  True on success, false on failure.
     *
     */
    function delete($userId = null)
    {
        // Set the primary key to delete.
        $k = $this->_tbl_key;
        if ($userId) {
            $this->$k = intval($userId);
        }

        // Delete the user.
        $this->_db->setQuery(
            'DELETE FROM ' . $this->_db->quoteName($this->_tbl) .
                ' WHERE ' . $this->_db->quoteName($this->_tbl_key) . ' = ' . (int)$this->$k
        );
        $this->_db->query();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Delete the user category maps.
        $this->_db->setQuery(
            'DELETE FROM ' . $this->_db->quoteName('#__subscriber_category_map') .
                ' WHERE ' . $this->_db->quoteName('subscriber_id') . ' = ' . (int)$this->$k
        );
        $this->_db->query();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        //////////////////////////////////////////////////////////////////////////////////////

        // Delete the user language maps.
        $this->_db->setQuery(
            'DELETE FROM ' . $this->_db->quoteName('#__subscriber_language_map') .
                ' WHERE ' . $this->_db->quoteName('subscriber_id') . ' = ' . (int)$this->$k
        );
        $this->_db->query();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    private
    function _to_ids($array)
    {
        $ret = array();
        foreach ($array as $key => $val)
            $ret[] = (int)$val['id'];
        return $ret;
    }
}
