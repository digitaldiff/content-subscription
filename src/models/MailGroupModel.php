<?php

namespace publishing\mailsubscriptions\models;

use craft\base\Model;

class MailGroupModel extends Model
{
    public int $id;
    public int $sectionId;
    public string $groupName;
    public string $emailSubject;
    public string $emailBody;
    public \DateTime $dateDeleted;
    public \DateTime $dateCreated;
    public \DateTime $dateUpdated;
    public string $uid;
}