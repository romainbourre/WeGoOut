<?php

namespace Adapters\PasswordGenerator;

enum CharacterTypes
{
    case Numeric;
    case Alphabetic;
    case AlphaNumeric;
    case SpecialAndAlphanumeric;
}
