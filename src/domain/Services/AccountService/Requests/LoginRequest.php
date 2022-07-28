<?php


namespace Domain\Services\AccountService\Requests
{


    use Domain\Exceptions\BadArgumentException;
    use Domain\Interfaces\IRequest;
    use PhpLinq\PhpLinq;
    use Respect\Validation\Validator;

    class LoginRequest implements IRequest
    {
        /**
         * @var ?string email of user
         */
        public ?string $email;

        /**
         * @var ?string password of user
         */
        public ?string $password;

        /**
         * LoginRequest constructor.
         * @param ?string $email
         * @param ?string $password
         */
        public function __construct(?string $email, ?string $password)
        {
            $this->email = $email;
            $this->password = $password;
        }

        /**
         * @inheritDoc
         */
        public function valid(): void
        {
            $validators = new PhpLinq();
            $validators->add(Validator::notOptional()->email()->validate($this->email));
            $validators->add(Validator::notOptional()->validate($this->password));

            if($validators->any(fn(bool $result) => !$result))
            {
                $class = self::class;
                throw new BadArgumentException("incorrect argument(s) for request $class");
            }
        }
    }
}