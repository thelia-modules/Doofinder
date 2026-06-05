<?php

namespace Doofinder\Controller\Api;

use Doofinder\Service\ApiDoofinderManagementService;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use OpenApi\Controller\Front\BaseFrontOpenApiController;
use OpenApi\Service\OpenApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Core\HttpFoundation\JsonResponse;

#[Route('/open_api/doofinder', name: "doofinder")]
class SearchController extends BaseFrontOpenApiController
{
    #[Route('/search', name: "_search", methods: ["Get"])]
    #[Get(
        path: "/doofinder/search",
        summary: "Search products",
        tags: ["Doofinder", "Search"],
        parameters: [
            new Parameter(
                name: 'query',
                in: 'query',
                schema: new Schema(type: 'string')
            ),
        ],
        responses: [
            new Response(
                response: "200",
                description: "Success",
                content: [
                    new JsonContent(
                        type: "array",
                        items: new Items(
                            ref: "#/components/schemas/DoofinderProduct"
                        )
                    )
                ]
            )
        ]
    )]
    public function search(
        Request $request,
        ApiDoofinderManagementService $apiDoofinderManagementService
    ): JsonResponse
    {
        $query = $request->get('query');
        $search = $apiDoofinderManagementService->search(['query' => $query]);


        return OpenApiService::jsonResponse($search['results']);
    }
}
