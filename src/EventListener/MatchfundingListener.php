<?php

namespace App\EventListener;

use App\Entity\Gateway\Charge;
use App\Service\Matchfunding\MatchfundingService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::postPersist,
    method: 'processMatchfunding',
    entity: Charge::class
)]
final class MatchfundingListener
{
    public function __construct(
        private MatchfundingService $matchfunding,
    ) {}

    public function processMatchfunding(Charge $charge, PostPersistEventArgs $event)
    {
        $this->matchfunding->match($charge);
    }
}
