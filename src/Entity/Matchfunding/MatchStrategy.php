<?php

namespace App\Entity\Matchfunding;

use App\Entity\Money;
use App\Mapping\Provider\MatchStrategyMapProvider;
use App\Repository\Matchfunding\MatchStrategyRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[MapProvider(MatchStrategyMapProvider::class)]
#[ORM\Entity(repositoryClass: MatchStrategyRepository::class)]
class MatchStrategy
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'strategy', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?MatchCall $call = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $ruleClasses = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $formulaName = null;

    #[ORM\Embedded(class: Money::class)]
    private ?object $limit = null;

    #[ORM\Column(nullable: true)]
    private ?float $factor = null;

    #[ORM\Column(enumType: MatchAgainst::class)]
    private ?MatchAgainst $against = MatchAgainst::DEFAULT;

    public static function of(MatchCall $call): MatchStrategy
    {
        $strategy = new MatchStrategy();
        $strategy->setCall($call);

        return $strategy;
    }

    public function getCall(): ?MatchCall
    {
        return $this->call;
    }

    public function setCall(MatchCall $call): static
    {
        $this->call = $call;

        return $this;
    }

    public function getRuleClasses(): array
    {
        return $this->ruleClasses;
    }

    public function setRuleClasses(array $ruleClasses): static
    {
        $this->ruleClasses = $ruleClasses;

        return $this;
    }

    public function getFormulaName(): ?string
    {
        return $this->formulaName;
    }

    public function setFormulaName(string $formulaName): static
    {
        $this->formulaName = $formulaName;

        return $this;
    }

    public function getLimit(): ?Money
    {
        return $this->limit;
    }

    public function setLimit(Money $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function getFactor(): ?float
    {
        return $this->factor;
    }

    public function setFactor(float $factor): static
    {
        $this->factor = $factor;

        return $this;
    }

    public function getAgainst(): ?MatchAgainst
    {
        return $this->against;
    }

    public function setAgainst(MatchAgainst $against): static
    {
        $this->against = $against;

        return $this;
    }
}
