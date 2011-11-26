<?php

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Subscribers master display controller.
 *
 */
class NewsletterController extends JController
{
    public function __construct($config = array())
    {
        $this->_loadStyles();
        $this->_loadScripts();
        parent::__construct($config);
    }

    /*
     *  Loads the primaray view
     *
     * */
    public function display($cachable = false, $urlparams = false)
    {
        return parent::display($cachable, $urlparams);
    }

    private function _loadScripts()
    {
        $doc = &JFactory::getDocument();

        $doc->addScriptDeclaration("var baseurl='" . JURI::base(true) . "/index.php';" . PHP_EOL);

        $lang = explode('-', JFactory::getLanguage()->getTag());
        $doc->addScriptDeclaration("var lang='" . $lang[0] . "';" . PHP_EOL);

        //Add scripts

        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/public/console.js');

        //$doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/public/application.js');
        $doc->addScript('http://localhost:9294/application.js');

        // Make jQuery object available
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/public/index.js');

        //jQuery.form
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/public/jquery.form.js');

        //Bootstrap
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/public/bootstrap-alerts.js');
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/public/bootstrap-buttons.js');
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/public/bootstrap-twipsy.js');
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/public/bootstrap-popover.js');

        //Main
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/public/main.js');
    }

    private function _loadStyles()
    {
        $doc = &JFactory::getDocument();

        //Add styles

        //Bootstrap
        $doc->addStyleSheet(JURI::base(true) . '/components/com_newsletter/assets/public/bootstrap.css');

        //Main
        //$doc->addStyleSheet(JURI::base(true) . '/components/com_newsletter/assets/public/application.css');
        $doc->addStyleSheet('http://localhost:9294/application.css');
    }
}