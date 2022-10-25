<?php
namespace publishing\mailsubscriptions\elements;

use craft\base\Element;
use publishing\mailsubscriptions\elements\db\SubscriptionQuery;

class Subscription extends Element
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return \Craft::t('mail-subscriptions', 'Subscription');
    }

    public static function find(): SubscriptionQuery
    {
        return new SubscriptionQuery(static::class);
    }
}