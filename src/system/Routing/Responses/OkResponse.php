<?php


namespace System\Routing\Responses
{


    class OkResponse extends Response
    {

        /**
         * OkResponse constructor.
         * @param mixed|null $body
         */
        public function __construct(mixed $body = null)
        {
            parent::__construct($body);
            $this->status = 200;
        }
    }
}