<?php


namespace System\Controllers
{


    use System\Routing\Responses\BadRequestResponse;
    use System\Routing\Responses\NotFoundResponse;
    use System\Routing\Responses\UnauthorizedResponse;
    use System\Routing\Responses\InternalServerErrorResponse;
    use System\Routing\Responses\OkResponse;

    interface IResponseController
    {
        /**
         * Return bad request response
         * @param mixed|null $content
         * @return BadRequestResponse
         */
        function badRequest(mixed $content = null): BadRequestResponse;

        /**
         * Return an internal error response
         * @param mixed|null $content
         * @return InternalServerErrorResponse
         */
        function internalServerError(mixed $content = null): InternalServerErrorResponse;

        /**
         * Return not found response
         * @param mixed|null $content
         * @return NotFoundResponse
         */
        function notFound(mixed $content = null): NotFoundResponse;

        /**
         * Return Ok response
         * @param mixed|null $content
         * @return OkResponse
         */
        function ok(mixed $content = null): OkResponse;

        /**
         * Return unauthorized response
         * @param mixed|null $content
         * @return UnauthorizedResponse
         */
        function unauthorized(mixed $content = null): UnauthorizedResponse;
    }
}