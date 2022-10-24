<?php
namespace publishing\mailsubscriptions\migrations;

use Craft;
use craft\db\Migration;
use publishing\mailsubscriptions\records\MailSubscriptions_MailGroupRecord;
use publishing\mailsubscriptions\records\MailSubscriptions_SubscriptionRecord;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTables();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->removeTables();

        return true;
    }

    protected function createTables(): void
    {
        $this->archiveTableIfExists(MailSubscriptions_MailGroupRecord::tableName());
        $this->createTable(MailSubscriptions_MailGroupRecord::tableName(), [
            'id' => $this->primaryKey(),
            'sectionId' => $this->integer()->notNull(),
            'groupName' => $this->string()->notNull(),
            'emailSubject' => $this->string()->notNull(),
            'emailBody' => $this->string()->notNull(),
            'dateDeleted' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->archiveTableIfExists(MailSubscriptions_SubscriptionRecord::tableName());
        $this->createTable(MailSubscriptions_SubscriptionRecord::tableName(), [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'firstName' => $this->string()->notNull(),
            'lastName' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'dateDeleted' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    protected function removeTables()
    {
        $tables = [
            MailSubscriptions_MailGroupRecord::tableName(),
            MailSubscriptions_SubscriptionRecord::tableName()
        ];
        foreach ($tables as $table) {
            $this->dropTableIfExists($table);
        }
    }
}
