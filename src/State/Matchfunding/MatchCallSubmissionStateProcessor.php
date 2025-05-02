<?php

namespace App\State\Matchfunding;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Matchfunding\MatchCallSubmissionApiResource;
use App\Dto\Matchfunding\MatchCallSubmissionCreationDto;
use App\Entity\Matchfunding\MatchCallSubmission;
use App\Mapping\AutoMapper;

class MatchCallSubmissionStateProcessor implements ProcessorInterface
{
    public function __construct(
        private AutoMapper $autoMapper
    ) {}

    /**
     * @param MatchCallSubmissionApiResource|MatchCallSubmissionCreationDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        dd($data);
        $entity = $this->autoMapper->map($data, MatchCallSubmission::class);
    }
}
