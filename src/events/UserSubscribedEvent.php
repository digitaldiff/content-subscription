<?php
namespace publishing\mailsubscriptions\events;

use publishing\mailsubscriptions\models\SubscriptionModel;
use yii\base\Event;

class UserSubscribedEvent extends Event
{
    public ?SubscriptionModel $subscriptionModel = null;
}