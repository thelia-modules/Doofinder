<?php

namespace Doofinder\Model\Api;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(description: "DoofinderProduct")]
class DoofinderProduct
{
    #[Property(type: "string")]
    protected string $id;

    #[Property(type: "string")]
    protected string $dfid;

    #[Property(type: "string")]
    protected string $title;

    #[Property(type: "string", nullable: true)]
    protected ?string $description;

    #[Property(type: "string", nullable: true)]
    protected ?string $brand;

    #[Property(type: "string", nullable: true)]
    protected ?string $image_link;

    #[Property(type: "integer", nullable: true)]
    protected ?int $sale_price;

    #[Property(type: "integer", nullable: true)]
    protected ?int $best_price;

    #[Property(type: "string", nullable: true)]
    protected ?string $link;

    #[Property(type: "array", items: new Items(type:"string"))]
    protected array $categories;

    #[Property(type: "string")]
    protected array $availability;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return DoofinderProduct
     */
    public function setId(string $id): DoofinderProduct
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getDfid(): string
    {
        return $this->dfid;
    }

    /**
     * @param string $dfid
     * @return DoofinderProduct
     */
    public function setDfid(string $dfid): DoofinderProduct
    {
        $this->dfid = $dfid;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return DoofinderProduct
     */
    public function setTitle(string $title): DoofinderProduct
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return DoofinderProduct
     */
    public function setDescription(?string $description): DoofinderProduct
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * @param string|null $brand
     * @return DoofinderProduct
     */
    public function setBrand(?string $brand): DoofinderProduct
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageLink(): ?string
    {
        return $this->image_link;
    }

    /**
     * @param string|null $image_link
     * @return DoofinderProduct
     */
    public function setImageLink(?string $image_link): DoofinderProduct
    {
        $this->image_link = $image_link;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSalePrice(): ?int
    {
        return $this->sale_price;
    }

    /**
     * @param int|null $sale_price
     * @return DoofinderProduct
     */
    public function setSalePrice(?int $sale_price): DoofinderProduct
    {
        $this->sale_price = $sale_price;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBestPrice(): ?int
    {
        return $this->best_price;
    }

    /**
     * @param int|null $best_price
     * @return DoofinderProduct
     */
    public function setBestPrice(?int $best_price): DoofinderProduct
    {
        $this->best_price = $best_price;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string|null $link
     * @return DoofinderProduct
     */
    public function setLink(?string $link): DoofinderProduct
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     * @return DoofinderProduct
     */
    public function setCategories(array $categories): DoofinderProduct
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailability(): array
    {
        return $this->availability;
    }

    /**
     * @param array $availability
     * @return DoofinderProduct
     */
    public function setAvailability(array $availability): DoofinderProduct
    {
        $this->availability = $availability;

        return $this;
    }
}
