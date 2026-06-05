<?php

namespace Doofinder\Controller;

use Doofinder\Service\ApiDoofinderManagementService;
use Doofinder\Service\DoofinderExcludedProductService;
use Doofinder\Service\DoofinderFormatService;
use Doofinder\Service\DoofinderService;
use Doofinder\Shared\Exceptions\ApiException;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Log\Tlog;

#[Route('/admin/doofinder/excluded/product', name: 'admin_doofinder_excluded_product_')]
class DoofinderExcludedProduct extends BaseAdminController
{
    /**
     * @throws PropelException
     * @throws ApiException
     */
    #[Route('/{id}', name: 'exclude_product', methods: 'POST')]
    public function excludeProduct(
        DoofinderExcludedProductService $doofinderExcludedProductService,
        DoofinderService $doofinderService,
        RequestStack $requestStack,
        int $id
    ): JsonResponse
    {
        $jsonResponse = [];
        $data = $requestStack->getCurrentRequest()->request->get('is_excluded');

        if ($data === "true") {
            $jsonResponse['excluded'] = $doofinderExcludedProductService->excludeProduct($id);
        }

        if ($data === "false") {
            $jsonResponse['included'] = $doofinderExcludedProductService->includeProduct($id);
        }

        Tlog::getInstance()->info($doofinderService->synchronizeDoofinderProducts($id));

        return new JsonResponse($jsonResponse);
    }
}