<?php


namespace System\Routing\Responses
{


    class BadRequestResponse extends Response
    {

        /**
         * BadRequestResponse constructor.
         * @param mixed|null $body
         */
        public function __construct(mixed $body = null)
        {
            parent::__construct($body);
            $this->status = 400;
        }
    }
}