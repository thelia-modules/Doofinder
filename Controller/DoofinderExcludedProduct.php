<?php

namespace Doofinder\Controller;

use Doofinder\Service\ApiDoofinderManagementService;
use Doofinder\Service\DoofinderExcludedProductService;
use Doofinder\Service\DoofinderFormatService;
use Doofinder\Shared\Exceptions\ApiException;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Log\Tlog;
use Thelia\Tools\TokenProvider;

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
        ApiDoofinderManagementService $apiDoofinderManagementService,
        DoofinderFormatService $formatService,
        RequestStack $requestStack,
        TokenProvider $tokenProvider,
        int $id
    ): JsonResponse
    {
        $request = $requestStack->getCurrentRequest();
        $tokenProvider->checkToken((string) $request->request->get('_token'));

        $jsonResponse = [];
        $data = $request->request->get('is_excluded');

        if ($data === "true") {
            $jsonResponse['excluded'] = $doofinderExcludedProductService->excludeProduct($id);
        }

        if ($data === "false") {
            $jsonResponse['included'] = $doofinderExcludedProductService->includeProduct($id);
        }

        try {
            $results = $apiDoofinderManagementService->synchronizeDoofinderProducts($id);
            $formatService->formatResponse($results);
        } catch (ApiException|\Throwable $exception) {
            Tlog::getInstance()->error(sprintf(
                'Doofinder synchronization of product %d failed: %s',
                $id,
                $exception->getMessage()
            ));

            $jsonResponse['error'] = 'The product was saved but its synchronization with Doofinder failed. Check the module configuration and the logs.';

            return new JsonResponse($jsonResponse, Response::HTTP_BAD_GATEWAY);
        }

        return new JsonResponse($jsonResponse);
    }
}