<?php

namespace publishing\mailsubscriptions\controllers;

use craft\web\Controller;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use publishing\mailsubscriptions\services\GroupsService;

class SubscriptionsController extends Controller
{
    public function actionCreateSubscription()
    {
        return $this->renderTemplate('mail-subscriptions/subscriptions/_new');
    }

    public function actionUpdateSubscription()
    {
        $request = \Craft::$app->getRequest();

        $id = $request->getRequiredParam('id');
        $groupId = $request->getRequiredParam('groupId');
        $firstName = $request->getRequiredParam('firstName');
        $lastName = $request->getRequiredParam('lastName');
        $email = $request->getRequiredParam('email');

        $subscriptionModel = new SubscriptionModel;
        $subscriptionModel->id = $id;
        $subscriptionModel->groupId = $groupId;
        $subscriptionModel->firstName = $firstName;
        $subscriptionModel->lastName = $lastName;
        $subscriptionModel->email = $email;

        Plugin::getInstance()->subscriptionsService->updateSubscription($subscriptionModel);

        return $this->redirect('mail-subscriptions/subscriptions');
    }

    /**
     *
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSubscription()
    {
        $request = \Craft::$app->getRequest();

        $email = $request->getRequiredParam('email');
        $groupId = $request->getRequiredParam('groupId');
        $firstName = $request->getRequiredParam('firstName');
        $lastName = $request->getRequiredParam('lastName');


        $subscriptionsService = Plugin::getInstance()->subscriptionsService;

        $subscriptionModel = new SubscriptionModel();
        $subscriptionModel->id = 0;
        $subscriptionModel->groupId = $groupId;
        $subscriptionModel->firstName = $firstName;
        $subscriptionModel->lastName = $lastName;
        $subscriptionModel->email = $email;

        $subscriptionsService->saveSubscription($subscriptionModel);

        return $this->redirect('mail-subscriptions/subscriptions');
    }

    public function actionEditSubscription(int $id)
    {
        if ($id > 0)
        {
            $subscription = Plugin::getInstance()->subscriptionsService->getSubscription($id);

            if ($subscription) {
                return $this->renderTemplate('mail-subscriptions/subscriptions/_edit', ['subscription' => $subscription]);
            }
        }

        return $this->asFailure('mail-subscriptions/subscriptions');
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