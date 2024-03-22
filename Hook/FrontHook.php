<?php

namespace Doofinder\Hook;

use Doofinder\Doofinder;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class FrontHook extends BaseHook
{
    public function addDoofinderSearchScript(HookRenderEvent $event): void
    {
        $event->add(
            $this->render("hooks/hook-search-script.html",
                [
                    'query_input_id' => Doofinder::getConfigValue(Doofinder::DOOFINDER_QUERY_INPUT_ID_CONFIG_KEY),
                    'hash_id' => Doofinder::getConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY),
                    'search_zone' => Doofinder::getConfigValue(Doofinder::DOOFINDER_SEARCH_ZONE_CONFIG_KEY),
                    'lang' => $this->getLang(),
                    'basic_input' => Doofinder::getConfigValue(Doofinder::DOOFINDER_BASIC_SEARCH_BAR_CONFIG_KEY)
                ]
            )
        );
    }

    public static function getSubscribedHooks(): array
    {
        return [
            Doofinder::getConfigValue(Doofinder::DOOFINDER_HOOK_SEARCH_SCRIPT_CONFIG_KEY) => [
                [
                    "type" => "front",
                    "method" => "addDoofinderSearchScript"
                ],
            ]
        ];
    }
}