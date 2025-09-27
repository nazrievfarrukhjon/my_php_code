<?php

namespace App\ECommerce\Catalog\ValueObjects;

class CategoryName
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('Category name is required');
        }
        if (mb_strlen($value) > 255) {
            throw new \InvalidArgumentException('Category name too long');
        }
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
