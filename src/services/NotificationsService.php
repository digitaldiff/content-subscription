<?php
namespace publishing\contentsubscriptions\services;


use craft\events\ModelEvent;
use craft\helpers\App;
use craft\helpers\UrlHelper;
use craft\mail\Mailer;
use publishing\contentsubscriptions\models\MailGroupModel;
use publishing\contentsubscriptions\models\SubscriptionModel;
use publishing\contentsubscriptions\Plugin;
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
     * @param SubscriptionModel $subscription
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    public  function initiateVerification($hashValue): bool
    {
        $subscription = Plugin::getInstance()->subscriptionsService->getSubscriptionByHash($hashValue);
        if (!$subscription) {
            return false;
        }

        $group = Plugin::getInstance()->groupsService->getMailGroup($subscription->groupId);
        if (!$group) {
            return false;
        }

        $attributes = get_object_vars($subscription);

        unset(
            $attributes['id'],
            $attributes['uid'],
            $attributes['hashValue'],
            $attributes['groupId'],
            $attributes['enabled'],
            $attributes['verificationStatus'],
            $attributes['dateCreated'],
            $attributes['dateUpdated'],
            );

        $body = $group->optInBody;

        foreach ($attributes as $key => $attribute) {
            $body = str_replace('##' . $key . '##', $subscription->$key, $body);
        }

        $url = UrlHelper::actionUrl('content-subscriptions/subscriptions/validate/?hashValue=' . $subscription->hashValue);

        $body = str_replace('##verificationLink##', $url, $body);

        return $this->sendMail(\Craft::$app->getMailer(), $group->optInSubject, $body, $subscription->email);
    }

    /**
     * @param MailGroupModel[] $groups
     * @param SubscriptionModel[] $subscriptions
     * @return void
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    protected function sendNotificationMails(array $groups, array $subscriptions): void {
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
                $attributes['verificationStatus'],
                $attributes['dateCreated'],
                $attributes['dateUpdated'],
            );

            foreach ($attributes as $key => $attribute) {
                $body = str_replace('##' . $key . '##', $subscription->$key, $body);
            }

            if ($group->enableUnsubscribing) {
                $url = UrlHelper::actionUrl('content-subscriptions/subscriptions/unsubscribe/?hashValue=' . $subscription->hashValue);

                $body = str_replace('##unsubscribeLink##', $url, $body);
            } else {
                $body = str_replace('##unsubscribeLink##', '', $body);
            }

            if ($this->sendMail($mailer, $groups[$subscription->groupId]->emailSubject, $body, $subscription->email )) {
                $count++;
            } else {
                $failures++;
            }

        }

        \Craft::$app->getSession()->setNotice(
            \Craft::t(
                'content-subscriptions',
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
            ->setHtmlBody( $body);

        return $message->send();
    }
}