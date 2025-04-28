<?php

namespace App\Matchfunding\Rule;

use App\ApiResource\Matchfunding\RuleApiResource;
use App\Entity\Matchfunding\MatchStrategy;
use Symfony\Component\VarExporter\Exception\ClassNotFoundException;

class RuleLocator
{
    /** @var array<string, RuleInterface> */
    private array $rulesByName = [];

    public function __construct(
        iterable $rules,
    ) {
        foreach ($rules as $rule) {
            $name = RuleApiResource::from($rule)->name;

            $this->rulesByName[$name] = $rule;
        }
    }

    /**
     * @return array<string, RuleInterface>
     */
    public function getAll(): array
    {
        return $this->rulesByName;
    }

    /**
     * @return array<string, RuleInterface>
     */
    public function getFrom(MatchStrategy $strategy): array
    {
        $rules = $strategy->getRuleNames();

        return \array_filter(
            $this->rulesByName,
            fn($v, $k) => \in_array($k, $rules),
            ARRAY_FILTER_USE_BOTH
        );
    }

    public function getOne(string $className): ?RuleInterface
    {
        if (!array_key_exists($className, $this->rulesByName)) {
            throw new ClassNotFoundException($className);
        }

        return $this->rulesByName[$className];
    }
}
