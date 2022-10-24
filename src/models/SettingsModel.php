<?php
namespace publishing\mailsubscriptions\models;

use craft\base\Model;

class SettingsModel extends Model
{
    /*public bool $pluginEnabled = true;*/

    public string $separator = '##';
    public array $tags = ['name', 'email'];
}