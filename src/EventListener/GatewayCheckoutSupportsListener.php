<?php

namespace App\EventListener;

use App\Entity\Accounting\Accounting;
use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Entity\Project\Project;
use App\Entity\Project\Support;
use App\Repository\Project\SupportRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(
    event: Events::preFlush,
    method: 'preFlush',
    entity: Checkout::class
)]
class GatewayCheckoutSupportsListener
{
    public function __construct(
        private SupportRepository $supportRepository,
    ) {}

    public function preFlush(Checkout $checkout, PreFlushEventArgs $args): void
    {
        /** @var EntityManagerInterface */
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Checkout) {
                continue;
            }

            $changes = $uow->getEntityChangeSet($entity);

            if (!isset($changes['status']) || !$entity->isCharged()) {
                continue;
            }

            $supports = $this->prepareSupports($entity);

            foreach ($supports as $support) {
                $em->persist($support);
                $uow->computeChangeSet($em->getClassMetadata(Support::class), $support);
            }
        }
    }

    /**
     * Create ProjectSupport for the given data.
     */
    private function createSupport(Project $project, Accounting $origin, array $transactions): Support
    {
        $projectSupport = $this->supportRepository->findOneBy([
            'project' => $project->getId(),
            'origin' => $origin->getId(),
        ]);

        if (!$projectSupport) {
            $projectSupport = new Support();
        }

        $projectSupport->setProject($project);
        $projectSupport->setOrigin($origin);
        $projectSupport->setAnonymous(false);

        foreach ($transactions as $transaction) {
            $projectSupport->addTransaction($transaction);
        }

        return $projectSupport;
    }

    /**
     * @return Support[]
     */
    public function prepareSupports(Checkout $checkout): array
    {
        /** @var Charge[] */
        $charges = $checkout->getCharges()->toArray();
        $origin = $checkout->getOrigin();

        $projects = [];
        $transactionsByProject = [];
        foreach ($charges as $charge) {
            $project = $charge->getTarget()->getProject();

            if (!$project) {
                continue;
            }

            $projectId = $project->getId();

            $projects[$projectId] = $project;
            $transactionsByProject[$projectId] = [
                ...$transactionsByProject[$projectId] ?? [],
                ...$charge->getTransactions(),
            ];
        }

        $supports = [];
        foreach ($transactionsByProject as $projectId => $transactions) {
            $supports[] = $this->createSupport($projects[$projectId], $origin, $transactions);
        }

        return $supports;
    }
}
