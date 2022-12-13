<?php


namespace System\Controllers {


    use System\Routing\Responses\BadRequestResponse;
    use System\Routing\Responses\InternalServerErrorResponse;
    use System\Routing\Responses\NotFoundResponse;
    use System\Routing\Responses\OkResponse;
    use System\Routing\Responses\UnauthorizedResponse;

    interface ResponseControllerInterface
    {
        /**
         * Return bad request response
         * @param mixed|null $content
         * @return BadRequestResponse
         */
        public function badRequest(mixed $content = null): BadRequestResponse;

        /**
         * Return an internal error response
         * @param mixed|null $content
         * @return InternalServerErrorResponse
         */
        public function internalServerError(mixed $content = null): InternalServerErrorResponse;

        /**
         * Return not found response
         * @param mixed|null $content
         * @return NotFoundResponse
         */
        public function notFound(mixed $content = null): NotFoundResponse;

        /**
         * Return Ok response
         * @param mixed|null $content
         * @return OkResponse
         */
        public function ok(mixed $content = null): OkResponse;

        /**
         * Return unauthorized response
         * @param mixed|null $content
         * @return UnauthorizedResponse
         */
        public function unauthorized(mixed $content = null): UnauthorizedResponse;
    }
}
