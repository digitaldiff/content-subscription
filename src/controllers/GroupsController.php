<?php
namespace publishing\mailsubscriptions\controllers;

use craft\test\mockclasses\ToString;
use craft\web\Controller;
use publishing\mailsubscriptions\models\MailGroupModel;
use publishing\mailsubscriptions\models\SettingsModel;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use publishing\mailsubscriptions\services\GroupsService;
use yii\web\Response;

class GroupsController extends Controller
{
    //
    //  Form loads
    //

    public function actionCreateMailGroup(): Response
    {
        return $this->renderTemplate('mail-subscriptions/groups/_new');
    }

    public function actionEditMailGroup(int $id): Response
    {
        if ($id > 0)
        {
            $group = Plugin::getInstance()->groupsService->getMailGroup($id);

            if ($group) {
                return $this->renderTemplate('mail-subscriptions/groups/_edit', ['group' => $group]);
            }
        }

        return $this->asFailure('mail-subscriptions/groups');
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

        $subscriptionsService->saveMailGroup($groupModel);

        return $this->redirect('mail-subscriptions/groups');
    }

    public function actionUpdateMailGroup()
    {
        $request = \Craft::$app->getRequest();

        $subscriptionsService = Plugin::getInstance()->groupsService;

        $groupModel = new MailGroupModel;
        $groupModel->id = $request->getRequiredParam('id');
        $this->mapRequestToModel($request, $groupModel);

        $subscriptionsService->updateMailGroup($groupModel);

        return $this->redirect('mail-subscriptions/groups');
    }

    public function actionDeleteMailGroup($id): Response
    {
        if ($id > 0)
        {
            $success = Plugin::getInstance()->groupsService->removeGroup($id);

            if ($success) {
                \Craft::$app->getSession()->setSuccess(\Craft::t('mail-subscriptions', 'Group deleted'));
                return $this->redirect('mail-subscriptions/groups/');
            }
        }
        \Craft::$app->getSession()->setError(\Craft::t('mail-subscriptions', 'An error occurred'));

        return $this->redirect('mail-subscriptions/groups/');
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
        $groupModel->enabled = $request->getRequiredParam('enabled');
        $groupModel->enableUnsubscribing = $request->getRequiredParam('enableUnsubscribing');
    }
}