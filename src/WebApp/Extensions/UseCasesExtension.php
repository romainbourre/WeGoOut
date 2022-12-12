<?php

namespace WebApp\Extensions;

use Business\UseCases\AskNewPassword\AskNewPasswordUseCase;
use Business\UseCases\Login\LoginUseCase;
use Business\UseCases\SignUp\SignUpUseCase;
use Business\UseCases\ValidateUserAccount\ValidateUserAccountUseCase;
use System\DependencyInjection\ContainerBuilderInterface;

class UseCasesExtension
{
    public static function use(ContainerBuilderInterface $services): void
    {
        $services->addService(LoginUseCase::class);
        $services->addService(SignUpUseCase::class);
        $services->addService(AskNewPasswordUseCase::class);
        $services->addService(ValidateUserAccountUseCase::class);
    }
}
