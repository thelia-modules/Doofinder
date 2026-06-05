<?php

namespace Doofinder\Service;

use Doofinder\Doofinder;
use Doofinder\Shared\Exceptions\ApiException;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;

class DoofinderService
{
    public function __construct(
        protected DoofinderBuilderService $doofinderBuilderService,
        protected ApiDoofinderManagementService $apiDoofinderManagementService,
        protected DoofinderFormatService $doofinderFormatService
    )
    {
    }

    /**
     * @throws PropelException
     * @throws ApiException
     */
    public function synchronizeDoofinderProducts(int $productId = null, bool $reset = false): string
    {
        if ($reset) {
            $this->apiDoofinderManagementService->deleteAllProducts();
        }

        $results = [
            Doofinder::DOOFINDER_STATE_CREATED_UPDATED => $this->createUpdateDoofinderProducts($productId),
            Doofinder::DOOFINDER_STATE_DELETED => $this->deleteDoofinderProducts($productId)
        ];

        return $this->doofinderFormatService->formatResponse($results);
    }

    /**
     * @throws ApiException
     * @throws PropelException
     */
    public function createUpdateDoofinderProducts(int $productId = null): array
    {
        $results = [];

        $itemParamsArrayUpdated = $this->doofinderBuilderService->buildItemParam(
            $this->getProducts(Doofinder::DOOFINDER_STATE_CREATED_UPDATED, $productId)
        );

        if ($itemParamsArrayUpdated !== []) {
            foreach ($itemParamsArrayUpdated as $itemParamsUpdated) {
                $results[] = $this->apiDoofinderManagementService->createDoofinderProductInBulk($itemParamsUpdated);
            }
        }

        return $results;
    }


    /**
     * @throws ApiException
     * @throws PropelException
     */
    public function deleteDoofinderProducts(int $productId = null): array
    {
        $results = [];

        $itemParamsArrayDeleted = $this->doofinderBuilderService->buildItemParam(
            $this->getProducts(Doofinder::DOOFINDER_STATE_DELETED, $productId)
        );

        if ($itemParamsArrayDeleted !== []) {
            foreach ($itemParamsArrayDeleted as $itemParamsDeleted) {
                $results[] = $this->apiDoofinderManagementService->deleteDoofinderProductInBulk($itemParamsDeleted);
            }
        }

        return $results;
    }


    /**
     * @throws ApiException
     * @throws PropelException
     */
    public function deleteDoofinderSingleProduct(Product $product): string
    {
        $results = [];

        $itemParamsArrayDeleted = $this->doofinderBuilderService->buildItemParam([$product], true);

        if ($itemParamsArrayDeleted !== []) {
            foreach ($itemParamsArrayDeleted as $itemParamsDeleted) {
                $results[] = $this->apiDoofinderManagementService->deleteDoofinderProductInBulk($itemParamsDeleted);
            }
        }

        $results = [
            Doofinder::DOOFINDER_STATE_DELETED => $results
        ];

        return $this->doofinderFormatService->formatResponse($results);
    }

    private function getProducts(string $type, int $productId = null): array|ObjectCollection
    {
        if (null !== $productId) {
            if ($type === Doofinder::DOOFINDER_STATE_CREATED_UPDATED) {
                return ProductQuery::create()
                    ->filterById($productId)
                    ->filterByVisible(1)
                    ->useDoofinderExcludedProductNotExistsQuery()
                    ->endUse()
                    ->find();
            }

            if ($type === Doofinder::DOOFINDER_STATE_DELETED) {
                return ProductQuery::create()
                    ->filterByVisible(0)
                    ->filterById($productId)
                    ->_or()
                    ->useDoofinderExcludedProductExistsQuery()
                    ->endUse()
                    ->find();
            }

            return [];
        }

        if ($type === Doofinder::DOOFINDER_STATE_CREATED_UPDATED) {
            return ProductQuery::create()
                ->filterByVisible(1)
                ->useDoofinderExcludedProductNotExistsQuery()
                ->endUse()
                ->find();
        }

        if ($type === Doofinder::DOOFINDER_STATE_DELETED) {
            return ProductQuery::create()
                ->filterByVisible(0)
                ->_or()
                ->useDoofinderExcludedProductExistsQuery()
                ->endUse()
                ->find();
        }

        return [];
    }
}
