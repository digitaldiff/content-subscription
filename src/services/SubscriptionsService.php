<?php
namespace publishing\mailsubscriptions\services;

use craft\events\ModelEvent;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use publishing\mailsubscriptions\models\MailGroupModel;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use publishing\mailsubscriptions\records\MailSubscriptions_MailGroupRecord;
use publishing\mailsubscriptions\records\MailSubscriptions_SubscriptionRecord;
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
     * @param int $groupId
     * @return array
     *
     * If GroupId is not set, this method returns all subscriptions.
     * Otherwise, only the subscriptions with the corresponding GroupId are returned.
     */
    /*public function getSubscriptions(int $groupId = 0): array
    {
        $result = [];
        if ($groupId === 0) {
            $subscriptions =  MailSubscriptions_SubscriptionRecord::find()->all();
        } else {
            $subscriptions =  MailSubscriptions_SubscriptionRecord::find()->where(['groupId' => $groupId])->all();
        }

        foreach ($subscriptions as $subscription) {
            $subscriptionModel = new SubscriptionModel();
            $subscriptionModel->id = $subscription->id;
            $subscriptionModel->groupId = $subscription->groupId;
            $subscriptionModel->firstName = $subscription->firstName;
            $subscriptionModel->lastName = $subscription->lastName;
            $subscriptionModel->email = $subscription->email;

            $result[$subscription->groupId][] = $subscriptionModel;
        }

        return $result;
    }*/


    /**
     * @return array
     */
    public function getSubscriptions(): array
    {
        $result = [];
        $subscriptions =  MailSubscriptions_SubscriptionRecord::find()->all();

        foreach ($subscriptions as $subscription) {
            $subscriptionModel = new SubscriptionModel();
            $subscriptionModel->id = $subscription->id;
            $subscriptionModel->groupId = $subscription->groupId;
            $subscriptionModel->firstName = $subscription->firstName;
            $subscriptionModel->lastName = $subscription->lastName;
            $subscriptionModel->email = $subscription->email;

            $result[] = $subscriptionModel;
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
        $record = new MailSubscriptions_SubscriptionRecord();

        $isNewTemplate = !$model->id;

        // Make sure it's got a UUID
        if ($isNewTemplate) {
            if (empty($this->uid)) {
                $model->uid = StringHelper::UUID();
            }
        } else if (!$model->uid) {
            $model->uid = Db::uidById(MailSubscriptions_SubscriptionRecord::tableName(), $model->id);
        }

        $record->id = $model->id;
        $record->groupId = $model->groupId;
        $record->firstName = $model->firstName;
        $record->lastName = $model->lastName;
        $record->email = $model->email;
        $record->dateCreated = $model->dateCreated ?? new \DateTime('now');
        $record->dateUpdated = $model->dateUpdated ?? new \DateTime('now');
        $record->uid = $model->uid;

        $record->save();

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
            /** @var MailSubscriptions_SubscriptionRecord $record */
            $record = MailSubscriptions_SubscriptionRecord::find()->where(['id' => $id])->one();
            $record->softDelete();
            return true;
        }
        return false;
    }

    // E-Mail Notification
    // TODO not compatible with new design (sectionId is no longer equal with groupId)
    /**
     * @param ModelEvent $event
     * @return void
     */
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