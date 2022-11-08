<?php


namespace Business\Ports;


use Business\Exceptions\BadArgumentException;

interface RequestInterface
{
    /**
     * Ensure if request is valid
     * @throws BadArgumentException
     */
    public function valid(): void;
}