<?php

namespace App\ECommerce\Catalog\Entities;

use App\ECommerce\Catalog\ValueObjects\CategoryName;
use App\ECommerce\Catalog\ValueObjects\Slug;

final class Category
{
    private string $id;
    private CategoryName $name;
    private Slug $slug;
    private ?string $parentId;
    /** @var string[] children ids kept in memory by aggregate (optional) */
    private array $childrenIds = [];

    public function __construct(string $id, CategoryName $name, Slug $slug, ?string $parentId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->parentId = $parentId;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): CategoryName
    {
        return $this->name;
    }

    public function slug(): Slug
    {
        return $this->slug;
    }

    public function parentId(): ?string
    {
        return $this->parentId;
    }

    public function changeName(CategoryName $name): void
    {
        $this->name = $name;
        $this->slug = Slug::fromName($name);
    }

    public function setParent(?string $parentId): void
    {
        // domain rule: don't set parent to self (check for cycles is done at service/repo using queries)
        if ($parentId === $this->id) {
            throw new \DomainException("Category cannot be parent of itself");
        }
        $this->parentId = $parentId;
    }

    public function addChildId(string $childId): void
    {
        if (!in_array($childId, $this->childrenIds, true)) {
            $this->childrenIds[] = $childId;
        }
    }

    public function childrenIds(): array
    {
        return $this->childrenIds;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name->value(),
            'slug' => $this->slug->value(),
            'parent_id' => $this->parentId,
        ];
    }
}
