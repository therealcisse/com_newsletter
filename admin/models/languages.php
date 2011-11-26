<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Languages model
 *
 */
class NewsletterModelLanguages extends JModel
{
    private $_languages;

    public function getLanguages()
    {
        if(empty($this->_languages)) {
            $this->_db->setQuery(
                'SELECT lang_id AS id, lang_code AS code, title_native, title, sef, case lang_code when ' . $this->_db->Quote(JFactory::getLanguage()->getTag()) . ' then 1 else 0 end AS is_default' . ' FROM #__languages'
            );
            $this->_languages = $this->_db->loadAssocList();
        }

        return $this->_languages;
    }
}