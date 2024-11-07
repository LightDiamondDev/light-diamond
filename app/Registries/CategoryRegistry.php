<?php

namespace App\Registries;

use App\Models\Enums\GameEdition;
use App\Registries\Types\ArticlesCategory;
use App\Registries\Types\BedrockAddonsCategory;
use App\Registries\Types\BedrockMapsCategory;
use App\Registries\Types\BedrockResourcePacksCategory;
use App\Registries\Types\Category;
use App\Registries\Types\JavaDataPacksCategory;
use App\Registries\Types\JavaMapsCategory;
use App\Registries\Types\JavaModsCategory;
use App\Registries\Types\JavaResourcePacksCategory;
use App\Registries\Types\SkinsCategory;

class CategoryRegistry
{
    /** @var array<class-string<CategoryType>, Category> */
    private array $categories = [];

    public function __construct()
    {
        $this->registerAll();
    }

    private function registerAll(): void
    {
        /** @var Category[] $categories */
        $categories = [
            new ArticlesCategory, new SkinsCategory,
            new BedrockResourcePacksCategory, new BedrockAddonsCategory, new BedrockMapsCategory,
            new JavaResourcePacksCategory, new JavaDataPacksCategory, new JavaModsCategory, new JavaMapsCategory
        ];

        foreach ($categories as $category) {
            $this->register($category);
        }
    }

    private function register(Category $category): void
    {
        $this->categories[$category->getType()->value] = $category;
    }

    /**
     * @return Category[]
     */
    public function getByEdition(GameEdition $edition): array
    {
        return array_filter($this->categories, fn($category) => $category->getEdition() === $edition);
    }

    public function get(CategoryType $type): Category
    {
        return new $this->categories[$type->value];
    }
}
