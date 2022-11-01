<?php

namespace publishing\mailsubscriptions\controllers;

use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use craft\helpers\Template as TemplateHelper;
use craft\web\Application;
use craft\web\Controller;
use publishing\mailsubscriptions\events\UserSubscribedEvent;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use publishing\mailsubscriptions\records\ContentSubscriptions_SubscriptionRecord;
use publishing\mailsubscriptions\services\SubscriptionsService;

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
        $template = 'mail-subscriptions/layouts/_message';
        $message = \Craft::t('mail-subscriptions', 'Link not valid.');

        if (Plugin::getInstance()->subscriptionsService->validateSubscription($hashValue)) {
            //return $this->renderTemplate('mail-subscriptions/subscriptions/_new');
            $message = \Craft::t('mail-subscriptions', 'E-Mail successfully verified.');
        }

        if ($this->view->doesTemplateExist($template, $this->view::TEMPLATE_MODE_CP)) {
            $html = $this->view->renderTemplate($template, ['message' => $message], $this->view::TEMPLATE_MODE_CP);
            return TemplateHelper::raw($html);
        }
        return '';

    }

    public function actionUnsubscribe(string $hashValue)
    {
        $template = 'mail-subscriptions/layouts/_message';
        $message = \Craft::t('mail-subscriptions', 'Link not valid.');

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

    public function actionTest()
    {
        dd('hello');
    }
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

    public function actionSendVerificationMail($hashValue)
    {
        if (!\Craft::$app->getUser()->getIdentity()) {
           return '';
        }
        if (Plugin::getInstance()->notificationsService->initiateVerification($hashValue)) {
            \Craft::$app->getSession()->setSuccess(\Craft::t('mail-subscriptions', 'Verification mail sent.'));
        } else {
            \Craft::$app->getSession()->setError(\Craft::t('mail-subscriptions', 'Verification mail couldn\'t be sent.'));
        }

        return $this->redirect('mail-subscriptions/subscriptions');
    }

    //
    //  FE DB Operations - Save
    //

    public function actionSubscribeToGroup()
    {
        $request = \Craft::$app->getRequest();

        $subscriptionModel = new SubscriptionModel;

        $subscriptionModel->groupId = $request->getRequiredParam('groupId');
        $subscriptionModel->firstName = $request->getRequiredParam('firstName');
        $subscriptionModel->lastName = $request->getRequiredParam('lastName');
        $subscriptionModel->email = $request->getRequiredParam('email');

        $subscriptionModel->verificationStatus = false;
        $subscriptionModel->enabled = true;

        $subscriptionModel->generateHash();

        $subscriptionService = Plugin::getInstance()->subscriptionsService;
        $hashValue = $subscriptionService->saveSubscription($subscriptionModel);

        // Trigger event on subscription
        if ($hashValue) {
            Plugin::getInstance()->notificationsService->initiateVerification($hashValue);

            $event = new UserSubscribedEvent([
                'subscriptionModel' => $subscriptionModel,
            ]);
            $this->trigger($subscriptionService::EVENT_USER_SUBSCRIBED, $event);
        }

        return $this->redirect($request->getFullPath());
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