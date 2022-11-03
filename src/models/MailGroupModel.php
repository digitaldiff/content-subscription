<?php

namespace publishing\contentsubscriptions\models;

use craft\base\Model;

class MailGroupModel extends Model
{
    public int $id = 0;
    public int $sectionId = 0;
    public string $groupName = '';
    public string $emailSubject;
    public string $emailBody;
    public string $optInSubject;
    public string $optInBody;
    public bool $enableUnsubscribing = true;
    public string $unsubscribeMessage;
    public bool $enabled = true;
    public \DateTime $dateCreated;
    public \DateTime $dateUpdated;
    public string $uid;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->emailSubject =  \Craft::t('content-subscriptions','New content has been released!');
        $this->emailBody =  \Craft::t('content-subscriptions',
'Hello ##firstName##
            
We just released new content, come check it out.
##entryLink##

If you no longer wish to receive notifications about this type of content, use following link to unsubscribe: 
##unsubscribeLink##');

        $this->optInSubject = 'E-Mail verification';
        $this->optInBody =
            'Hello ##firstName##
            
Use following link to activate your subscription. 
##verificationLink##

If you no longer wish to subscribe, you can just ignore this message.';
        $this->unsubscribeMessage = 'You\'ve successfully unsubscribe from this topic.';
    }

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
}