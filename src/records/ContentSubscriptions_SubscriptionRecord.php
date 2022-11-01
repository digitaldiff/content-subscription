<?php
namespace publishing\contentsubscriptions\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property int $groupId
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property bool $verificationStatus;
 * @property string $hashValue;
 * @property bool $enabled;
 *
 * @property-read \yii\db\ActiveQueryInterface $element
 */
class ContentSubscriptions_SubscriptionRecord extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%contentsubscriptions_subscription}}';
    }
}