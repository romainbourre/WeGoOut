<?php


namespace System\Routing\Responses
{


    class UnauthorizedResponse extends Response
    {

        /**
         * UnauthorizedResponse constructor.
         * @param mixed|null $body
         */
        public function __construct(mixed $body = null)
        {
            parent::__construct($body);
            $this->status = 403;
        }
    }
}