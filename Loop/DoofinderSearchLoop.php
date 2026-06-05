<?php

namespace Doofinder\Loop;

use Doofinder\Service\ApiDoofinderManagementService;
use Doofinder\Shared\Exceptions\ApiException;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\Map\ProductTableMap;
use Thelia\Type\EnumListType;
use Thelia\Type\TypeCollection;

class DoofinderSearchLoop extends BaseLoop implements ArraySearchLoopInterface
{
    public function __construct(
        private ApiDoofinderManagementService $apiDoofinderManagementService
    ) {}

    public function parseResults(LoopResult $loopResult): LoopResult
    {
        foreach ($loopResult->getResultDataCollection()['results'] as $searchedProduct) {
            $loopResultRow = new LoopResultRow($searchedProduct);
            $loopResultRow->set('PRODUCT_ID', $searchedProduct['id'])
                ->set('PRODUCT_TOTAL', $loopResult->getResultDataCollection()['total']);
            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }

    protected function getArgDefinitions(): ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createAnyTypeArgument('query'),
            Argument::createIntTypeArgument('doofinder_limit', 12),
            Argument::createIntTypeArgument('doofinder_page', 1),
            new Argument(
                'doofinder_order',
                new TypeCollection(
                    new EnumListType(
                        [
                            'id',
                            'id_reverse',
                            'alpha',
                            'alpha_reverse',
                            'min_price',
                            'max_price',
                            'manual',
                            'manual_reverse',
                            'created',
                            'created_reverse',
                            'updated',
                            'updated_reverse',
                            'ref',
                            'ref_reverse',
                            'visible',
                            'visible_reverse',
                            'position',
                            'position_reverse',
                            'promo',
                            'new',
                            'random',
                            'given_id',
                        ]
                    )
                ),
                'alpha'
            ),
        );
    }

    public function buildArray()
    {
        $params = [];
        if ($this->getQuery()) {
            $params['query'] = $this->getQuery();
        }
        if ($this->getDoofinderLimit()) {
            $params['rpp'] = $this->getDoofinderLimit();
        }
        if ($this->getDoofinderPage()) {
            $params['page'] = $this->getDoofinderPage();
        }

        $parsedOrder = $this->parseOrder($this->getDoofinderOrder());
        if (!empty($parsedOrder)) {
            $params['sort'] = $parsedOrder;
        }

        return $this->apiDoofinderManagementService->search($params);
    }



    private function parseOrder(array $orders): array
    {
        $parsedOrders = [];
        foreach ($orders as $order) {
            switch ($order) {
                case 'id':
                    $parsedOrders['id'] = mb_strtolower(Criteria::ASC);
                    break;
                case 'id_reverse':
                    $parsedOrders['id'] = mb_strtolower(Criteria::DESC);
                    break;
                case 'alpha':
                    $parsedOrders['title'] = mb_strtolower(Criteria::ASC);
                    break;
                case 'alpha_reverse':
                    $parsedOrders['title'] = mb_strtolower(Criteria::DESC);
                    break;
                case 'min_price':
                    $parsedOrders['best_price'] = mb_strtolower(Criteria::ASC);
                    break;
                case 'max_price':
                    $parsedOrders['best_price'] = mb_strtolower(Criteria::DESC);
                    break;
            }
        }
        return $parsedOrders;
    }
}
