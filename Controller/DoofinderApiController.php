<?php

namespace Doofinder\Controller;

use Doofinder\Service\ApiDoofinderManagementService;
use Doofinder\Service\DoofinderFormatService;
use Doofinder\Shared\Exceptions\ApiException;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\AdminController;
use Thelia\Log\Tlog;
use Thelia\Tools\URL;

#[Route('/admin/module/Doofinder', name: 'admin_doofinder_api_')]
class DoofinderApiController extends AdminController
{
    public function __construct(
        protected ApiDoofinderManagementService $apiDoofinderManagementService,
        protected DoofinderFormatService $formatService
    ) {}

    #[Route('/sync/all', name: 'import_all')]
    public function syncAllProducts(): RedirectResponse|Response
    {
        $sync = "success";

        try {
            $results = $this->apiDoofinderManagementService->synchronizeDoofinderProducts();

            $this->formatService->formatResponse($results);
        } catch (ApiException|Exception $e) {
            Tlog::getInstance()->error($e->getMessage());
            $sync = "failed";
        }

        return new RedirectResponse(URL::getInstance()->absoluteUrl('/admin/module/Doofinder', ['sync' => $sync]));
    }
}