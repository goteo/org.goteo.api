<?php

namespace App\State\Gateway;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Gateway\ChargeApiResource;
use App\ApiResource\Money as ApiResourceMoney;
use App\Dto\Gateway\ChargeGetCollectionDto;
use App\Entity\Gateway\Charge;
use App\Entity\Money;
use App\Library\Economy\MoneyService;
use App\Mapping\AutoMapper;
use App\Service\Gateway\ChargeService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ChargeStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)]
        private ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)]
        private ProviderInterface $itemProvider,
        private AutoMapper $autoMapper,
        private ChargeService $chargeService,
        private MoneyService $moneyService,
        private string $defaultCurrency = 'EUR',
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable|object
    {
        if ($operation instanceof GetCollection) {
            $collection = $this->collectionProvider->provide($operation, $uriVariables, $context);

            $currency = $this->defaultCurrency;
            $totalContributions = new Money(0, $currency);
            $totalTips = new Money(0, $currency);

            foreach ($collection as $charge) {
                if (!$charge instanceof Charge) {
                    continue;
                }
                /** @var ChargeApiResource */
                $charge = $this->autoMapper->map($charge, ChargeApiResource::class);

                $chargeMoney = $charge->money;

                $target = $charge->target;
                if ($target->project !== null) {
                    $totalContributions = $this->moneyService->add($chargeMoney, $totalContributions);
                } elseif ($target->tipjar !== null) {
                    $totalTips = $this->moneyService->add($chargeMoney, $totalTips);
                }
            }

            /** @var ApiResourceMoney */
            $contributions = $this->autoMapper->map($totalContributions, ApiResourceMoney::class);
            /** @var ApiResourceMoney */
            $tips = $this->autoMapper->map($totalTips, ApiResourceMoney::class);

            return new ChargeGetCollectionDto($contributions, $tips, $collection);
        }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }
}
