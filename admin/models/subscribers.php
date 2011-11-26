<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Subscribers model
 *
 */
class NewsletterModelSubscribers extends JModelList
{
    /**
     * @var        string    The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_NEWSLETTER_NEWSLETTER';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'first_name', 'a.first_name',
                'last_name', 'a.last_name',
                'email', 'a.email',
                'published', 'a.published',
                'registerDate', 'a.registerDate',
                'created_user_id', 'a.created_user_id',
                'activation', 'a.activation',
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {

        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout', 'default')) {
            $this->context .= '.' . $layout;
        }

        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $active = $this->getUserStateFromRequest($this->context . '.filter.active', 'filter_active');
        $this->setState('filter.active', $active);

        $state = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published');
        $this->setState('filter.published', $state);

        $categoryId = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_category_id', null, 'int');
        $this->setState('filter.category_id', $categoryId);

        $languageId = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language_id', null, 'int');
        $this->setState('filter.language_id', $languageId);

        // List state information.
        parent::populateState('a.registerDate', 'DESC');
    }

    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.active');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.language_id');

        return parent::getStoreId($id);
    }

    public function getItems()
    {
        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (empty($this->cache[$store])) {
            $items = parent::getItems();


            // Bail out on an error or empty list.
            if (empty($items)) {
                $this->cache[$store] = $items;

                return $items;
            }

            // Joining the groups with the main query is a performance hog.
            // Find the information only on the result set.

            // First pass: get list of the user id's and reset the counts.
            $subscriberIds = array();
            foreach ($items as $item)
            {
                $subscriberIds[] = (int)$item->id;

                if($item->created_user_id) {
                    $createdBy = JFactory::getUser($item->created_user_id);
                    unset($item->created_user_id);

                    if($createdBy and $createdBy->name)
                        $item->created_username = $createdBy->name;
                }

                $item->registerDate = gmDate("Y-m-d\TH:i:s\Z", strtotime($item->registerDate)); //date('c', strtotime($item->registerDate));
                $item->published = $item->published === '0' ? false : true;
                $item->categories = array();
                $item->languages = array();
            }

            // Get the counts from the database only for the users in the list.
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            // Join over the group mapping table.
            $query->select('map.subscriber_id AS subscriber_id, map.category_id AS catid, category.title AS category_title, category.alias AS category_alias')
                ->from('#__subscriber_category_map AS map')
                ->join('LEFT', '#__categories AS category ON category.id = map.category_id')
                ->where('map.subscriber_id IN (' . implode(',', $subscriberIds) . ')');
                //->group('map.category_id');

            $db->setQuery($query);

            // Load the counts into an array indexed on the user id field.
            $subscriberCategories = $db->loadObjectList();

            $error = $db->getErrorMsg();
            if ($error) {
                $this->setError($error);

                return false;
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            $query = $db->getQuery(true);
            // Join over the group mapping table.
            $query->select('map.subscriber_id AS subscriber_id, map.language_id AS langid, language.title AS language_title, language.title_native AS language_title_native, language.lang_code AS language_code, language.sef AS language_sef')
                ->from('#__subscriber_language_map AS map')
                ->join('LEFT', '#__languages AS language ON language.lang_id = map.language_id')
                ->where('map.subscriber_id IN (' . implode(',', $subscriberIds) . ')');
                //->group('map.language_id');

            $db->setQuery($query);

            // Load the counts into an array indexed on the user id field.
            $subscriberLanguages = $db->loadObjectList();

            $error = $db->getErrorMsg();
            if ($error) {
                $this->setError($error);

                return false;
            }

            /////////////////////////////////////////////////////////////////////////////////////

            foreach ($subscriberCategories as &$category) {
                foreach ($items as &$item)
                    if ($item->id == $category->subscriber_id)
                        $item->categories[] = array(
                            'id' => $category->catid,
                            'title' => $category->category_title,
                            'alias' => $category->category_alias
                        );
            }

            foreach ($subscriberLanguages as &$language) {
                foreach ($items as &$item)
                    if ($item->id == $language->subscriber_id)
                        $item->languages[] = array(
                            'id' => $language->langid,
                            'title' => $language->language_title,
                            'title_native' => $language->language_title_native,
                            'code' => $language->language_code,
                            'sef' => $language->language_sef,
                            'default' => JFactory::getLanguage()->getTag() === $language->language_code
                                ? true : false
                        );
            }

            // Add the items to the internal cache.
            $this->cache[$store] = $items;
        }

        return $this->cache[$store];
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return    JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        /*return $query->from($db->quoteName('#__subscribers'))
                    ->select('*')
                    ->order('registerDate DESC');*/

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );
        $query->from('`#__subscribers` AS a');

        // If the model is set to check item state, add to the query.
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where('a.published = ' . (int)$published);
        }

        // If the model is set to check the activated state, add to the query.
        $active = $this->getState('filter.active');

        if (is_numeric($active)) {
            if ($active == '0') {
                $query->where('a.activation = ' . $db->quote(''));
            }
            else if ($active == '1') {
                $query->where('LENGTH(a.activation) = 32');
            }
        }

        // Filter the items over the group id if set.
        $languageId = $this->getState('filter.language_id');
        if ($languageId) {
            $query->join('LEFT', '#__subscriber_language_map AS map2 ON map2.subscriber_id = a.id');
            $query->where('map2.language_id = ' . (int)$languageId);
        }

        $categoryId = $this->getState('filter.category_id');
        if ($categoryId) {
            $query->join('LEFT', '#__subscriber_category_map AS map3 ON map3.subscriber_id = a.id');
            $query->where('map3.category_id = ' . (int)$categoryId);
        }

        if ($languageId || $categoryId)
            $query->group('a.id');

        // Filter the items over the search string if set.
        if ($this->getState('filter.search') !== '') {
            // Escape the search token.
            $token = $db->Quote('%' . $db->getEscaped($this->getState('filter.search')) . '%');

            // Compile the different search clauses.
            $searches = array();
            $searches[] = 'a.first_name LIKE ' . $token;
            $searches[] = 'a.last_name LIKE ' . $token;
            $searches[] = 'a.email LIKE ' . $token;

            // Add the clauses to the query.
            $query->where('(' . implode(' OR ', $searches) . ')');
        }

        // Add the list ordering clause.
        $query->order($db->getEscaped($this->getState('list.ordering', 'a.registerDate')) . ' ' . $db->getEscaped($this->getState('list.direction', 'DESC')));

        return $query;
    }
}