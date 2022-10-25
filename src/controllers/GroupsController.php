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
    public function actionSaveMailGroup()
    {
        $request = \Craft::$app->getRequest();

        $emailSubject = $request->getRequiredParam('emailSubject');
        $emailBody = $request->getRequiredParam('emailMessage');
        $groupName = $request->getRequiredParam('groupName');
        $sectionId = $request->getRequiredParam('section');

        //TODO set values

        $subscriptionsService = Plugin::getInstance()->groupsService;

        $groupModel = new MailGroupModel;
        $groupModel->sectionId = $sectionId;
        $groupModel->groupName = $groupName;
        $groupModel->emailSubject = $emailSubject;
        $groupModel->emailBody = $emailBody;

        $subscriptionsService->saveMailGroup($groupModel);

        return $this->redirect('mail-subscriptions/groups');
    }

    public function actionUpdateMailGroup()
    {
        $request = \Craft::$app->getRequest();

        $emailSubject = $request->getRequiredParam('emailSubject');
        $emailBody = $request->getRequiredParam('emailMessage');
        $groupName = $request->getRequiredParam('groupName');
        $sectionId = $request->getRequiredParam('section');
        $groupId = $request->getRequiredParam('id');

        $subscriptionsService = Plugin::getInstance()->groupsService;

        $groupModel = new MailGroupModel;
        $groupModel->id = $groupId;
        $groupModel->sectionId = $sectionId;
        $groupModel->groupName = $groupName;
        $groupModel->emailSubject = $emailSubject;
        $groupModel->emailBody = $emailBody;

        $subscriptionsService->updateMailGroup($groupModel);

        return $this->redirect('mail-subscriptions/groups');
    }

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
}