<?php

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Notifications Content Plugin
 */
class plgContentNotification extends JPlugin
{
    private $mailer;
    private $sender_email;
    private $sender_name;

    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        $this->mailer = &JFactory::getMailer();

        $cfg = JFactory::getConfig();

        $this->sender_email = $cfg->get('mailfrom');
        $this->sender_name = $cfg->get('fromname');
    }


    private function sendNotification(&$article, &$info, $isLang)
    {
        list(
            $id,
            $fname,
            $lname,
            $email,
            $category_title,
            $category_alias,
            $category,
            $lang,
            $code,
            $native_title,
            $title) = $info;

        jimport('joomla.mail.mail');
        jimport('joomla.mail.helper');

        $url = JURI::base() . JRoute::_("/index.php?option=com_newsletter&task=subscriber.deactivate&id=" . $article->get('id'));
        $fn = ucfirst($fname);
        $ln = ucfirst($lname);
        $message = <<<EMAIL

<div>

    <p>Bonjour  $fn $ln </p>

    <p>Une nouvelle artcile vien d'etre publiee sur notre site.</p>

    <p>Cliquez à présent sur ce lien pour <a
            href="http://muazcisse.org/joomla/"
            target="_blank">voir l'article</a>. </p>

    <p>Ce message a été généré automatiquement par notre site. Veuillez ne pas y répondre. Merci</p>

    <table style="border-top:1px solid #999;padding-top:4px;margin-top:1.5em;width:100%;">
        <tbody>
        <tr>
            <td style="text-align:left;font-family:Helvetica, Arial, Sans-Serif;font-size:11px;margin:0 6px 1.2em 0;color:#333;">You are subscribed to email updates from <a rel="nofollow"
                                                                                                                                                                             target="_blank"
                                                                                                                                                                             href="http://muazciss.org">Muaz Cisse
                Blog</a> <br>To stop receiving these emails, you may <a rel="nofollow" target="_blank" href="$url">unsubscribe now</a>.
            </td>
        </tr>
        </tbody>
    </table>

</div>

EMAIL;

        if (!$this->mailer->sendMail($this->sender_email, $this->sender_name, $email, 'New article from Muaz Cisse\'s Blog', $message, true)) {
            JError::raiseError(500, 'Email failed.');
        }
    }

    public function onContentAfterSave($context, &$article, $isNew)
    {
        // Check we are handling the frontend edit form.
        if ($context != 'com_content.article') {
            return true;
        }

        // Check this is a new article.
        if (!$isNew) {
            return true;
        }

        if($article->get('state') === 0) {
            return true;
        }

        if($article->get('access') !== '1') {
            return true;
        }

        $catid = (int)$article->get('catid');

        if (!$catid) {
            return true;
        }

        $lang = $article->get('language');

        // Get subscribers to this category
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query

            ->select(
                  array(
                      $db->quoteName('subscriber.id') . ' AS id',
                      $db->quoteName('first_name'),
                      $db->quoteName('last_name'),
                      $db->quoteName('email'),

                      // Category info
                      $db->quoteName('category.title') . ' AS category_title',
                      $db->quoteName('category.alias') . ' AS category_alias',
                      $db->quoteName('category.id') . ' AS category_id'))

            ->from($db->quoteName('#__subscribers') . ' AS subscriber')
            ->from($db->quoteName('#__subscriber_category_map') . ' AS category_map')
            ->from($db->quoteName('#__categories') . ' AS category')

            ->where($db->quoteName('subscriber.activation') . ' = ' . $db->quote('')) //make sure the subscriber is activated
            ->where($db->quoteName('subscriber.published') . ' = ' . $db->quote(1)) //make sure the subscriber is published

        // Subscriber2Category JOIN
        ->where('subscriber.id = category_map.subscriber_id')
        ->where('category_map.category_id = category.id')

        // And finally

        ->where('category.id = ' . $db->quote($catid))

        ;

        if($isLang = ($lang and $lang !== '*')) {

            $query

                ->select(

                    // Language info
                    array(
                        $db->quoteName('language.lang_id') . ' AS language_id',
                        $db->quoteName('language.lang_code') . ' AS language_code',
                        $db->quoteName('language.title_native') . ' AS language_title_native',
                        $db->quoteName('language.title') . ' AS language_title'
                    ))

            ->from($db->quoteName('#__subscriber_language_map') . ' AS language_map')
            ->from($db->quoteName('#__languages') . ' AS language')

            //Subscriber3Language JOIN
            ->where('subscriber.id = language_map.subscriber_id')
            ->where('language.lang_id = language_map.language_id')

            ->where('language.lang_code IN ( ' . $db->quote('*') . ', ' . $db->quote($lang) . ')')

            ;
        }

        $db->setQuery($query);

        foreach ($db->loadRowList() as $info)
            $this->sendNotification(&$article, &$info, $isLang);

        return true;
    }
}
