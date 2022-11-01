<?php
namespace publishing\contentsubscriptions\migrations;

use Craft;
use craft\db\Migration;
use publishing\contentsubscriptions\records\ContentSubscriptions_MailGroupRecord;
use publishing\contentsubscriptions\records\ContentSubscriptions_SubscriptionRecord;

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
        $this->createIndexes();
        $this->addForeignKeys();
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

        $this->archiveTableIfExists(ContentSubscriptions_MailGroupRecord::tableName());
        $this->createTable(ContentSubscriptions_MailGroupRecord::tableName(), [
            'id' => $this->primaryKey(),
            'sectionId' => $this->integer()->notNull(),
            'groupName' => $this->string()->notNull(),
            'emailSubject' => $this->string()->notNull(),
            'emailBody' => $this->string()->notNull(),
            'optInSubject' => $this->string()->notNull(),
            'optInBody' => $this->string()->notNull(),
            'enableUnsubscribing' => $this->boolean()->notNull()->defaultValue(true),
            'unsubscribeMessage' => $this->string()->notNull(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->archiveTableIfExists(ContentSubscriptions_SubscriptionRecord::tableName());
        $this->createTable(ContentSubscriptions_SubscriptionRecord::tableName(), [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'firstName' => $this->string()->notNull(),
            'lastName' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'verificationStatus' => $this->boolean()->notNull()->defaultValue(false),
            'hashValue' => $this->string()->notNull(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }


    public function createIndexes(): void
    {
        $this->createIndex(null, ContentSubscriptions_SubscriptionRecord::tableName(), 'groupId', false);
    }

    protected function addForeignKeys(): void
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName(),
            ContentSubscriptions_SubscriptionRecord::tableName(),
            'id',
            '{{%elements}}',
            'id',
            'CASCADE'
        );
    }

    protected function removeTables()
    {
        $tables = [
            ContentSubscriptions_MailGroupRecord::tableName(),
            ContentSubscriptions_SubscriptionRecord::tableName()
        ];
        foreach ($tables as $table) {
            $this->dropTableIfExists($table);
        }
    }
}
