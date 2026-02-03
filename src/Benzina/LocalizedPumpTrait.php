<?php

namespace App\Benzina;

use App\Entity\LocalizedInterface;
use Gedmo\Translatable\Entity\Translation;
use Goteo\Benzina\Pump\ContextAwareTrait;
use Goteo\Benzina\Pump\DoctrinePumpTrait;

trait LocalizedPumpTrait
{
    use ContextAwareTrait;
    use DoctrinePumpTrait;

    /** @var array<class-string, string[]> */
    private static array $translatableFieldsCache = [];

    public function localize(
        LocalizedInterface $entity,
        array $localizations = [],
        array $context = [],
        array $fieldCallbacks = [],
    ): void {
        if ($this->isDryRun($context)) {
            return;
        }

        $em = $this->getEntityManager();
        $meta = $em->getClassMetadata($entity::class);
        $translations = $em->getRepository(Translation::class);

        $translatableFields = $this->getTranslatableFields($meta);

        foreach ($localizations as $localization) {
            $locale = $localization['lang'] ?? null;
            if (!$locale) {
                continue;
            }

            $entity->addLocale($locale);

            foreach ($translatableFields as $field) {
                if (isset($fieldCallbacks[$field]) && is_callable($fieldCallbacks[$field])) {
                    $value = $fieldCallbacks[$field]($localization);
                } else {
                    $value = $meta->getFieldValue($entity, $field);
                }

                if ($value !== null) {
                    $translations->translate($entity, $field, $locale, $value);
                }
            }
        }

        if ($this->preventFlushAndClear) {
            return;
        }

        $em->flush();
        $em->clear();
    }

    /**
     * Get all #[Gedmo\Translatable] fields for this entity.
     *
     * @return string[]
     */
    private function getTranslatableFields($meta): array
    {
        $className = $meta->getName();

        if (isset(self::$translatableFieldsCache[$className])) {
            return self::$translatableFieldsCache[$className];
        }

        $reflection = $meta->getReflectionClass();
        $fields = [];

        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(\Gedmo\Mapping\Annotation\Translatable::class);
            if (!empty($attrs)) {
                $fields[] = $property->getName();
            }
        }

        self::$translatableFieldsCache[$className] = $fields;

        return $fields;
    }
}
