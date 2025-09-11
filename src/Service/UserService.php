<?php

namespace App\Service;

class UserService
{
    /**
     * Normalizes a string into a handle-valid string.
     *
     * @return string A handle-valid string
     *
     * @throws \Exception If the given value could not be normalized into a compliant handle string
     */
    public static function asHandle(string $value): string
    {
        // If email remove provider
        if (\str_contains($value, '@') && \preg_match('/^[^@]+/', $value, $matches)) {
            $value = $matches[0];
        }

        // Only lowercase a-z, numbers, underscore and middle dots in user handles
        $value = \preg_replace('/[^a-z0-9_.]|^\.|\.$/', '_', \strtolower($value));

        // Min length 4
        $value = \str_pad($value, 4, '_');

        // Max length 30
        $value = \substr($value, 0, 30);

        if (strlen(str_replace('_', '', $value)) < 1) {
            throw new \Exception(\sprintf('The string \'%s\' could not be safely normalized for a handle', $value));
        }

        return $value;
    }
}
