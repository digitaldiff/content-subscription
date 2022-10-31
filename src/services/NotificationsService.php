<?php
namespace publishing\mailsubscriptions\services;


use craft\events\ModelEvent;
use craft\helpers\App;
use craft\mail\Mailer;
use publishing\mailsubscriptions\models\MailGroupModel;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use yii\base\Component;


class NotificationsService extends Component
{
    public function notificationEvent($event)
    {
        $sectionId = $event->sender->sectionId;
        /** @var MailGroupModel[] $groups */
        $groups = Plugin::getInstance()->groupsService->getEnabledGroupsBySection($sectionId);


        /** @var SubscriptionModel[] $subscriptions */
        $subscriptions = Plugin::getInstance()->subscriptionsService->getSubscriptionsForMailNotifications(array_keys($groups));

        $this->sendNotificationMails($groups, $subscriptions);

        //$subscriptions = Plugin::getInstance()->subscriptionsService->getSubscriptions($event->sender->sectionId);
    }

    /**
     * @param MailGroupModel[] $groups
     * @param SubscriptionModel[] $subscriptions
     * @return void
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function sendNotificationMails(array $groups, array $subscriptions): void {
        $mailer = \Craft::$app->getMailer();
        $count = 0;
        $failures = 0;


        foreach ($subscriptions as $subscription) {
            $group = $groups[$subscription->groupId];
            $body = $group->emailBody;


            $attributes = get_object_vars($subscription);

            unset(
                $attributes['id'],
                $attributes['uid'],
                $attributes['hashValue'],
                $attributes['groupId'],
                $attributes['enabled'],
                $attributes['verificationStatus']
            );

            foreach ($attributes as $key => $attribute) {
                $body = str_replace('##' . $key . '##', $subscription->$key, $body);
            }

            if ($this->sendMail($mailer, $groups[$subscription->groupId]->emailSubject, $body, $subscription->email )) {
                $count++;
            } else {
                $failures++;
            }

        }

        \Craft::$app->getSession()->setNotice(
            \Craft::t(
                'mail-subscriptions',
                '{success} subscribers successfully notified, {failures} failures',
                [
                    'success' => $count,
                    'failures' => $failures
                ]
            )
        );
    }

    /**
     * @param Mailer $mailer
     * @param string $subject
     * @param string $body
     * @param string $to
     * @return mixed
     */
    protected function sendMail(Mailer $mailer, string $subject, string $body, string $to): bool
    {
        $message = $mailer->compose()
            ->setFrom(App::env('EMAIL_SYSTEM'))
            ->setTo($to)
            ->setSubject($subject)
            ->setHtmlBody($body);

        return $message->send();
    }




    // E-Mail Notification
    // TODO not compatible with new design (sectionId is no longer equal with groupId)
    /**
     * @param ModelEvent $event
     * @return void
     *
     */
    public function old_notificationEvent($event) {
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

        $template = $this->getMailGroups($sectionId)[$sectionId];

        $this->sendMail($template[0], $subscriptions, $event->sender);
    }

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