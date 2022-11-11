<?php

namespace WebApp\Services\ToasterService;

interface ToasterRepositoryInterface
{
    public function getToasts(): array;
}