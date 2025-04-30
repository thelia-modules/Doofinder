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

    /**
     * @throws PropelException
     * @throws ApiException
     */
    public function synchronizeDoofinderProducts(int $productId = null): array
    {
        $results = [];

        $itemParamsUpdated = $this->buildItemParam(Doofinder::DOOFINDER_STATE_CREATED_UPDATED, $productId);
        $itemParamsDeleted = $this->buildItemParam(Doofinder::DOOFINDER_STATE_DELETED, $productId);

        if ($itemParamsUpdated !== []) {
            $results[Doofinder::DOOFINDER_STATE_CREATED_UPDATED] = $this->createDoofinderProductInBulk($itemParamsUpdated);
        }
        if ($itemParamsDeleted !== []) {
            $results[Doofinder::DOOFINDER_STATE_DELETED] = $this->deleteDoofinderProductInBulk($itemParamsDeleted);
        }

        return $results;
    }

    /**
     * @throws PropelException
     * @throws ApiException
     */
    public function addDoofinderProductSaleElementss(ProductSaleElements $productSaleElements): array
    {
        $itemParamsUpdated = $this->formatService->formatIndexImport($productSaleElements);

        return $this->createDoofinderProductInBulk($itemParamsUpdated);
    }

    /**
     * @throws ApiException
     * @throws PropelException
     */
    public function deleteDoofinderProducts(int $productId = null): array
    {
        $itemParamsDeleted = $this->buildItemParam(Doofinder::DOOFINDER_STATE_DELETED, $productId);

        return $this->deleteDoofinderProductInBulk($itemParamsDeleted);
    }

    /**
     * @throws ApiException
     * @throws PropelException
     */
    public function deleteDoofinderProductSaleElementss(ProductSaleElements $productSaleElements)
    {
        $itemParamsDeleted = $this->formatService->formatIndexImport($productSaleElements);

        return $this->deleteDoofinderProductInBulk($itemParamsDeleted);
    }



    /**
     * @throws PropelException
     */
    public function buildItemParam(string $type, int $productId = null): array
    {
        $itemParams = [];
        $productSaleElementss = [];

        if (null !== $productId) {
            if ($type === Doofinder::DOOFINDER_STATE_CREATED_UPDATED) {
                $productSaleElementss = ProductSaleElementsQuery::create()
                    ->useProductQuery()
                    ->filterById($productId)
                    ->filterByVisible(1)
                    ->useDoofinderExcludedProductNotExistsQuery()
                    ->endUse()
                    ->endUse()
                    ->find()
                ;
            }

            if ($type === Doofinder::DOOFINDER_STATE_DELETED) {
                $productSaleElementss = ProductSaleElementsQuery::create()
                    ->useProductQuery()
                    ->filterById($productId)
                    ->filterByVisible(0)
                    ->_or()
                    ->useDoofinderExcludedProductExistsQuery()
                    ->endUse()
                    ->endUse()
                    ->find();
            }
        }

        if (null === $productId) {
            if ($type === Doofinder::DOOFINDER_STATE_CREATED_UPDATED) {
                $productSaleElementss = ProductSaleElementsQuery::create()
                    ->useProductQuery()
                    ->filterByVisible(1)
                    ->useDoofinderExcludedProductNotExistsQuery()
                    ->endUse()
                    ->endUse()
                    ->find()
                ;
            }

            if ($type === Doofinder::DOOFINDER_STATE_DELETED) {
                $productSaleElementss = ProductSaleElementsQuery::create()
                    ->useProductQuery()
                    ->filterByVisible(0)
                    ->_or()
                    ->useDoofinderExcludedProductExistsQuery()
                    ->endUse()
                    ->endUse()
                    ->find()
                ;
            }
        }

        /** @var ProductSaleElements $productSaleElements */
        foreach ($productSaleElementss as $productSaleElements) {
            $itemParams[] = $this->formatService->formatIndexImport($productSaleElements);
        }

        return $itemParams;
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
