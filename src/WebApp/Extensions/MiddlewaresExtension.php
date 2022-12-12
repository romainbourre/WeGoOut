<?php

namespace WebApp\Extensions;

use System\DependencyInjection\ContainerBuilderInterface;
use WebApp\Authentication\Middlewares\AuthenticationMiddleware;
use WebApp\Middleware\AccountNotValidatedGuardMiddleware;
use WebApp\Middleware\AccountValidatedGuardMiddleware;
use WebApp\Middleware\AccountValidatedGuardMiddlewareAsUnauthorized;
use WebApp\Middleware\AlertDisplayMiddleware;
use WebApp\Middleware\AuthenticatedUserGuardMiddleware;
use WebApp\Middleware\CreateEventMiddleware;
use WebApp\Middleware\ErrorManagerMiddleware;
use WebApp\Middleware\NonAuthenticatedUserGuardMiddleware;
use WebApp\Middleware\NotificationDisplayMiddleware;
use WebApp\Middleware\SearchResultsDisplayMiddleware;

class MiddlewaresExtension
{
    public static function use(ContainerBuilderInterface $services): void
    {
        $services->addService(NonAuthenticatedUserGuardMiddleware::class);
        $services->addService(AlertDisplayMiddleware::class);
        $services->addService(AuthenticationMiddleware::class);
        $services->addService(ErrorManagerMiddleware::class);
        $services->addService(AccountValidatedGuardMiddleware::class);
        $services->addService(AccountNotValidatedGuardMiddleware::class);
        $services->addService(AccountValidatedGuardMiddlewareAsUnauthorized::class);
        $services->addService(NotificationDisplayMiddleware::class);
        $services->addService(SearchResultsDisplayMiddleware::class);
        $services->addService(CreateEventMiddleware::class);
        $services->addService(AuthenticatedUserGuardMiddleware::class);
    }
}
