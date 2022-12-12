<?php

namespace WebApp\Extensions;

use System\DependencyInjection\ContainerBuilderInterface;
use WebApp\Controllers\CreateEventController;
use WebApp\Controllers\EditEventController;
use WebApp\Controllers\EventController;
use WebApp\Controllers\EventExtensions\EventExtensions;
use WebApp\Controllers\EventExtensions\Extensions\TabAbout;
use WebApp\Controllers\EventExtensions\Extensions\TabParticipants;
use WebApp\Controllers\EventExtensions\Extensions\TabPublications;
use WebApp\Controllers\EventExtensions\Extensions\TabReviews;
use WebApp\Controllers\EventExtensions\Extensions\TabToDoList;
use WebApp\Controllers\ForgotPasswordController;
use WebApp\Controllers\LoginController;
use WebApp\Controllers\NotificationsCenterController;
use WebApp\Controllers\OneEventController;
use WebApp\Controllers\ProfileController;
use WebApp\Controllers\ResearchController;
use WebApp\Controllers\SignUpController;
use WebApp\Controllers\ValidationController;

class ControllersExtension
{
    public static function use(ContainerBuilderInterface $services): void
    {
        $services->addService(CreateEventController::class);
        $services->addService(EditEventController::class);
        $services->addService(EventController::class);
        $services->addService(ForgotPasswordController::class);
        $services->addService(LoginController::class);
        $services->addService(NotificationsCenterController::class);
        $services->addService(OneEventController::class);
        $services->addService(ProfileController::class);
        $services->addService(ResearchController::class);
        $services->addService(SignUpController::class);
        $services->addService(ValidationController::class);
        $services->addService(LoginController::class);
        $services->addService(EventExtensions::class);

        $services->addService(TabPublications::class);
        $services->addService(TabParticipants::class);
        $services->addService(TabToDoList::class);
        $services->addService(TabAbout::class);
        $services->addService(TabReviews::class);
    }
}
