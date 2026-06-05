<?php

namespace Doofinder\Service;

use Propel\Runtime\Exception\PropelException;
use RuntimeException;
use Thelia\Log\Tlog;
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
    public function buildItemParam($products, $isDelete = false): array
    {
        $countItems = 0;
        $itemParams = [];

        /** @var Product $product */
        foreach ($products as $key => $productSaleElements) {
            if ($key % 100 === 0) {
                $countItems++;
            }
            try {
                if ($isDelete) {
                    $itemParams[$countItems][] = $this->formatService->formatIndexImportDelete($productSaleElements->getId());
                } else {
                    $itemParams[$countItems][] = $this->formatService->formatIndexImport($productSaleElements);
                }
            }catch (RuntimeException $exception){
                Tlog::getInstance()->error($exception->getMessage());
                continue;
            }
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
