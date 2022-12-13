<?php


namespace System\Routing\Responses
{


    class NotFoundResponse extends Response
    {

        /**
         * NotFoundResponse constructor.
         * @param mixed|null $body
         */
        public function __construct(mixed $body = null)
        {
            parent::__construct($body);
            $this->status = 404;
        }
    }
}