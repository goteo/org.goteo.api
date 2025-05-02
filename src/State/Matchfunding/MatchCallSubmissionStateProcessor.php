<?php

namespace App\State\Matchfunding;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Matchfunding\MatchCallSubmissionApiResource;
use App\Entity\Matchfunding\MatchCallSubmission;
use App\Mapping\AutoMapper;

class MatchCallSubmissionStateProcessor implements ProcessorInterface
{
    public function __construct(
        private AutoMapper $autoMapper
    ) {}

    /**
     * @param MatchCallSubmissionApiResource $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $entity = $this->autoMapper->map($data, MatchCallSubmission::class);

        dd($entity);
    }
}
