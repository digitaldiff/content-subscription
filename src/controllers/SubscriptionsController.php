<?php

namespace publishing\mailsubscriptions\controllers;

use craft\web\Controller;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use publishing\mailsubscriptions\services\GroupsService;

class SubscriptionsController extends Controller
{
    //
    //  Form loads
    //

    public function actionCreateSubscription()
    {
        return $this->renderTemplate('mail-subscriptions/subscriptions/_new');
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

    //
    //  DB Operations - Save / Update / Delete
    //

    public function actionUpdateSubscription()
    {
        $request = \Craft::$app->getRequest();

        $subscriptionModel = new SubscriptionModel;
        $subscriptionModel->id = $request->getRequiredParam('id');
        $subscriptionModel->groupId = $request->getRequiredParam('groupId');
        $subscriptionModel->firstName = $request->getRequiredParam('firstName');
        $subscriptionModel->lastName = $request->getRequiredParam('lastName');
        $subscriptionModel->email = $request->getRequiredParam('email');
        $subscriptionModel->enabled = $request->getRequiredParam('enabled');
        $subscriptionModel->verificationStatus = $request->getRequiredParam('verificationStatus');

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

        $subscriptionsService = Plugin::getInstance()->subscriptionsService;

        $subscriptionModel = new SubscriptionModel();
        $subscriptionModel->id = 0;
        $subscriptionModel->email = $request->getRequiredParam('email');
        $subscriptionModel->groupId = $request->getRequiredParam('groupId');
        $subscriptionModel->firstName = $request->getRequiredParam('firstName');
        $subscriptionModel->lastName = $request->getRequiredParam('lastName');
        $subscriptionModel->enabled = $request->getRequiredParam('enabled');
        $subscriptionsService->saveSubscription($subscriptionModel);

        return $this->redirect('mail-subscriptions/subscriptions');
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