<?php

namespace App\Factory\Gateway;

use App\Entity\Gateway\Charge;
use App\Entity\Money;
use App\Entity\Project\Project;
use App\Factory\Project\ProjectFactory;
use App\Gateway\ChargeType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Project>
 */
final class ChargeFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    public static function class(): string
    {
        return Charge::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $money = new Money(1000, 'USD');

        return [
            'type' => ChargeType::Single,
            'title' => 'Generic Title',
            'money' => $money,
            'description' => 'Test charge',
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this->beforeInstantiate(function (array $parameters) {
            if (!isset($parameters['target'])) {
                $project = ProjectFactory::createOne();
                $parameters['target'] = $project->getAccounting();
            }

            return $parameters;
        });
    }
}
