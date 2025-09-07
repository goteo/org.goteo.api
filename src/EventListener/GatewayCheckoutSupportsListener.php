<?php

namespace App\EventListener;

use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
use App\Entity\EmbeddableMoney;
use App\Entity\Gateway\Charge;
use App\Entity\Gateway\Checkout;
use App\Entity\Project\Project;
use App\Entity\Project\Support;
use App\Money\Money;
use App\Money\MoneyService;
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
        private MoneyService $moneyService,
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
            $supports[] = $this->getSupport($projects[$projectId], $origin, $transactions);
        }

        return $supports;
    }

    /**
     * Create ProjectSupport for the given data.
     * 
     * @param Transaction[] $transactions
     */
    private function getSupport(Project $project, Accounting $origin, array $transactions): Support
    {
        /** @var Support|null */
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

        $money = new Money(0, $project->getAccounting()->getCurrency());
        foreach ($transactions as $transaction) {
            $money = $this->moneyService->add($transaction->getMoney(), $money);

            $projectSupport->addTransaction($transaction);
        }

        $projectSupport->setMoney(EmbeddableMoney::of($money));

        return $projectSupport;
    }
}
