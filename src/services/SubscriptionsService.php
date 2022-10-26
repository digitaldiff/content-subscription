<?php
namespace publishing\mailsubscriptions\services;

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

/**
 * DB operations
 *
 * @property-read array $mailGroups
 */
class SubscriptionsService extends Component
{
    /**
     * @param int $id
     * @return SubscriptionModel
     */
    public function getSubscription(int $id): SubscriptionModel
    {
        /** @var ContentSubscriptions_SubscriptionRecord $record */
        $record = ContentSubscriptions_SubscriptionRecord::find()->where(['id' => $id])->one();
        return $this->mapRecordToModel($record);
    }


    /**
     * @return array
     */
    public function getSubscriptions(): array
    {
        $result = [];
        $subscriptions =  ContentSubscriptions_SubscriptionRecord::find()->all();

        foreach ($subscriptions as $subscription) {
            $result[] = $this->mapRecordToModel($subscription);
        }

        return $result;

    }

    /**
     * @param SubscriptionModel $model
     * @return bool
     * @throws \Exception
     */
    public function saveSubscription(SubscriptionModel $model): bool
    {
        $record = new ContentSubscriptions_SubscriptionRecord();

        $isNewTemplate = !$model->id;

        // Make sure it's got a UUID
        if ($isNewTemplate) {
            if (empty($this->uid)) {
                $model->uid = StringHelper::UUID();
            }
        } else if (!$model->uid) {
            $model->uid = Db::uidById(ContentSubscriptions_SubscriptionRecord::tableName(), $model->id);
        }
        $model->generateHash();

        $record->id = $model->id;
        $record->hashValue = $model->hashValue;
        $record->dateCreated = $model->getDateCreated();
        $record->dateUpdated = $model->dateUpdated ?? new \DateTime('now');
        $record->uid = $model->uid;

        $this->mapModelToRecord($model, $record);

        $record->save();

        return true;
    }

    /**
     * @param SubscriptionModel $subscriptionModel
     * @return bool
     * @throws \yii\db\StaleObjectException
     */
    public function updateSubscription(SubscriptionModel $subscriptionModel): bool
    {
        /** @var ContentSubscriptions_SubscriptionRecord $subscriptionRecord */
        $subscriptionRecord = ContentSubscriptions_SubscriptionRecord::find()->where(['id' => $subscriptionModel->id])->one();

        $this->mapModelToRecord($subscriptionModel, $subscriptionRecord);

        $subscriptionRecord->update();

        return true;
    }

    /**
     * @param $id
     * @return bool
     * @throws \Throwable
     */
    public function removeSubscription($id): bool
    {
        if(\Craft::$app->getUser()->getIdentity()){
            /** @var ContentSubscriptions_SubscriptionRecord $record */
            $record = ContentSubscriptions_SubscriptionRecord::find()->where(['id' => $id])->one();
            $record->delete();
            return true;
        }
        return false;
    }

    /**
     * @param ContentSubscriptions_SubscriptionRecord $record
     * @return SubscriptionModel
     */
    protected function mapRecordToModel(ContentSubscriptions_SubscriptionRecord $record): SubscriptionModel
    {
        $subscriptionModel = new SubscriptionModel();
        $subscriptionModel->id = $record->id;
        $subscriptionModel->groupId = $record->groupId;
        $subscriptionModel->firstName = $record->firstName;
        $subscriptionModel->lastName = $record->lastName;
        $subscriptionModel->email = $record->email;
        $subscriptionModel->verificationStatus = $record->verificationStatus;
        $subscriptionModel->hashValue = $record->hashValue;
        $subscriptionModel->enabled = $record->enabled;

        return $subscriptionModel;
    }

    /**
     * @param SubscriptionModel $subscriptionModel
     * @param ContentSubscriptions_SubscriptionRecord $subscriptionRecord
     * @return void
     */
    protected function mapModelToRecord(SubscriptionModel $subscriptionModel, ContentSubscriptions_SubscriptionRecord $subscriptionRecord): void
    {
        $subscriptionRecord->groupId = $subscriptionModel->groupId;
        $subscriptionRecord->firstName = $subscriptionModel->firstName;
        $subscriptionRecord->lastName = $subscriptionModel->lastName;
        $subscriptionRecord->email = $subscriptionModel->email;
        $subscriptionRecord->verificationStatus = $subscriptionModel->verificationStatus;
        $subscriptionRecord->enabled = $subscriptionModel->enabled;
    }
    /**
     * @param ModelEvent $event
     * @return void
     */



    // E-Mail Notification
    // TODO not compatible with new design (sectionId is no longer equal with groupId)
/*    public function notificationEvent($event) {
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
/*    private function sendMail($mailTemplate, $subscriptions, $sender)
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