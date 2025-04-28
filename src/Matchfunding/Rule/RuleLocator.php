<?php

namespace App\Matchfunding\Rule;

use App\Entity\Matchfunding\MatchStrategy;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

class RuleLocator
{
    /** @var array<string, RuleInterface> */
    private array $rulesByClassName = [];

    public function __construct(
        iterable $rules,
    ) {
        foreach ($rules as $rule) {
            $this->rulesByClassName[$rule::class] = $rule;
        }
    }

    /**
     * @return array<string, RuleInterface>
     */
    public function getAll(): array
    {
        return $this->rulesByClassName;
    }

    /**
     * @return array<string, RuleInterface>
     */
    public function getFrom(MatchStrategy $strategy): array
    {
        $rules = $strategy->getRuleClasses();

        return \array_filter(
            $this->rulesByClassName,
            fn($r, $k) => \in_array($k, $rules)
        );
    }

    public function getOne(string $className): ?RuleInterface
    {
        if (!array_key_exists($className, $this->rulesByClassName)) {
            throw new ClassNotFoundException($className);
        }

        return $this->rulesByClassName[$className];
    }
}
