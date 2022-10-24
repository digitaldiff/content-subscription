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
        $emailBody = $request->getRequiredParam('emailBody');

        //TODO set values
        $groupName = '';
        $emailSubject = '';

        $subscriptionsService = Plugin::getInstance()->groupsService;

        $groupModel = new MailGroupModel;
        $groupModel->id = 0;
        $groupModel->sectionId = 1;
        $groupModel->groupName = $groupName;
        $groupModel->emailSubject = $emailSubject;
        $groupModel->emailBody = $emailBody;

        $subscriptionsService->saveMailGroup($groupModel);
    }

    public function actionCreateMailGroup(): Response
    {
        $request = \Craft::$app->getRequest();
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
                return $this->asSuccess('mail-subscriptions/groups');
            }
        }

        return $this->asFailure('mail-subscriptions/groups');
    }
}