<?php

namespace WebApp\Attributes;


use Attribute;

#[Attribute]
class Page
{

    public function __construct(public readonly string $css, public readonly string $js)
    {
    }
}
