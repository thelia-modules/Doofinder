<?php

namespace Doofinder\Service;

use Doofinder\Doofinder;
use Doofinder\Event\DoofinderItemParamEvent;
use Doofinder\Event\DoofinderItemParamEvents;
use Doofinder\Management\ManagementClient;
use Doofinder\Search\SearchClient;
use Doofinder\Shared\Exceptions\ApiException;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;

class ApiDoofinderManagementService
{
    private ManagementClient $managementClient;
    private SearchClient $searchClient;

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected DoofinderFormatService $formatService
    ) {
        $host = sprintf(
            Doofinder::DOOFINDER_URL,
            Doofinder::getConfigValue(Doofinder::DOOFINDER_SEARCH_ZONE_CONFIG_KEY) ?? "eu1",
        );
        $hostSearch = sprintf(
            Doofinder::DOOFINDER_SEARCH_URL,
            Doofinder::getConfigValue(Doofinder::DOOFINDER_SEARCH_ZONE_CONFIG_KEY) ?? "eu1",
        );

        $this->managementClient = ManagementClient::create(
            $host,
            Doofinder::getConfigValue(Doofinder::DOOFINDER_USER_TOKEN_CONFIG_KEY),
            Doofinder::getConfigValue(Doofinder::DOOFINDER_USER_ID_CONFIG_KEY)
        );

        $this->searchClient = SearchClient::create(
            $hostSearch,
            Doofinder::getConfigValue(Doofinder::DOOFINDER_USER_TOKEN_CONFIG_KEY)
        );
    }

    public function deleteAllProducts()
    {
        $params = [
            'query' => '',
            'rpp' => 100,
            'page' => 1,
        ];
        $products = $this->search($params);
        $productIds = array_map(fn($product) => ['id' => $product['id']], $products['results']);
        $this->deleteDoofinderProductInBulk($productIds);

        if ($products['total'] > 100) {
            $this->deleteAllProducts();
        }
    }

    /**
     * @throws ApiException
     */
    public function createDoofinderProductInBulk(array $itemParams)
    {
        $event = new DoofinderItemParamEvent($itemParams);
        $this->eventDispatcher->dispatch($event, DoofinderItemParamEvents::DOOFINDER_ITEM_PARAM);

        $response = $this->managementClient->createItemsInBulk(
            Doofinder::getConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY),
            "product",
            $itemParams
        );

        return $response->getBody();
    }

    /**
     * @throws ApiException
     */
    public function deleteDoofinderProductInBulk(array $itemParams)
    {
        $response = $this->managementClient->deleteItemsInBulk(
            Doofinder::getConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY),
            "product",
            $itemParams
        );

        return $response->getBody();
    }

    /**
     * @throws ApiException
     */
    public static function getSearchEngine(): ?array
    {
        $host = sprintf(
            Doofinder::DOOFINDER_URL,
            Doofinder::getConfigValue(Doofinder::DOOFINDER_SEARCH_ZONE_CONFIG_KEY) ?? "eu1",
        );

        $managementClient = ManagementClient::create(
            $host,
            Doofinder::getConfigValue(Doofinder::DOOFINDER_USER_TOKEN_CONFIG_KEY),
            Doofinder::getConfigValue(Doofinder::DOOFINDER_USER_ID_CONFIG_KEY)
        );

        $response = $managementClient->getSearchEngine(Doofinder::getConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY));

        return $response->getBody()->jsonSerialize();
    }

    /**
     * @throws ApiException
     */
    public function search($params): ?array
    {
        $response = $this->searchClient->search(
            Doofinder::getConfigValue(Doofinder::DOOFINDER_HASH_ID_CONFIG_KEY),
            $params
        );

        return $response->getBody();
    }
}
