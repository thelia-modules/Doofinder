<?php

namespace Doofinder\Service;

use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Product;
use Thelia\Model\ProductSaleElements;

class DoofinderBuilderService
{
    public function __construct(
        protected DoofinderFormatService $formatService
    )
    {
    }

    /**
     * @throws PropelException
     */
    public function buildItemParam($products): array
    {
        $countItems = 0;
        $itemParams = [];

        /** @var Product $product */
        foreach ($products as $key => $productSaleElements) {
            if ($key % 100 === 0) {
                $countItems++;
            }

            $itemParams[$countItems][] = $this->formatService->formatIndexImport($productSaleElements);
        }

        return $itemParams;
    }

    /**
     * @throws PropelException
     */
    public function simpleBuildItemParam(Product $product) : array
    {
        return [$this->formatService->formatIndexImport($product)];
    }
}