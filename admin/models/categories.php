<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Categories model
 *
 */
class NewsletterModelCategories extends JModel
{
    private $_categories;

    public function getCategories()
    {
        if(empty($this->_categories)) {
            $level = JRequest::getInt('level', 1);

            $this->_db->setQuery(
                'SELECT id, title, alias' . ' FROM #__categories WHERE extension=' . $this->_db->Quote('com_content') . ' AND level=' . $this->_db->Quote($level) . 'AND published=' .$this->_db->Quote(1)
            );
            $this->_categories = $this->_db->loadAssocList();

        }

        return $this->_categories;
    }
}