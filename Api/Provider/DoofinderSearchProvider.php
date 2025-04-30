<?php

namespace Doofinder\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doofinder\Api\Resource\DoofinderProduct;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Doofinder\Service\ApiDoofinderManagementService;

class DoofinderSearchProvider implements ProviderInterface
{
    public function __construct(
        private RequestStack                           $requestStack,
        private readonly ApiDoofinderManagementService $apiDoofinderManagementService,
        private readonly SerializerInterface           $serializer
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|null|object
    {
        $query = $this->requestStack->getCurrentRequest()?->query->get('search', '');

        $searchResults = $this->apiDoofinderManagementService->search(['query' => $query]);
        $searchResults = json_decode($searchResults, true, 512, JSON_THROW_ON_ERROR);

        return $this->serializer->deserialize(
            data: json_encode($searchResults, JSON_THROW_ON_ERROR),
            type: DoofinderProduct::class . '[]',
            format: 'json',
            context : ['groups' => [DoofinderProduct::GROUP_READ]]
        );
    }
}
