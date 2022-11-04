<?php
namespace publishing\contentsubscriptions\controllers;

use craft\test\mockclasses\ToString;
use craft\web\Controller;
use publishing\contentsubscriptions\models\MailGroupModel;
use publishing\contentsubscriptions\models\SubscriptionModel;
use publishing\contentsubscriptions\Plugin;
use publishing\contentsubscriptions\services\GroupsService;
use yii\web\Response;

class GroupsController extends Controller
{
    //
    //  Form loads
    //

    public function actionCreateMailGroup(): Response
    {
        return $this->renderTemplate('content-subscriptions/groups/_new', ['group' => new MailGroupModel()]);
    }

    public function actionEditMailGroup(int $id): Response
    {
        if ($id > 0) {
            $group = Plugin::getInstance()->groupsService->getMailGroup($id);
        }
        
        if ($group) {
            return $this->renderTemplate('content-subscriptions/groups/_edit', ['group' => $group]);
        }

        return $this->asFailure('content-subscriptions/groups');
    }

    //
    //  DB Operations - Save / Update / Delete
    //

    public function actionSaveMailGroup()
    {
        $request = \Craft::$app->getRequest();

        $subscriptionsService = Plugin::getInstance()->groupsService;

        $groupModel = new MailGroupModel;

        $this->mapRequestToModel($request, $groupModel);

        if (!$groupModel->validate()) {
            return $this->renderTemplate('content-subscriptions/groups/_new', ['group' => $groupModel]);
        }

        $subscriptionsService->saveMailGroup($groupModel);

        return $this->redirect('content-subscriptions/groups');
    }

    public function actionUpdateMailGroup()
    {
        $request = \Craft::$app->getRequest();

        $subscriptionsService = Plugin::getInstance()->groupsService;

        $groupModel = new MailGroupModel;
        $groupModel->id = $request->getRequiredParam('id');
        $this->mapRequestToModel($request, $groupModel);

        if (!$groupModel->validate()) {
            return $this->renderTemplate('content-subscriptions/groups/_edit', ['group' => $groupModel]);
        }

        $subscriptionsService->updateMailGroup($groupModel);

        return $this->redirect('content-subscriptions/groups');
    }

    public function actionDeleteMailGroup($id): Response
    {
        if ($id > 0)
        {
            $success = Plugin::getInstance()->groupsService->removeGroup($id);

            if ($success) {
                \Craft::$app->getSession()->setSuccess(\Craft::t('content-subscriptions', 'Group deleted'));
                return $this->redirect('content-subscriptions/groups/');
            }
        }
        \Craft::$app->getSession()->setError(\Craft::t('content-subscriptions', 'An error occurred'));

        return $this->redirect('content-subscriptions/groups/');
    }

    /**
     * @param \craft\web\Request|\craft\console\Request|\yii\web\Request|\yii\console\Request $request
     * @param MailGroupModel $groupModel
     * @return void
     * @throws \yii\web\BadRequestHttpException
     */
    protected function mapRequestToModel(\craft\web\Request|\craft\console\Request|\yii\web\Request|\yii\console\Request $request, MailGroupModel $groupModel): void
    {
        $groupModel->sectionId = $request->getRequiredParam('section');
        $groupModel->groupName = $request->getRequiredParam('groupName');
        $groupModel->emailSubject = $request->getRequiredParam('emailSubject');
        $groupModel->emailBody = $request->getRequiredParam('emailMessage');
        $groupModel->optInSubject = $request->getRequiredParam('optInSubject');
        $groupModel->optInBody = $request->getRequiredParam('optInBody');
        $groupModel->enableUnsubscribing = $request->getRequiredParam('enableUnsubscribing');
        $groupModel->unsubscribeMessage = $request->getRequiredParam('unsubscribeMessage');
        $groupModel->enabled = $request->getRequiredParam('enabled');
    }
}