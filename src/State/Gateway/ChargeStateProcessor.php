<?php

namespace App\State\Gateway;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Gateway\ChargeApiResource;
use App\Dto\Gateway\ChargeUpdationDto;
use App\Entity\Gateway\Charge;
use App\Mapping\AutoMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ChargeStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $innerProcessor,
        #[Autowire(service: AutoMapper::class)]
        private AutoMapper $autoMapper,
    ) {}

    /**
     * @param ChargeApiResource|ChargeUpdationDto $data
     *
     * @return Charge
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof ChargeUpdationDto) {
            $data = $this->autoMapper->map($uriVariables, $data);
        }

        $entity = $this->autoMapper->map($data, Charge::class);
        $entity = $this->innerProcessor->process($entity, $operation, $uriVariables, $context);

        return $this->autoMapper->map($entity, ChargeApiResource::class);
    }
}
