<?php

namespace publishing\mailsubscriptions\controllers;

use craft\web\Controller;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use publishing\mailsubscriptions\services\SubscriptionsService;

class SubscriptionsController extends Controller
{
    //
    //  CP Form loads
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
    //  FE DB Operations - Save
    //

    public function actionSubscribeToGroup()
    {
        $request = \Craft::$app->getRequest();

        return $this->redirect($request->getFullPath());
        $subscriptionModel = new SubscriptionModel;

        $subscriptionModel->groupId = $request->getRequiredParam('groupId');
        $subscriptionModel->firstName = $request->getRequiredParam('firstName');
        $subscriptionModel->lastName = $request->getRequiredParam('lastName');
        $subscriptionModel->email = $request->getRequiredParam('email');

        $subscriptionModel->verificationStatus = false;
        $subscriptionModel->enabled = true;

        $subscriptionModel->generateHash();

        (Plugin::getInstance()->subscriptionsService)->saveSubscription($subscriptionModel);

        return $this->renderTemplate($request->getFullUri());
    }

    //
    //  CP DB Operations - Save / Update / Delete
    //
    /**
     *
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSubscription()
    {
        $request = \Craft::$app->getRequest();

        $subscriptionModel = new SubscriptionModel();

        $subscriptionModel->id = 0;

        $subscriptionModel->groupId = $request->getRequiredParam('groupId');
        $subscriptionModel->firstName = $request->getRequiredParam('firstName');
        $subscriptionModel->lastName = $request->getRequiredParam('lastName');
        $subscriptionModel->email = $request->getRequiredParam('email');
        $subscriptionModel->enabled = $request->getRequiredParam('enabled');
        (Plugin::getInstance()->subscriptionsService)->saveSubscription($subscriptionModel);

        return $this->redirect('mail-subscriptions/subscriptions');
    }

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

    public function actionDeleteSubscription(int $id)
    {
        $request = \Craft::$app->getRequest();

        /** @var SubscriptionsService $settingsServices */
        $settingsServices = Plugin::getInstance()->subscriptionsService;

        $settingsServices->removeSubscription($id);
        return $this->redirect('mail-subscriptions/subscriptions');
    }
}