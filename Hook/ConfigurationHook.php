<?php

namespace Doofinder\Hook;

use Doofinder\Doofinder;
use Doofinder\Form\ConfigurationForm;
use Doofinder\Form\FrontHooksForm;
use Doofinder\Service\ApiDoofinderManagementService;
use Doofinder\Shared\Exceptions\ApiException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Log\Tlog;
use Thelia\Model\ProductQuery;

class ConfigurationHook extends BaseHook
{
    public function __construct(
        private readonly TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

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

        $configurationForm = $this->formFactory->createForm(ConfigurationForm::getName());
        $frontHooksForm = $this->formFactory->createForm(FrontHooksForm::getName());

        $event->add(
            $this->render(
                "Doofinder/module_configuration.html.twig",
                [
                    'configuration_form' => $configurationForm->createView()->getView(),
                    'front_hooks_form' => $frontHooksForm->createView()->getView(),
                    'excluded_products' => $this->getExcludedProducts(),
                    'feeds' => $indices,
                    "search_engine" => $searchEngine['name'] ?? '',
                    "search_engine_lang" => $searchEngine['language'] ?? '',
                    "search_engine_server" => Doofinder::getConfigValue(Doofinder::DOOFINDER_SEARCH_ZONE_CONFIG_KEY),
                    "search_engine_hash_id" => Doofinder::getConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY),
                    "search_engine_currency" => $searchEngine['currency'] ?? '',
                    "search_engine_status" => !($searchEngine['inactive'] ?? false),
                ]
            )
        );
    }

    /**
     * Reproduces the {loop type="product" is_excluded=true} from the legacy Smarty template:
     * products that have a matching DoofinderExcludedProduct row.
     *
     * @return array<int, array{id: int, title: string, ref: string}>
     */
    private function getExcludedProducts(): array
    {
        $request = $this->getRequest();
        $locale = $request?->getSession()?->getLang()?->getLocale() ?? 'en_US';

        $products = ProductQuery::create()
            ->useDoofinderExcludedProductExistsQuery()
            ->endUse()
            ->find();

        $result = [];
        foreach ($products as $product) {
            $product->setLocale($locale);
            $result[] = [
                'id' => $product->getId(),
                'title' => (string) $product->getTitle(),
                'ref' => (string) $product->getRef(),
            ];
        }

        return $result;
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
