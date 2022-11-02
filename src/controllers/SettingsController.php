<?php

namespace publishing\contentsubscriptions\controllers;

use publishing\contentsubscriptions\Plugin;
use yii\web\Response;

class SettingsController extends \craft\web\Controller
{
    public function actionSettings(): Response
    {
        $settings = Plugin::getInstance()->getSettings();

        return $this->renderTemplate('content-subscriptions/settings/general', [
            'settings' => $settings,
        ]);
    }
}