<?php

namespace App\Entity\Matchfunding;

use App\Entity\EmbeddableMoney as Money;
use App\Mapping\Provider\EntityMapProvider;
use App\Matchfunding\Formula\MultiplicationFormula;
use App\Repository\Matchfunding\MatchStrategyRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[MapProvider(EntityMapProvider::class)]
#[ORM\Entity(repositoryClass: MatchStrategyRepository::class)]
class MatchStrategy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'strategies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MatchCall $call = null;

    #[ORM\Column]
    private ?int $ranking = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $ruleNames = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $formulaName;

    #[ORM\Embedded(class: Money::class)]
    private ?object $limit = null;

    #[ORM\Column(nullable: true)]
    private ?float $factor = 1.0;

    #[ORM\Column(enumType: MatchAgainst::class)]
    private ?MatchAgainst $against = MatchAgainst::DEFAULT;

    public static function of(
        MatchCall $call,
        ?string $formulaName = null,
    ): MatchStrategy {
        $strategy = new MatchStrategy();

        $strategy->setCall($call);
        $strategy->setFormulaName($formulaName ?? MultiplicationFormula::getName());
        $strategy->setLimit(new Money(
            2147483647,
            $call->getAccounting()->getCurrency()
        ));

        return $strategy;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCall(): ?MatchCall
    {
        return $this->call;
    }

    public function setCall(?MatchCall $call): static
    {
        $this->call = $call;

        return $this;
    }

    public function getRanking(): ?int
    {
        return $this->ranking;
    }

    public function setRanking(int $ranking): static
    {
        $this->ranking = $ranking;

        return $this;
    }

    public function getRuleNames(): array
    {
        return $this->ruleNames;
    }

    public function setRuleNames(array $ruleNames): static
    {
        $this->ruleNames = $ruleNames;

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
