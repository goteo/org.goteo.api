<?php

namespace App\Entity\Project;

enum Category: string
{
    case Solidary = 'solidary';

    case LibreSoftware = 'libre-software';

    case Employment = 'employment';

    case Design = 'design';

    case Journalism = 'journalism';

    case Education = 'education';

    case Culture = 'culture';

    case Ecology = 'ecology';

    case HealthCares = 'health-and-cares';

    case OpenData = 'open-data';

    case Democracy = 'democracy';

    case Equity = 'equity';
}
