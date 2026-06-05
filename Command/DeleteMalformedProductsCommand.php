<?php

declare(strict_types=1);

namespace Doofinder\Command;

use Doofinder\Service\ApiDoofinderManagementService;
use Doofinder\Shared\Exceptions\ApiException;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thelia\Model\Product;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductQuery;

#[AsCommand(name: 'module:doofinder:delete:malformed:products', description: 'This command deletes all malformed products from the Doofinder API.')]
class DeleteMalformedProductsCommand extends Command
{

    public function __construct(
        protected ApiDoofinderManagementService $apiDoofinderManagementService
    )
    {
        parent::__construct();
    }

    /**
     * @throws PropelException
     * @throws ApiException
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $products = ProductQuery::create()->find();
        $count = 0;
        foreach ($products as $product) {
            foreach ($product->getProductSaleElementss() as $productSaleElements) {
                $productPrice = ProductPriceQuery::create()->findOneByProductSaleElementsId($productSaleElements->getId());
                if ($productPrice?->getPrice() > 0) {
                    continue 2;
                }
            }
            $this->deleteProduct($product, $output);
            ++$count;
        }
        $output->write("Products deleted: {$count}");
        return Command::SUCCESS;
    }

    /**
     * @throws ApiException|\JsonException
     */
    private function deleteProduct(Product $product, OutputInterface $output): void
    {
        $itemParams = [
            ['id' => (string)$product->getId()],
        ];
        $result = $this->apiDoofinderManagementService->deleteDoofinderProductInBulk($itemParams);
        $output->write(json_encode($result, JSON_THROW_ON_ERROR));
    }
}
