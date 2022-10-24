<?php
namespace publishing\mailsubscriptions\records;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\gql\types\DateTime;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * @property int $id
 * @property int $sectionId
 * @property string $groupName
 * @property string $emailSubject
 * @property string $emailBody
 *
 * @property-read \yii\db\ActiveQueryInterface $element
 */
class MailSubscriptions_MailGroupRecord extends ActiveRecord
{
    use SoftDeleteTrait;

    public static function tableName()
    {
        return '{{%mailsubscriptions_mailgroup}}';
    }
}