<?php

namespace Business\Exceptions;

class ValidationErrorMessages
{
    public const INCORRECT_FIRSTNAME = 'incorrect firstname given';
    public const INCORRECT_LASTNAME = 'incorrect lastname given';
    public const INCORRECT_EMAIL = 'incorrect email given';
    public const INCORRECT_POSTAL_CODE = 'incorrect postal code given';
    public const INCORRECT_CITY = 'incorrect city given';
    public const INCORRECT_PASSWORD = 'incorrect password given';
    public const INCORRECT_BIRTHDATE = 'incorrect birthdate given';
    public const INCORRECT_EVENT_VISIBILITY = 'invalid given visibility';
    public const INCORRECT_EVENT_TITLE = 'incorrect event title';
    public const INCORRECT_EVENT_TOO_LONG_TITLE = 'too long event title';
    public const EVENT_CATEGORY_NOT_FOUND = 'event category not found';
    public const TOO_MUCH_PARTICIPANTS = 'too much participants in event';
    public const INSUFFICIENT_PARTICIPANTS = 'insufficient participants in event';
    public const PUBLIC_EVENT_WITH_GUESTS_ONLY = 'public event cannot be guests only';
    public const PARTICIPANTS_FOR_GUESTS_ONLY_PRIVATE_EVENT = 'private event with guests only cannot have participants limit';
    public const EVENT_CANNOT_END_BEFORE_START = 'event cannot end before start';
}
