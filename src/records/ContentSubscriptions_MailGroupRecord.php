<?php
namespace publishing\mailsubscriptions\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property int $sectionId
 * @property string $groupName
 * @property string $emailSubject
 * @property string $optInSubject
 * @property string $emailBody
 * @property string $optInBody
 * @property bool $enableUnsubscribing
 * @property string $unsubscribeMessage
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