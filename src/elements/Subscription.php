<?php
namespace publishing\mailsubscriptions\elements;

use craft\base\Element;
use publishing\mailsubscriptions\elements\db\SubscribtionQuery;

class Subscription extends Element
{
    public static function displayName(): string
    {
        return \Craft::t('mail-subscriptions', 'Subscription');
    }

    /**
     * @inheritDoc
     */
    public static function refHandle(): ?string
    {
        return 'subscription';
    }

    /**
     * @inheritDoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    public static function find(): SubscribtionQuery
    {
        return new SubscribtionQuery(static::class);
    }
}