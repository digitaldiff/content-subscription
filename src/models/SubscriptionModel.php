<?php
namespace publishing\mailsubscriptions\models;

use craft\base\Model;

class SubscriptionModel extends Model
{
    public int $id;
    public int $groupId;
    public string $firstName;
    public string $lastName;
    public string $email;
    public \DateTime $dateDeleted;
    public \DateTime $dateCreated;
    public \DateTime $dateUpdated;
    public string $uid;
}