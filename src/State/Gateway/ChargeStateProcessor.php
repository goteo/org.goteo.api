<?php

namespace App\State\Gateway;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Gateway\ChargeApiResource;
use App\Dto\Gateway\ChargeUpdateDto;
use App\Entity\Gateway\Charge;
use App\Mapping\AutoMapper;
use App\State\EntityStateProcessor;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ChargeStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityStateProcessor $entityStateProcessor,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $innerProcessor,
        #[Autowire(service: AutoMapper::class)]
        private AutoMapper $autoMapper,
    ) {}

    /**
     * @param ChargeUpdateDto $data
     * @param array{id: int}  $uriVariables
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ChargeApiResource
    {
        // if ($data instanceof ChargeUpdateDto) {
        //     $data = $this->autoMapper->map($uriVariables, $data);

        //     /** @var Charge */
        //     $charge = $this->autoMapper->map($data, Charge::class);
        // }

        // $charge  = $this->entityStateProcessor->process($charge, $operation, $uriVariables, $context);
        $charge = $this->entityStateProcessor->process($data, $operation, $uriVariables, $context);

        if ($charge === null) {
            return null;
        }

        return $this->autoMapper->map($charge, ChargeApiResource::class);
    }
}
