<?php
namespace publishing\mailsubscriptions\records;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

/**
 * @property int $id
 * @property int $groupId
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 *
 * @property-read \yii\db\ActiveQueryInterface $element
 */
class MailSubscriptions_SubscriptionRecord extends ActiveRecord
{
    use SoftDeleteTrait;

    public static function tableName()
    {
        return '{{%mailsubscriptions_subscription}}';
    }
}