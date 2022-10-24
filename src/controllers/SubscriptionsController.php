<?php

namespace publishing\mailsubscriptions\controllers;

use craft\web\Controller;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use publishing\mailsubscriptions\services\GroupsService;

class SubscriptionsController extends Controller
{
    public function actionSaveSubscription()
    {
        $request = \Craft::$app->getRequest();

        $email = $request->getRequiredParam('subscription-email');
        $sectionId = $request->getRequiredParam('section-id');
        //TODO set values
        $firstName = '';
        $lastName = '';

        $subscriptionsService = Plugin::getInstance()->subscriptionsService;

        $subscriptionModel = new SubscriptionModel();
        $subscriptionModel->id = 0;
        $subscriptionModel->groupId = $sectionId;
        $subscriptionModel->firstName = $firstName;
        $subscriptionModel->lastName = $lastName;
        $subscriptionModel->email = $email;

        $subscriptionsService->saveSubscription($subscriptionModel);
    }

    public function actionRemoveSubscription()
    {
        $request = \Craft::$app->getRequest();

        $sectionId = $request->getRequiredParam('subscription-id');

        /** @var GroupsService $settingsServices */
        $settingsServices = Plugin::getInstance()->groupsService;

        $settingsServices->removeSubscription($sectionId);
    }
}