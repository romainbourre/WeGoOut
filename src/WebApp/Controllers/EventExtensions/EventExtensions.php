<?php

namespace WebApp\Controllers\EventExtensions;


use PhpLinq\PhpLinq;
use WebApp\Controllers\EventExtensions\Extensions\TabAbout;
use WebApp\Controllers\EventExtensions\Extensions\TabParticipants;
use WebApp\Controllers\EventExtensions\Extensions\TabPublications;
use WebApp\Controllers\EventExtensions\Extensions\TabReviews;
use WebApp\Controllers\EventExtensions\Extensions\TabToDoList;

class EventExtensions extends PhpLinq
{

    public function __construct(TabPublications $tabPublications, TabParticipants $tabParticipants, TabToDoList $tabToDoList, TabAbout $tabAbout, TabReviews $tabReviews)
    {
        parent::__construct([$tabPublications, $tabParticipants, $tabToDoList, $tabAbout, $tabReviews]);
    }
}
