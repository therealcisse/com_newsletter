<?php

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Subscribers form display controller.
 *
 */
class SubscriberController extends JController
{

    public function __construct($config = array())
    {
        $this->_loadStyles();
        $this->_loadScripts();
        parent::__construct($config);
    }

    private function checkQapTcha()
    {
        if (isset($_POST['subscriber_form_submit'])) {

            if (isset($_POST['iQapTcha']) && empty($_POST['iQapTcha']) && isset($_SESSION['iQaptcha']) && $_SESSION['iQaptcha']) {
                unset($_SESSION['iQaptcha']);
                return true;
            }

            return false; // Submitted but no captcha validated
        }
        return true; // not submitted
    }

    public function display($cachable = false, $urlparams = false)
    {
        $this->checkQapTcha() or jexit('Wrong qaptcha');
        return parent::display($cachable, $urlparams);
    }


    public function qaptcha()
    {
        echo json_encode(array('error' => false));
        return false;
    }

    public function subscribe()
    {
        JRequest::setVar('layout', 'subscribed');
        return parent::display();
    }

    private function _loadScripts()
    {
        $doc = &JFactory::getDocument();

        $doc->addScriptDeclaration("var baseurl='" . JURI::base(true) . "/index.php';");

        $lang = explode('-', JFactory::getLanguage()->getTag());
        $doc->addScriptDeclaration("var lang='" . $lang[0] . "';");

        //Add scripts

        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/js/jquery.js');
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/js/jquery.form.js');
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/js/jquery-ui.js');

        // QapTcha
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/js/QapTcha.jquery.js');

        //Bootstrap
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/js/bootstrap-alerts.js');
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/js/bootstrap-buttons.js');

        //Main
        $doc->addScript(JURI::base(true) . '/components/com_newsletter/assets/js/main.js');
    }

    private function _loadStyles()
    {
        $doc = &JFactory::getDocument();

        //Add styles

        //Bootstrap
        $doc->addStyleSheet(JURI::base(true) . '/components/com_newsletter/assets/css/bootstrap.css');

        //QapTcha
        $doc->addStyleSheet(JURI::base(true) . '/components/com_newsletter/assets/css/QapTcha.jquery.css');

        //Main
        $doc->addStyleSheet(JURI::base(true) . '/components/com_newsletter/assets/css/style.css');
    }
}