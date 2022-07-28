<?php

namespace System\Routing\Responses;

class RedirectedResponse extends Response
{

    public function __construct()
    {
        parent::__construct();
        $this->status = 302;
    }

    public static function to(string $redirectUrl): Response
    {
        return (new self())->withRedirectTo($redirectUrl);
    }
}