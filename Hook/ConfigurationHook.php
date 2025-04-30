<?php

namespace Doofinder\Hook;

use Doofinder\Doofinder;
use Doofinder\Service\ApiDoofinderManagementService;
use Doofinder\Shared\Exceptions\ApiException;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Log\Tlog;

class ConfigurationHook extends BaseHook
{
    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $indices = [];
        $searchEngine = [];

        try {
            $searchEngine = ApiDoofinderManagementService::getSearchEngine();

            foreach ($searchEngine['indices'] as $indice) {
                foreach ($indice['datasources'] as $datasource) {
                    $indices[$indice['name']][] = $datasource['options']['url'];
                }
            }
        } catch (ApiException $e) {
            Tlog::getInstance()->error($e->getMessage());
        }

        $event->add(
            $this->render(
                "module_configuration.html",
                [
                    'feeds' => $indices,
                    "search_engine" => $searchEngine['name'] ?? '',
                    "search_engine_lang" => $searchEngine['language'] ?? '',
                    "search_engine_server" => Doofinder::getConfigValue(Doofinder::DOOFINDER_SEARCH_ZONE_CONFIG_KEY),
                    "search_engine_hash_id" => Doofinder::getConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY),
                    "search_engine_currency" => $searchEngine['currency'] ?? '',
                    "search_engine_status" => !($searchEngine['inactive'] ?? false)
                ]
            )
        );
    }

    public static function getSubscribedHooks(): array
    {
        return [
            "module.configuration" => [
                [
                    "type" => "back",
                    "method" => "onModuleConfiguration"
                ],
            ]
        ];
    }
}
