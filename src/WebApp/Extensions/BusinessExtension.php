<?php

namespace WebApp\Extensions;

use Adapters\DateTimeProvider\DateTimeProvider;
use Adapters\Md5PasswordEncoder\Md5PasswordEncoder;
use Adapters\MySqlDatabase\Repositories\EventRepository;
use Adapters\MySqlDatabase\Repositories\UserRepository;
use Adapters\PasswordGenerator\PasswordGenerator;
use Adapters\SendGrid\SendGridAdapter;
use Adapters\TokenProvider\TokenProvider;
use Adapters\TwigRenderer\TwigRendererAdapter;
use Business\Ports\AuthenticationContextInterface;
use Business\Ports\DateTimeProviderInterface;
use Business\Ports\EmailSenderInterface;
use Business\Ports\EventRepositoryInterface;
use Business\Ports\PasswordEncoderInterface;
use Business\Ports\PasswordGeneratorInterface;
use Business\Ports\TemplateRendererInterface;
use Business\Ports\TokenProviderInterface;
use Business\Ports\UserRepositoryInterface;
use Business\Services\AccountService\AccountService;
use Business\Services\AccountService\IAccountService;
use Business\Services\EventService\EventService;
use Business\Services\EventService\IEventService;
use System\DependencyInjection\ContainerBuilderInterface;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Librairies\Emitter;
use WebApp\Services\ToasterService\ToasterInterface;
use WebApp\Services\ToasterService\ToasterRepositoryInterface;
use WebApp\Services\ToasterService\ToasterService;

class BusinessExtension
{
    public static function use(ContainerBuilderInterface $services): void
    {
        $services->addService(ToasterService::class, new ToasterService());
        $services->addService(EventRepositoryInterface::class, EventRepository::class);
        $services->addService(UserRepositoryInterface::class, UserRepository::class);
        $services->addService(EventService::class, EventService::class);
        $services->addService(TemplateRendererInterface::class, new TwigRendererAdapter(ROOT . '/Business/Templates/Emails'));
        $services->addService(EmailSenderInterface::class, SendGridAdapter::class);
        $services->addService(PasswordEncoderInterface::class, Md5PasswordEncoder::class);
        $services->addService(DateTimeProviderInterface::class, DateTimeProvider::class);
        $services->addService(TokenProviderInterface::class, TokenProvider::class);
        $services->addService(PasswordGeneratorInterface::class, PasswordGenerator::class);
        $services->addService(AuthenticationContextInterface::class, AuthenticationContext::class);
        $services->addService(Emitter::class, Emitter::getInstance());
        $services->addService(IEventService::class, EventService::class);
        $services->addService(IAccountService::class, AccountService::class);
        $services->addService(ToasterInterface::class, ToasterService::class);
        $services->addService(ToasterRepositoryInterface::class, ToasterService::class);
    }
}
