<?php

namespace Business\Ports;

use Business\Entities\EventCategory;
use PhpLinq\Interfaces\ILinq;

interface EventCategoryRepositoryInterface
{

    public function all(): ILinq;

    public function getById(int $id): ?EventCategory;
}
