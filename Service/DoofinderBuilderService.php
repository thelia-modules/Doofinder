<?php

namespace Doofinder\Service;

use Propel\Runtime\Exception\PropelException;
use RuntimeException;
use Thelia\Log\Tlog;
use Thelia\Model\Product;
use Thelia\Model\ProductSaleElements;

class DoofinderBuilderService
{
    private const BULK_ITEM_LIMIT = 100;

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
        $batches = [];
        $currentBatch = [];

        /** @var Product $product */
        foreach ($products as $product) {
            try {
                $items = $isDelete
                    ? $this->formatService->formatIndexImportDelete($product->getId())
                    : $this->formatService->formatIndexImport($product);
            } catch (RuntimeException $exception) {
                Tlog::getInstance()->error($exception->getMessage());
                continue;
            }

            foreach ($items as $item) {
                $currentBatch[] = $item;

                if (count($currentBatch) === self::BULK_ITEM_LIMIT) {
                    $batches[] = $currentBatch;
                    $currentBatch = [];
                }
            }
        }

        if ($currentBatch !== []) {
            $batches[] = $currentBatch;
        }

        return $batches;
    }

    /**
     * @throws PropelException
     */
    public function simpleBuildItemParam(Product $product) : array
    {
        return [$this->formatService->formatIndexImport($product)];
    }
}
