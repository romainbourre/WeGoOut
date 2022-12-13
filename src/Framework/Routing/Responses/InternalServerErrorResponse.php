<?php


namespace System\Routing\Responses
{


    class InternalServerErrorResponse extends Response
    {

        /**
         * InternalServerErrorResponse constructor.
         * @param mixed|null $body
         */
        public function __construct(mixed $body = null)
        {
            parent::__construct($body);
            $this->status = 500;
        }
    }
}