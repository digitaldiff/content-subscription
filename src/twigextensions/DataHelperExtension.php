<?php
namespace publishing\contentsubscriptions\twigextensions;

use Craft;
use publishing\contentsubscriptions\models\SubscriptionModel;
use publishing\contentsubscriptions\Plugin;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DataHelperExtension extends AbstractExtension
{
    /**
     * @return string
     */
/*    public function availableTags()
    {
        $settings = Craft::$app->getPlugins()->getPlugin('content-subscriptions')->getSettings();
        $list = [];
        if ($settings) {
            foreach ($settings->tags as $tag) {
                $list[] = $settings->seperator . $tag . $settings->seperator;
            }
        }

        return implode(', ', $list);
    }*/

/*    public function getSubscriptionForm()
    {
        return (new SubscriptionModel())->getSubscriptionForm();
    }*/
    public function setInstructions(array $tags):string
    {
        $text = Craft::t('content-subscriptions', 'Available tags:');
        $hoverText = Craft::t('content-subscriptions', 'Copy to clipboard');

        foreach ($tags as $tag) {
            $text.= ' <span title="'.$hoverText.'" class="code copytextbtn" onclick="navigator.clipboard.writeText(\'##'. $tag .'##\')">##'. $tag .'##</span>';
        }

        return $text;

    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('setInstructions', [$this, 'setInstructions']),
        ];
    }
}
