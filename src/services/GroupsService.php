<?php
namespace publishing\mailsubscriptions\services;

use craft\elements\conditions\RelatedToConditionRule;
use craft\errors\ElementNotFoundException;
use craft\events\ModelEvent;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use publishing\mailsubscriptions\models\MailGroupModel;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use publishing\mailsubscriptions\records\ContentSubscriptions_MailGroupRecord;
use publishing\mailsubscriptions\records\ContentSubscriptions_SubscriptionRecord;
use yii\base\Component;
use craft\helpers\App;
use yii\debug\models\search\Mail;

/**
 * DB operations
 *
 * @property-read array $mailGroups
 */
class GroupsService extends Component
{
    public function getMailGroup($id): MailGroupModel|null
    {
        /** @var ContentSubscriptions_MailGroupRecord $groupRecord */
        $groupRecord =  ContentSubscriptions_MailGroupRecord::find()->where(['id' => $id])->one();

        return ($groupRecord) ? $this->mapRecordToModel($groupRecord) : null;
    }

    public function getMailGroups($groupId = 0): array
    {
        $result = [];

        if ($groupId === 0) {
            $groupRecords =  ContentSubscriptions_MailGroupRecord::find()->all();
        } else {
            $groupRecords =  ContentSubscriptions_MailGroupRecord::find()->where(['id' => $groupId])->all();
        }

        foreach ($groupRecords as $groupRecord) {
            $result[$groupRecord->id] = $this->mapRecordToModel($groupRecord);
        }

        return $result;
    }

    /**
     * @param MailGroupModel $mailGroupModel
     * @return bool
     * @throws \Exception
     */
    public function saveMailGroup(MailGroupModel $mailGroupModel): bool
    {
        $groupRecord = new ContentSubscriptions_MailGroupRecord;

        $this->mapModelToRecord($mailGroupModel, $groupRecord);


        $groupRecord->dateCreated = $mailGroupModel->getDateCreated();
        $groupRecord->dateUpdated = $mailGroupModel->getDateUpdated();
        $groupRecord->uid = StringHelper::UUID();

        $groupRecord->save();

        return true;
    }

    /**
     * @param MailGroupModel $mailGroupModel
     * @return bool
     * @throws \Exception
     */
    public function updateMailGroup(MailGroupModel $mailGroupModel): bool
    {
        $groupRecord = ContentSubscriptions_MailGroupRecord::find()->where(['id' => $mailGroupModel->id])->one();

        $this->mapModelToRecord($mailGroupModel, $groupRecord);

        $groupRecord->update();

        return true;
    }

    public function removeGroup($id): bool
    {
        if(\Craft::$app->getUser()->getIdentity()){
            /** @var ContentSubscriptions_MailGroupRecord $record */
            $record = ContentSubscriptions_MailGroupRecord::find()->where(['id' => $id])->one();
            $record->softDelete();
            return true;
        }
        return false;
    }

    /**
     * @param MailGroupModel $mailGroupModel
     * @param array|\yii\db\ActiveRecord|null $groupRecord
     * @return void
     */
    protected function mapModelToRecord(MailGroupModel $mailGroupModel, array|\yii\db\ActiveRecord|null $groupRecord): void
    {
        $groupRecord->sectionId = $mailGroupModel->sectionId;
        $groupRecord->groupName = $mailGroupModel->groupName;
        $groupRecord->emailSubject = $mailGroupModel->emailSubject;
        $groupRecord->emailBody = $mailGroupModel->emailBody;
        $groupRecord->optInSubject = $mailGroupModel->optInSubject;
        $groupRecord->optInBody = $mailGroupModel->optInBody;
        $groupRecord->enableUnsubscribing = $mailGroupModel->enableUnsubscribing;
        $groupRecord->unsubscribeMessage = $mailGroupModel->unsubscribeMessage;
        $groupRecord->enabled = $mailGroupModel->enabled;
    }

    /**
     * @param ContentSubscriptions_MailGroupRecord $record
     * @return MailGroupModel
     */
    protected function mapRecordToModel(ContentSubscriptions_MailGroupRecord $record): MailGroupModel
    {
        $groupModel = new MailGroupModel();
        $groupModel->id = $record->id;
        $groupModel->sectionId = $record->sectionId;
        $groupModel->groupName = $record->groupName;
        $groupModel->emailSubject = $record->emailSubject;
        $groupModel->optInSubject = $record->optInSubject;
        $groupModel->emailBody = $record->emailBody;
        $groupModel->optInBody = $record->optInBody;
        $groupModel->enableUnsubscribing = $record->enableUnsubscribing;
        $groupModel->unsubscribeMessage = $record->unsubscribeMessage;
        $groupModel->enabled = $record->enabled;

        return $groupModel;
    }

    // E-Mail Notification
    // TODO not compatible with new design (sectionId is no longer equal with groupId)
    /*public function notificationEvent($event) {
        $sectionId = $event->sender->sectionId;
        $subscriptions = $this->getSubscriptions($sectionId)[$sectionId];


        //Debug Notice
        /*$mailList = [];
        foreach ($subscriptions[$sectionId] as $sub) {
            $mailList[] = $sub->mail;
        }
        if ($subscriptions) {
            \Craft::$app->getSession()->setNotice('Send Mail to '. implode(', ', $mailList));
        }*/

        /*$template = $this->getMailGroups($sectionId)[$sectionId];

        $this->sendMail($template[0], $subscriptions, $event->sender);
    }*/

    //TODO finalize as soon as the tag-implementation is finalized
    /**
     * @param $mailTemplate
     * @param $subscriptions
     * @param $sender
     * @return void
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    /*private function sendMail($mailTemplate, $subscriptions, $sender)
    {
        //Get tags from settings
        //$settings = Plugin::getInstance()->getSettings();
        $tags = ['##lastname##', '##firstname##', '##email##'];
        //dd($_POST['abc']);
        //Populate template
        $mailTemplate = str_replace(array('##EMAIL##', '##TITLE##'), array($subscriptions[0]->mail, $sender->title), $mailTemplate->template);

        //Send template

        $subject = 'Neuer Beitrag veröffentlicht';
        $message = $mailTemplate;
        $receiver = $subscriptions[0]->mail;

        $mailer = \Craft::$app->getMailer();

        $message = $mailer->compose()
            ->setFrom(App::env('EMAIL_SYSTEM'))
            ->setTo($receiver)
            ->setSubject($subject)
            ->setHtmlBody($message);

        $success = $message->send();

        if ($success) {
            \Craft::$app->getSession()->setNotice('Abonnenten informiert: XX Personen');
        }
        else {
            \Craft::$app->getSession()->setError('Abonnenten konnten nicht erreicht werden.');
        }
    }*/

    // I think those aren't needed anymore

    /*public function getSingleTemplate($groupId): string
{
    return MailSubscriptions_MailGroupRecord::find()->where(['groupId' => $groupId])->one()->template ?? '';
}*/

}