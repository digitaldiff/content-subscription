<?php
namespace publishing\mailsubscriptions\variables;

use publishing\mailsubscriptions\models\MailGroupModel;
use publishing\mailsubscriptions\Plugin;

class MailSubscriptionsVariable
{
    public function getMailGroups($id = 0)
    {
        return Plugin::getInstance()->groupsService->getMailGroups($id);
    }

    public function getSubscriptions(): array
    {
        return Plugin::getInstance()->subscriptionsService->getSubscriptions();
    }

    public function removeGroup($id): bool
    {
        return Plugin::getInstance()->groupsService->removeGroup($id);
    }

    public function getSections(): array
    {
        $sections = \Craft::$app->getSections()->getAllSections();
        $result = [];
        foreach ($sections as $section) {
             $result[$section->id] = $section->name;
        }
        return $result;
    }

    public function getMailGroupLabels()
    {
        $result = [];
        /** @var MailGroupModel[] $groups */
        $groups = $this->getMailGroups();

        foreach ($groups as $group) {
            $result[$group->id] = $group->groupName;
        }
        return $result;

    }
}