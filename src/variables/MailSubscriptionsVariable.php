<?php
namespace publishing\mailsubscriptions\variables;

use publishing\mailsubscriptions\Plugin;

class MailSubscriptionsVariable
{
    public function getMailGroups($id = 0)
    {
        return Plugin::getInstance()->groupsService->getMailGroups($id);
    }

    public function getSubscriptions($id = 0): array
    {
        return Plugin::getInstance()->subscriptionsService->getSubscriptions($id);
    }

    public function removeGroup($id): bool
    {
        return Plugin::getInstance()->groupsService->removeGroup($id);
    }
}