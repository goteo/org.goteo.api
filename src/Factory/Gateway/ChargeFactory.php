<?php

namespace App\Factory\Gateway;

use App\Entity\Gateway\Charge;
use App\Entity\Money;
use App\Entity\Project\Project;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserFactory;
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
        $projectUser = UserFactory::createOne([
            'handle' => 'project_user_charge',
            'email' => 'projectuser@charge.com',
        ]);
        $project = ProjectFactory::createOne(['owner' => $projectUser]);
        $target = $project->getAccounting();

        $money = new Money(1000, 'USD');

        return [
            'type' => ChargeType::Single,
            'title' => 'Generic Title',
            'target' => $target,
            'money' => $money,
            'description' => 'Test charge',
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Project $project): void {})
        ;
    }
}
