<?php
namespace publishing\mailsubscriptions;

use Craft;
use craft\base\Model;
use craft\events\DefineHtmlEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\Cp;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use publishing\mailsubscriptions\models\SettingsModel;
use publishing\mailsubscriptions\services\GroupsService;
use publishing\mailsubscriptions\services\SubscriptionsService;
use publishing\mailsubscriptions\twigextensions\DataHelperExtension;
use publishing\mailsubscriptions\variables\MailSubscriptionsVariable;
use yii\base\Event;
use craft\elements\Entry;
use craft\events\ModelEvent;

/**
 * @property GroupsService $groupsService;
 * @property SubscriptionsService $subscriptionsService;
 */
class Plugin extends \craft\base\Plugin
{
/*    public bool $hasCpSection = true;*/
    public bool $hasCpSettings = true;

    public function init(): void
    {
        parent::init();

        $this->setup();

        $this->registerEvents();

        $this->_registerTwigExtensions();
    }

    private function setup(): void
    {
        /** @var SettingsModel $settings */
        $settings = $this->getSettings();
        /*$this->hasCpSection = $settings->pluginEnabled;*/

        // Register Services
        $this->setComponents([
            'groupsService' => GroupsService::class,
            'subscriptionsService' => SubscriptionsService::class,
        ]);
    }

    private function registerEvents(): void
    {

        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'url' => 'mail-subscriptions',
                    'label' => 'Mail Subscriptions',
                    'subnav' => [
                        'groups' => ['label' => 'Mail Groups', 'url' => 'mail-subscriptions/groups'],
                        'subscriptions' => ['label' => 'Subscriptions', 'url' => 'mail-subscriptions/subscriptions'],
                        'field-layouts' => ['label' => 'Field Layouts', 'url' => 'mail-subscriptions/field-layouts'],
                    ],
                ];
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function(RegisterUrlRulesEvent $event) {
                // Field Layouts
                $event->rules['mail-subscriptions/groups/new'] = 'mail-subscriptions/groups/create-mail-group';
                $event->rules['mail-subscriptions/groups/edit/<id:\d+>'] = 'mail-subscriptions/groups/edit-mail-group';
                $event->rules['mail-subscriptions/groups/delete/<id:\d+>'] = 'mail-subscriptions/groups/delete-mail-group';

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
                    !($event->sender->duplicateOf && $event->sender->getIsCanonical() && !$event->sender->updatingFromDerivative) &&
                    $event->sender->firstSave
                ) {
                    \Craft::$app->getSession()->setNotice('EVENT_AFTER_SAVE triggered.');
                    self::getInstance()->groupsService->notificationEvent($event);
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
                $variable->set('mailSubscriptions', MailSubscriptionsVariable::class);
            }
        );

        /**
         * Entry-Sidebar event to add the notify-option to the entry view.
         */
        Event::on(
            Entry::class,
            Entry::EVENT_DEFINE_SIDEBAR_HTML,
            static function (DefineHtmlEvent $event) {
                //TODO replace with twig containing lightswitch to enable mail distribution.
                $html = '<fieldset>
<legend class="h6">Status</legend><div class="meta"><div id="enabled-field" class="field lightswitch-field" data-attribute="enabled"><div class="heading"><label id="enabled-label" for="enabled">Enabled</label></div><div class="input ltr">

        <button type="button" id="abc" class="lightswitch on" role="switch" aria-checked="true" aria-labelledby="enabled-label">        <div class="lightswitch-container">
            <div class="handle"></div>
        </div>
        <input type="hidden" name="abc" value="1">    </button></div></div></div></fieldset>';
                $event->html .= $html;
            }
        );
    }

    private function _registerTwigExtensions() : void
    {
        $extensions = [
            DataHelperExtension::class,
        ];

        foreach ($extensions as $extension) {
            Craft::$app->view->registerTwigExtension(new $extension);
        }
    }

    protected function createSettingsModel(): ?Model
    {
        return new SettingsModel();
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
        return \Craft::$app->getView()->renderTemplate('mail-subscriptions/_settings', [
            'settings' => $this->getSettings()
        ]);
    }
}