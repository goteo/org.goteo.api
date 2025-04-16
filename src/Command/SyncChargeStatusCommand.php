<?php

namespace App\Command;

use App\Gateway\ChargeStatus;
use App\Repository\Gateway\ChargeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sync-charge-status',
    description: 'Sync the Charge.status field to match its Checkout.status'
)]
class SyncChargeStatusCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private ChargeRepository $chargeRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $charges = $this->chargeRepository->findAll();
        $count = 0;

        $validChargeStatusValues = array_map(
            fn(ChargeStatus $c) => $c->value,
            ChargeStatus::cases()
        );

        foreach ($charges as $charge) {
            $checkout = $charge->getCheckout();
            $checkoutStatus = $checkout?->getStatus();

            if ($checkoutStatus && in_array(
                $checkoutStatus->value,
                $validChargeStatusValues,
                true
            )) {
                $newStatus = ChargeStatus::from($checkoutStatus->value);

                if ($charge->getStatus() !== $newStatus) {
                    $charge->setStatus($newStatus);
                    ++$count;
                }
            }
        }

        $this->em->flush();

        $output->writeln(sprintf('✔️  Updated %d charge(s) with correct status.', $count));

        return Command::SUCCESS;
    }
}
