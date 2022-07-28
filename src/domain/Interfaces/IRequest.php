<?php


namespace Domain\Interfaces;


use Domain\Exceptions\BadArgumentException;

interface IRequest
{
    /**
     * Ensure if request is valid
     * @throws BadArgumentException
     */
    public function valid(): void;
}