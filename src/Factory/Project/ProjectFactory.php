<?php

namespace App\Factory\Project;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectCategory;
use App\Entity\Project\ProjectDeadline;
use App\Entity\Project\ProjectStatus;
use App\Entity\Territory;
use App\Entity\User\User;
use App\Factory\User\UserFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Project>
 */
final class ProjectFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    public static function class(): string
    {
        return Project::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return $this->defaultsOptimized();
    }

    protected static function defaultsOptimized(): array|callable
    {
        return [
            'category' => ProjectCategory::Design,
            'deadline' => ProjectDeadline::Minimum,
            'description' => '',
            'territory' => new Territory('ES'),
            'owner' => new User(),
            'status' => ProjectStatus::InEditing,
            'subtitle' => 'Subtitle',
            'title' => 'Title',
        ];
    }

    protected static function defaultsFull(): array|callable
    {
        return [
            'category' => self::faker()->randomElement(ProjectCategory::cases()),
            'deadline' => self::faker()->randomElement(ProjectDeadline::cases()),
            'description' => self::faker()->text(),
            'territory' => new Territory('ES'),
            'owner' => UserFactory::createWithMode(),
            'status' => self::faker()->randomElement(ProjectStatus::cases()),
            'subtitle' => self::faker()->text(255),
            'title' => self::faker()->text(255),
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

    /**
     * Create projects with optimized or complete values ​​according to the parameter.
     */
    public static function createManyWithMode(int $count, array $overrides = [], bool $optimized = false): void
    {
        $defaults = $optimized ? self::defaultsOptimized() : self::defaultsFull();
        self::createMany($count, array_merge($defaults, $overrides));
    }
}
