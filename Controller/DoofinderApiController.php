<?php

namespace Doofinder\Controller;

use Doofinder\Service\DoofinderService;
use Doofinder\Shared\Exceptions\ApiException;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\AdminController;
use Thelia\Log\Tlog;
use Thelia\Tools\URL;

#[Route('/admin/module/Doofinder', name: 'admin_doofinder_api_')]
class DoofinderApiController extends AdminController
{
    public function __construct(protected DoofinderService $doofinderService,) {}

    #[Route('/sync/all', name: 'import_all')]
    public function syncAllProducts(): RedirectResponse|Response
    {
        $sync = "success";

        try {
            Tlog::getInstance()->info($this->doofinderService->synchronizeDoofinderProducts());
        } catch (ApiException|Exception $e) {
            Tlog::getInstance()->error($e->getMessage());
            $sync = "failed";
        }

        return new RedirectResponse(URL::getInstance()->absoluteUrl('/admin/module/Doofinder', ['sync' => $sync]));
    }

    /**
     * Remove all products from Doofinder and import them again
     */
    #[Route('/sync/reset/all', name: 'import_reset_all')]
    public function syncResetAllProducts(): RedirectResponse|Response
    {
        $sync = "success";

        try {
            Tlog::getInstance()->info($this->doofinderService->synchronizeDoofinderProducts(reset: true));
        } catch (ApiException|Exception $e) {
            Tlog::getInstance()->error($e->getMessage());
            $sync = "failed";
        }

        return new RedirectResponse(URL::getInstance()->absoluteUrl('/admin/module/Doofinder', ['sync' => $sync]));
    }
}
