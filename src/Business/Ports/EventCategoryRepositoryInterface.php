<?php

namespace Business\Ports;

use Business\Entities\EventCategory;

interface EventCategoryRepositoryInterface
{

    public function getById(int $id): ?EventCategory;
}
