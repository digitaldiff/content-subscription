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
    public string $optInSubject;
    public string $optInBody;
    public bool $enableUnsubscribing;
    public string $unsubscribeMessage;
    public bool $enabled;
    public \DateTime $dateCreated;
    public \DateTime $dateUpdated;
    public string $uid;

    public function getDateCreated()
    {
        if (!isset($this->dateCreated)) {
            $this->dateCreated = new \DateTime('now');
        }
        return $this->dateCreated;
    }

    public function getDateUpdated()
    {
        if (!isset($this->dateUpdated)) {
            $this->dateUpdated = new \DateTime('now');
        }
        return $this->dateUpdated;
    }

    public function getTags()
    {
        return ['##firstname##', '##lastname##', '##email##'];
    }
}