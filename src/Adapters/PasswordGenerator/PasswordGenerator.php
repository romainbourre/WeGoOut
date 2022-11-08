<?php

namespace Adapters\PasswordGenerator;

use Business\Ports\PasswordGeneratorInterface;
use InvalidArgumentException;


class PasswordGenerator implements PasswordGeneratorInterface
{
    public static function generateTimeStampMd5(): string
    {
        return md5(microtime());
    }

    public function generate(): string
    {
        $groupNumbers = 4;
        $finalPassword = '';
        for ($i = 0; $i < $groupNumbers; $i++) {
            if ($i > 0) {
                $finalPassword .= '-';
            }
            $finalPassword .= $this->generateRandomString();
        }
        return $finalPassword;
    }

    private function generateRandomString(): string
    {
        $characters = $this->getCharactersSet(CharacterTypes::AlphaNumeric);
        $randomCharactersSize = 4;
        $finalRandomString = '';
        for ($characterIndex = 1; $characterIndex <= $randomCharactersSize; $characterIndex++) {
            $numberOfCharactersSet = strlen($characters);
            $randomIndex = mt_rand(0, ($numberOfCharactersSet - 1));
            $finalRandomString .= $characters[$randomIndex];
        }
        return $finalRandomString;
    }

    private function getCharactersSet(CharacterTypes $type): string
    {
        return match ($type) {
            CharacterTypes::Numeric => '0123456789',
            CharacterTypes::Alphabetic => 'abcdefghijklmnopqrstuvwyxzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            CharacterTypes::AlphaNumeric => 'abcdefghijklmnopqrstuvwyxzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            CharacterTypes::SpecialAndAlphanumeric => 'abcdefghijklmnopqrstuvwyxzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@!:;,§/?*µ$=+',
            default => throw new InvalidArgumentException($type->name),
        };
    }
}
