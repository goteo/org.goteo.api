<?php

namespace App\Validator;

use App\Dto\Matchfunding\MatchCallSubmissionCreationDto;
use App\Repository\Matchfunding\MatchCallSubmissionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class SingleProjectSubmissionPerCallValidator extends ConstraintValidator
{
    public function __construct(
        private MatchCallSubmissionRepository $matchCallSubmissionRepository
    ) {}

    /**
     * @param MatchCallSubmissionCreationDto $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $submission = $this->matchCallSubmissionRepository->findOneBy([
            'call' => $value->call->id,
            'project' => $value->project->id
        ]);

        if ($submission === null) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
