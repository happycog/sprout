<?php

namespace BarrelStrength\Sprout\core\helpers;

use CommerceGuys\Addressing\Country\CountryRepository;
use libphonenumber\PhoneNumberUtil;

class RegexHelper
{
    public const UUID_PATTERN = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';
}
