<?php

namespace Adapters\InMemory\Repositories;

use Business\Entities\EventCategory;
use Business\Ports\EventCategoryRepositoryInterface;
use PhpLinq\Interfaces\ILinq;
use PhpLinq\PhpLinq;

class InMemoryEventCategoryRepository implements EventCategoryRepositoryInterface
{
    public ILinq $categories;

    public function __construct()
    {
        $this->categories = new PhpLinq();
    }

    public function haveNoCategories(): void
    {
        $this->categories = new PhpLinq();
    }

    public function haveOneCategory(string $name): EventCategory
    {
        $category = new EventCategory(1, $name);
        $this->categories = new PhpLinq([$category]);
        return $category;
    }

    public function getById(int $id): ?EventCategory
    {
        return $this->categories->firstOrNull(fn(EventCategory $category) => $category->id === $id);
    }
}
