<?php
namespace publishing\contentsubscriptions\variables;

use craft\helpers\Template as TemplateHelper;
use craft\web\View;
use publishing\contentsubscriptions\models\MailGroupModel;
use publishing\contentsubscriptions\models\SubscriptionModel;
use publishing\contentsubscriptions\Plugin;

class ContentSubscriptionsVariable
{
    /**
     * @return string
     */
    public function getPluginName(): string
    {
        return Plugin::getInstance()->getSettings()->pluginName;
    }

    public function getMailGroups($id = 0): array
    {
        return Plugin::getInstance()->groupsService->getMailGroups($id);
    }

    public function getSubscriptions(): array
    {
        return Plugin::getInstance()->subscriptionsService->getSubscriptions();
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

    public function getMailGroupLabels(): array
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
    public function getSubscriptionForm($param)
    {
        $view = \Craft::$app->getView();

        $templatePath = 'content-subscriptions/_forms/subscription-form.twig';

        $fields = (new SubscriptionModel())->getFormProperties($param['groupId']);

        $fields['returnUrl'] = $param['returnUrl'] ?? '';
        $fields['btnValue'] = $param['btnValue'] ?? '';
        $fields['btnClass'] = $param['btnClass'] ?? '';
        $fields['formClass'] = $param['formClass'] ?? '';

        if ($view->doesTemplateExist($templatePath, View::TEMPLATE_MODE_CP)) {
                $html = $view->renderTemplate($templatePath, $fields, View::TEMPLATE_MODE_CP);
            return TemplateHelper::raw($html);
        }
        return '';
    }
}