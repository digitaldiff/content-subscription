<?php
namespace publishing\mailsubscriptions\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property int $sectionId
 * @property string $groupName
 * @property string $emailSubject
 * @property string $emailBody
 * @property bool $enableUnsubscribing
 * @property bool $enabled
 *
 * @property-read \yii\db\ActiveQueryInterface $element
 */
class ContentSubscriptions_MailGroupRecord extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%contentsubscriptions_mailgroup}}';
    }
}