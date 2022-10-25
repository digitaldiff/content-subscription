<?php
namespace publishing\mailsubscriptions\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SubscriptionQuery extends ElementQuery
{
    protected function beforePrepare(): bool
    {
        // See if 'group' was set to an invalid handle
        $this->joinElementTable('mailsubscriptions_subscription');

        $this->query->select([
            'mailsubscriptions_subscription.email',
        ]);

        return parent::beforePrepare();
    }
}