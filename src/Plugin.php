<?php
namespace publishing\contentsubscriptions;

use Craft;
use craft\base\Model;
use craft\events\DefineHtmlEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\Cp;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use publishing\contentsubscriptions\services\GroupsService;
use publishing\contentsubscriptions\services\NotificationsService;
use publishing\contentsubscriptions\services\SubscriptionsService;
use publishing\contentsubscriptions\twigextensions\DataHelperExtension;
use publishing\contentsubscriptions\variables\ContentSubscriptionsVariable;
use yii\base\Event;
use craft\elements\Entry;
use craft\events\ModelEvent;

/**
 * @property GroupsService $groupsService;
 * @property SubscriptionsService $subscriptionsService;
 * @property NotificationsService $notificationsService;
 * @property-read null|array $cpNavItem
 */
class Plugin extends \craft\base\Plugin
{
    public bool $hasCpSection = true;
    public bool $hasCpSettings = true;

    public function init(): void
    {
        parent::init();

        $this->setup();

        $this->registerEvents();

        $this->_registerTwigExtensions();
    }

    protected function setup(): void
    {
        // Register Services
        $this->setComponents([
            'groupsService' => GroupsService::class,
            'subscriptionsService' => SubscriptionsService::class,
            'notificationsService' => NotificationsService::class
        ]);
    }

    protected function registerEvents(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function(RegisterUrlRulesEvent $event) {
                // Field Layouts
                $event->rules['content-subscriptions/groups/new'] = 'content-subscriptions/groups/create-mail-group';
                $event->rules['content-subscriptions/groups/edit/<id:\d+>'] = 'content-subscriptions/groups/edit-mail-group';
                $event->rules['content-subscriptions/groups/delete/<id:\d+>'] = 'content-subscriptions/groups/delete-mail-group';
                $event->rules['content-subscriptions/subscriptions/new'] = 'content-subscriptions/subscriptions/create-subscription';
                $event->rules['content-subscriptions/subscriptions/edit/<id:\d+>'] = 'content-subscriptions/subscriptions/edit-subscription';
                $event->rules['content-subscriptions/subscriptions/delete/<id:\d+>'] = 'content-subscriptions/subscriptions/delete-subscription';
                $event->rules['content-subscriptions/subscriptions/sendVerificationMail/<hashValue:\w+>'] = 'content-subscriptions/subscriptions/send-verification-mail';
            }
        );

        /**
         * Entry-Save event as notification trigger.
         */
        Event::on(
            Entry::class,
            Entry::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                if (
                    $_POST['content-subscription']
                ) {
                    $this->notificationsService->notificationEvent($event);
                }
            }
        );

        /**
         * Make methods available in twig.
         */
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->set('mailSubscriptions', ContentSubscriptionsVariable::class);
            }
        );

        /**
         * Entry-Sidebar event to add the notify-option to the entry view.
         */
        Event::on(
            Entry::class,
            Entry::EVENT_DEFINE_SIDEBAR_HTML,
            static function (DefineHtmlEvent $event) {
                $html =  Craft::$app->view->renderTemplate('content-subscriptions/_field/sendNotificationLightswitch');
                $event->html .= $html;
            }
        );
    }

    protected function _registerTwigExtensions() : void
    {
        $extensions = [
            DataHelperExtension::class,
        ];

        foreach ($extensions as $extension) {
            Craft::$app->view->registerTwigExtension(new $extension);
        }
    }

    public function getCpNavItem(): ?array
    {
        $nav = parent::getCpNavItem();

        $nav['label'] = \Craft::t('content-subscriptions', 'Content Subscriptions');
        $nav['url'] = 'content-subscriptions';

        $nav['subnav']['groups'] = [
            'label' => Craft::t('content-subscriptions', 'Mail groups'),
            'url' => 'content-subscriptions/groups',
        ];

        $nav['subnav']['subscriptions'] = [
            'label' => Craft::t('content-subscriptions', 'Subscriptions'),
            'url' => 'content-subscriptions/subscriptions',
        ];

        /*$nav['subnav']['field-layouts'] = [
            'label' => Craft::t('content-subscriptions', 'Field Layouts'),
            'url' => 'content-subscriptions/field-layouts',
        ];*/

        if (Craft::$app->getUser()->getIsAdmin()) {
            $nav['subnav']['settings'] = [
                'label' => Craft::t('content-subscriptions', 'Settings'),
                'url' => 'content-subscriptions/settings',
            ];
        }

        return $nav;
    }

    /**
     * @return string|null
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    protected function settingsHtml(): ?string
    {
        return \Craft::$app->getView()->renderTemplate('content-subscriptions/_settings', [
            'settings' => $this->getSettings()
        ]);
    }
}