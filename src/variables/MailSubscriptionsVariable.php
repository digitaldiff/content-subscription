<?php
namespace publishing\mailsubscriptions\variables;

use craft\helpers\Template as TemplateHelper;
use craft\web\View;
use publishing\mailsubscriptions\models\MailGroupModel;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use function Psy\debug;

class MailSubscriptionsVariable
{
    public function getMailGroups($id = 0)
    {
        return Plugin::getInstance()->groupsService->getMailGroups($id);
    }

    public function getSubscriptions(): array
    {
        return Plugin::getInstance()->subscriptionsService->getSubscriptions();
    }

    public function removeGroup($id): bool
    {
        return Plugin::getInstance()->groupsService->removeGroup($id);
    }

    public function getSections(): array
    {
        $sections = \Craft::$app->getSections()->getAllSections();
        $result = [];
        foreach ($sections as $section) {
             $result[$section->id] = $section->name;
        }
        return $result;
    }

    public function getMailGroupLabels()
    {
        $result = [];
        /** @var MailGroupModel[] $groups */
        $groups = $this->getMailGroups();

        foreach ($groups as $group) {
            $result[$group->id] = $group->groupName;
        }
        return $result;
    }

    /**
     * @param $id
     * @return string|\Twig\Markup
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     *
     * Render a form element to enable site visitors to sign up for the mail subscription
     */
    public function getSubscriptionForm($id)
    {
        $view = \Craft::$app->getView();

        $templatePath = 'mail-subscriptions/_forms/subscription-form.twig';

        $fields = (new SubscriptionModel())->getFormProperties($id);

        if ($view->doesTemplateExist($templatePath, View::TEMPLATE_MODE_CP)) {
            $html = $view->renderTemplate($templatePath, $fields, View::TEMPLATE_MODE_CP);
            return TemplateHelper::raw($html);
        }
        return '';
    }
}