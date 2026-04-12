<?php

namespace Doofinder\Api\Resource;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Doofinder\Api\Provider\DoofinderSearchProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/doofinder/search',
            openapi: new Operation(parameters: [
                new Parameter(name: 'search', in: 'query', required: false, schema: ['type' => 'string'], description: 'Termes de recherche'),
            ]),
            provider: DoofinderSearchProvider::class
        )
    ],
    normalizationContext: ['groups' => [self::GROUP_READ]]
)]
class DoofinderProduct
{
    public const GROUP_READ = 'doofinder:read';

    #[Groups([self::GROUP_READ])]
    private string $id;

    #[Groups([self::GROUP_READ])]
    private string $dfid;

    #[Groups([self::GROUP_READ])]
    private string $title;

    #[Groups([self::GROUP_READ])]
    private ?string $description = null;

    #[Groups([self::GROUP_READ])]
    private ?string $brand = null;

    #[Groups([self::GROUP_READ])]
    private ?string $image_link = null;

    #[Groups([self::GROUP_READ])]
    private ?float $sale_price = null;

    #[Groups([self::GROUP_READ])]
    private ?float $best_price = null;

    #[Groups([self::GROUP_READ])]
    private ?string $link = null;

    #[Groups([self::GROUP_READ])]
    private array $categories = [];

    #[Groups([self::GROUP_READ])]
    private string $availability;

    public function getAvailability(): string
    {
        return $this->availability;
    }

    public function setAvailability(string $availability): DoofinderProduct
    {
        $this->availability = $availability;
        return $this;
    }

    public function getBestPrice(): ?float
    {
        return $this->best_price !== null ? round($this->best_price, 2) : null;
    }

    public function setBestPrice(?float $best_price): DoofinderProduct
    {
        $this->best_price = $best_price;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): DoofinderProduct
    {
        $this->brand = $brand;
        return $this;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): DoofinderProduct
    {
        $this->categories = $categories;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): DoofinderProduct
    {
        $this->description = $description;
        return $this;
    }

    public function getDfid(): string
    {
        return $this->dfid;
    }

    public function setDfid(string $dfid): DoofinderProduct
    {
        $this->dfid = $dfid;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): DoofinderProduct
    {
        $this->id = $id;
        return $this;
    }

    public function getImageLink(): ?string
    {
        return $this->image_link;
    }

    public function setImageLink(?string $image_link): DoofinderProduct
    {
        $this->image_link = $image_link;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): DoofinderProduct
    {
        $this->link = $link;
        return $this;
    }

    public function getSalePrice(): ?float
    {
        return $this->sale_price !== null ? round($this->sale_price, 2) : null;
    }

    public function setSalePrice(?float $sale_price): DoofinderProduct
    {
        $this->sale_price = $sale_price;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): DoofinderProduct
    {
        $this->title = $title;
        return $this;
    }
}
