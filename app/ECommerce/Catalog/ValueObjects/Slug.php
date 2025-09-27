<?php

namespace App\ECommerce\Catalog\ValueObjects;

final class Slug
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        // simple validation, you can use a stricter regex
        if (!preg_match('/^[a-z0-9\-]+$/', $value)) {
            throw new \InvalidArgumentException('Invalid slug');
        }
        $this->value = $value;
    }

    public static function fromName(CategoryName $name): self
    {
        $slug = mb_strtolower($name->value());
        $slug = preg_replace('/[^\w\-]+/u', '-', $slug);
        $slug = preg_replace('/\-+/', '-', $slug);
        $slug = trim($slug, '-');
        return new self(substr($slug, 0, 190));
    }

    public function value(): string
    {
        return $this->value;
    }
}
