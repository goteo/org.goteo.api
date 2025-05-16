<?php

namespace App\Service;

class VersionedResourceService
{
    public const VERSIONED_RESOURCES_DIR = 'versioned_resources';
    public const VERSIONED_RESOURCE_NAMES_LOCK = 'versioned_resource_names_compiled.lock';

    private static function getCompileDir(): string
    {
        return sprintf(
            '%s%svar%s%s',
            \dirname(__DIR__, 2),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            self::VERSIONED_RESOURCES_DIR,
        );
    }

    private static function getNamesLockFile(): string
    {
        return sprintf(
            '%s%s%s',
            self::getCompileDir(),
            DIRECTORY_SEPARATOR,
            self::VERSIONED_RESOURCE_NAMES_LOCK
        );
    }

    /**
     * Generates a directory for the service files.
     */
    public function makeCompileDir()
    {
        $compileDir = self::getCompileDir();

        if (!\is_dir($compileDir)) {
            \mkdir($compileDir, 0777, true);
        }
    }

    /**
     * Stores the given names in lock file.
     *
     * @param array<string, string> $names
     */
    public function compileNames(array $names)
    {
        $this->makeCompileDir();

        \file_put_contents(
            self::getNamesLockFile(),
            json_encode($names)
        );
    }

    /**
     * @return array<string, string> List of the versioned resource names
     *
     * @see compileNames()
     */
    public static function getNames(): array
    {
        return json_decode(\file_get_contents(self::getNamesLockFile()), true);
    }

    public static function getEntityFromResource(string $resourceShortName): string
    {
        $names = self::getNames();

        if (\array_key_exists($resourceShortName, $names)) {
            return $names[$resourceShortName];
        }

        throw new \Exception(\sprintf(
            "The resource shortName '%s' does not match to a known versioned resource",
            $resourceShortName
        ));
    }

    public static function getResourceFromEntity(string $entityClass): string
    {
        foreach (self::getNames() as $shortName => $entityFqcl) {
            if ($entityFqcl === $entityClass) {
                return $shortName;
            }
        }

        throw new \Exception(\sprintf(
            "The entity class name '%s' does not match to a known versioned resource",
            $entityClass
        ));
    }
}
