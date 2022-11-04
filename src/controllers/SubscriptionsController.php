<?php

namespace publishing\contentsubscriptions\controllers;

use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use craft\helpers\Template as TemplateHelper;
use craft\web\Application;
use craft\web\Controller;
use Exception;
use publishing\contentsubscriptions\models\SubscriptionModel;
use publishing\contentsubscriptions\Plugin;
use publishing\contentsubscriptions\records\ContentSubscriptions_SubscriptionRecord;
use publishing\contentsubscriptions\services\SubscriptionsService;
use yii\web\HttpException;

class SubscriptionsController extends Controller
{
    /**
     * @param string $hashValue
     * @return string|\Twig\Markup
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function actionValidate(string $hashValue)
    {
        $template = 'content-subscriptions/_layouts/message';
        $message = \Craft::t('content-subscriptions', 'Link not valid.');

        if (Plugin::getInstance()->subscriptionsService->validateSubscription($hashValue)) {
            $message = \Craft::t('content-subscriptions', 'E-Mail successfully verified.');
        }

        if ($this->view->doesTemplateExist($template, $this->view::TEMPLATE_MODE_CP)) {
            $html = $this->view->renderTemplate($template, ['message' => $message], $this->view::TEMPLATE_MODE_CP);
            return TemplateHelper::raw($html);
        }
        return '';

    }

    public function actionUnsubscribe(string $hashValue)
    {
        $template = 'content-subscriptions/_layouts/message';
        $message = \Craft::t('content-subscriptions', 'Link not valid.');

        $subscription = Plugin::getInstance()->subscriptionsService->getSubscriptionByHash($hashValue);
        if ($subscription) {
            $group = Plugin::getInstance()->groupsService->getMailGroup($subscription->groupId);

            if(Plugin::getInstance()->subscriptionsService->userUnsubscribe($hashValue)) {
                $message = $group?->unsubscribeMessage;
            }
        }
        if ($this->view->doesTemplateExist($template, $this->view::TEMPLATE_MODE_CP)) {
            $html = $this->view->renderTemplate($template, ['message' => $message], $this->view::TEMPLATE_MODE_CP);
            return TemplateHelper::raw($html);
        }
    }

    //
    //  CP Form loads
    //
    public function actionCreateSubscription()
    {
        return $this->renderTemplate('content-subscriptions/subscriptions/_new', ['subscription' => new SubscriptionModel()]);
    }

    public function actionEditSubscription(int $id)
    {
        if ($id > 0)
        {
            $subscription = Plugin::getInstance()->subscriptionsService->getSubscription($id);

            if ($subscription) {
                return $this->renderTemplate('content-subscriptions/subscriptions/_edit', ['subscription' => $subscription]);
            }
        }

        return $this->asFailure('content-subscriptions/subscriptions');
    }

    public function actionSendVerificationMail($hashValue)
    {
        if (!\Craft::$app->getUser()->getIdentity()) {
           return '';
        }
        if (Plugin::getInstance()->notificationsService->initiateVerification($hashValue)) {
            \Craft::$app->getSession()->setSuccess(\Craft::t('content-subscriptions', 'Verification mail sent.'));
        } else {
            \Craft::$app->getSession()->setError(\Craft::t('content-subscriptions', 'Verification mail couldn\'t be sent.'));
        }

        return $this->redirect('content-subscriptions/subscriptions');
    }

    //
    //  FE DB Operations - Save
    //

    public function actionSubscribeToGroup()
    {
        $request = \Craft::$app->getRequest();
        $returnUrl = $request->getRequiredParam('returnUrl') !== '' ? $request->getRequiredParam('returnUrl') :$request->getFullPath();

        // Honeypot
        if ($request->getParam('pot-email') !== '') {
            return $this->redirect($returnUrl);
        }

        // TODO check for spam via cookie / timestamp

        $subscriptionModel = new SubscriptionModel;

        $subscriptionModel->groupId = $request->getRequiredParam('groupId');
        $subscriptionModel->firstName = $request->getRequiredParam('firstName');
        $subscriptionModel->lastName = $request->getRequiredParam('lastName');
        $subscriptionModel->email = $request->getRequiredParam('email');

        $subscriptionModel->verificationStatus = false;
        $subscriptionModel->enabled = true;

        $subscriptionModel->generateHash();

        $subscriptionService = Plugin::getInstance()->subscriptionsService;
        $isDuplicate = $subscriptionService->checkForDuplicates($subscriptionModel->groupId, $subscriptionModel->email);

        if (!$isDuplicate)
        {
            $hashValue = $subscriptionService->saveSubscription($subscriptionModel);

            if ($hashValue) {
                Plugin::getInstance()->notificationsService->initiateVerification($hashValue);
            }
        }

        return $this->redirect($returnUrl. '?s=1');
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

        if (!$subscriptionModel->validate()) {
            return $this->renderTemplate('content-subscriptions/subscriptions/_new', ['subscription' => $subscriptionModel]);
        }

        $isDuplicate = (Plugin::getInstance()->subscriptionsService)->checkForDuplicates($subscriptionModel->groupId, $subscriptionModel->email);
        if ($isDuplicate) {
            \Craft::$app->getSession()->setError(\Craft::t('content-subscriptions','Duplicate entry found - no entry created'));
            return $this->renderTemplate('content-subscriptions/subscriptions/_new', [ 'subscription' => $subscriptionModel]);
        }

        (Plugin::getInstance()->subscriptionsService)->saveSubscription($subscriptionModel);

        return $this->redirect('content-subscriptions/subscriptions');
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

        if (!$subscriptionModel->validate()) {
            return $this->renderTemplate('content-subscriptions/subscriptions/_edit', ['subscription' => $subscriptionModel]);
        }

        Plugin::getInstance()->subscriptionsService->updateSubscription($subscriptionModel);

        return $this->redirect('content-subscriptions/subscriptions');
    }

    public function actionDeleteSubscription(int $id)
    {
        $request = \Craft::$app->getRequest();

        /** @var SubscriptionsService $settingsServices */
        $settingsServices = Plugin::getInstance()->subscriptionsService;

        $settingsServices->removeSubscription($id);
        return $this->redirect('content-subscriptions/subscriptions');
    }
}