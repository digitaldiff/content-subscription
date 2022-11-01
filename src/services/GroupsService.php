<?php
namespace publishing\contentsubscriptions\services;

use craft\elements\conditions\RelatedToConditionRule;
use craft\errors\ElementNotFoundException;
use craft\events\ModelEvent;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use publishing\contentsubscriptions\models\MailGroupModel;
use publishing\contentsubscriptions\models\SubscriptionModel;
use publishing\contentsubscriptions\Plugin;
use publishing\contentsubscriptions\records\ContentSubscriptions_MailGroupRecord;
use publishing\contentsubscriptions\records\ContentSubscriptions_SubscriptionRecord;
use yii\base\Component;
use craft\helpers\App;
use yii\debug\models\search\Mail;

/**
 * DB operations
 *
 * @property-read array $mailGroups
 */
class GroupsService extends Component
{
    public function getMailGroup($id): MailGroupModel|null
    {
        /** @var ContentSubscriptions_MailGroupRecord $groupRecord */
        $groupRecord =  ContentSubscriptions_MailGroupRecord::find()
            ->where(['id' => $id])
            ->one();

        return ($groupRecord) ? $this->mapRecordToModel($groupRecord) : null;
    }

    public function getMailGroups($groupId = 0): array
    {
        $result = [];

        if ($groupId === 0) {
            $groupRecords =  ContentSubscriptions_MailGroupRecord::find()
                ->all();
        } else {
            $groupRecords =  ContentSubscriptions_MailGroupRecord::find()
                ->where(['id' => $groupId])
                ->all();
        }

        foreach ($groupRecords as $groupRecord) {
            $result[$groupRecord->id] = $this->mapRecordToModel($groupRecord);
        }

        return $result;
    }

    public function getEnabledGroupsBySection(int $sectionId): array
    {
        $groups = ContentSubscriptions_MailGroupRecord::findAll([
            'sectionId' => $sectionId,
            'enabled' => true
        ]);

        $list = [];
        foreach ($groups as $group) {
            $list[$group->id] = $this->mapRecordToModel($group);
        }

        return $list;
    }

    /**
     * @param MailGroupModel $mailGroupModel
     * @return bool
     * @throws \Exception
     */
    public function saveMailGroup(MailGroupModel $mailGroupModel): bool
    {
        $groupRecord = new ContentSubscriptions_MailGroupRecord;

        $this->mapModelToRecord($mailGroupModel, $groupRecord);


        $groupRecord->dateCreated = $mailGroupModel->getDateCreated();
        $groupRecord->dateUpdated = $mailGroupModel->getDateUpdated();
        $groupRecord->uid = StringHelper::UUID();

        $groupRecord->save();

        return true;
    }

    /**
     * @param MailGroupModel $mailGroupModel
     * @return bool
     * @throws \Exception
     */
    public function updateMailGroup(MailGroupModel $mailGroupModel): bool
    {
        $groupRecord = ContentSubscriptions_MailGroupRecord::find()
            ->where(['id' => $mailGroupModel->id])
            ->one();

        $this->mapModelToRecord($mailGroupModel, $groupRecord);

        $groupRecord->update();

        return true;
    }

    public function removeGroup($id): bool
    {
        if(\Craft::$app->getUser()->getIdentity()){
            /** @var ContentSubscriptions_MailGroupRecord $record */
            $record = ContentSubscriptions_MailGroupRecord::find()
                ->where(['id' => $id])
                ->one()
                ->delete();

            return true;
        }
        return false;
    }

    /**
     * @param MailGroupModel $mailGroupModel
     * @param array|\yii\db\ActiveRecord|null $groupRecord
     * @return void
     */
    protected function mapModelToRecord(MailGroupModel $mailGroupModel, array|\yii\db\ActiveRecord|null $groupRecord): void
    {
        $groupRecord->sectionId = $mailGroupModel->sectionId;
        $groupRecord->groupName = $mailGroupModel->groupName;
        $groupRecord->emailSubject = $mailGroupModel->emailSubject;
        $groupRecord->emailBody = $mailGroupModel->emailBody;
        $groupRecord->optInSubject = $mailGroupModel->optInSubject;
        $groupRecord->optInBody = $mailGroupModel->optInBody;
        $groupRecord->enableUnsubscribing = $mailGroupModel->enableUnsubscribing;
        $groupRecord->unsubscribeMessage = $mailGroupModel->unsubscribeMessage;
        $groupRecord->enabled = $mailGroupModel->enabled;
    }

    /**
     * @param ContentSubscriptions_MailGroupRecord $record
     * @return MailGroupModel
     */
    protected function mapRecordToModel(ContentSubscriptions_MailGroupRecord $record): MailGroupModel
    {
        $groupModel = new MailGroupModel();
        $groupModel->id = $record->id;
        $groupModel->sectionId = $record->sectionId;
        $groupModel->groupName = $record->groupName;
        $groupModel->emailSubject = $record->emailSubject;
        $groupModel->optInSubject = $record->optInSubject;
        $groupModel->emailBody = $record->emailBody;
        $groupModel->optInBody = $record->optInBody;
        $groupModel->enableUnsubscribing = $record->enableUnsubscribing;
        $groupModel->unsubscribeMessage = $record->unsubscribeMessage;
        $groupModel->enabled = $record->enabled;

        return $groupModel;
    }
}