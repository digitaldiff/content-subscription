<?php
namespace publishing\mailsubscriptions\twigextensions;

use Craft;
use publishing\mailsubscriptions\models\SubscriptionModel;
use publishing\mailsubscriptions\Plugin;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DataHelperExtension extends AbstractExtension
{
    /**
     * @return string
     */
    public function availableTags()
    {
        $settings = Craft::$app->getPlugins()->getPlugin('mail-subscriptions')->getSettings();
        $list = [];
        if ($settings) {
            foreach ($settings->tags as $tag) {
                $list[] = $settings->seperator . $tag . $settings->seperator;
            }
        }

        return implode(', ', $list);
    }

/*    public function getSubscriptionForm()
    {
        return (new SubscriptionModel())->getSubscriptionForm();
    }*/

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('availableTags', [$this, 'availableTags']),
            new TwigFunction('getSubscriptionForm', [$this, 'getSubscriptionForm']),
        ];
    }
}
