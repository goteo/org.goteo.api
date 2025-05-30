<?php

namespace App\State\Gateway;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Gateway\CheckoutApiResource;
use App\Dto\CheckoutUpdationDto;
use App\Entity\Gateway\Checkout;
use App\Gateway\GatewayLocator;
use App\Mapping\AutoMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CheckoutStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $innerProcessor,
        private GatewayLocator $gatewayLocator,
        private AutoMapper $autoMapper,
    ) {}

    /**
     * @param CheckoutApiResource|CheckoutUpdationDto $data
     *
     * @return Checkout
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof CheckoutUpdationDto) {
            $data = $this->autoMapper->map($uriVariables, $data);
        }

        $entity = $this->autoMapper->map($data, Checkout::class);
        $entity = $this->innerProcessor->process($entity, $operation, $uriVariables, $context);

        if ($data instanceof CheckoutApiResource) {
            $entity = $this->gatewayLocator->get($data->gateway->name)->process($entity);
            $entity = $this->innerProcessor->process($entity, $operation, $uriVariables, $context);
        }

        return $this->autoMapper->map($entity, CheckoutApiResource::class);
    }
}
