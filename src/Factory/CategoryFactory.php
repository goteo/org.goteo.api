<?php

namespace App\Factory;

use App\Entity\Category;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Category>
 */
final class CategoryFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Category::class;
    }

    protected function defaults(string $id = 'test'): array|callable
    {
        return [
            'id' => $id,
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Category $category): void {})
            ->andPersist()
        ;
    }
}
