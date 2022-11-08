<?php


namespace Business\UseCases\Login
{


    use Business\Exceptions\BadArgumentException;
    use Business\Ports\RequestInterface;
    use PhpLinq\PhpLinq;
    use Respect\Validation\Validator;

    class LoginRequestInterface implements RequestInterface
    {


        public function __construct(public readonly string $email, public readonly string $password)
        {
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