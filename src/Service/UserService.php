<?php

namespace App\Service;

class UserService
{
    /**
     * Normalizes a string into a handle-valid string.
     *
     * @param string $value The input string to be normalized into a valid handle
     * @param int    $min   The shortest possible length of the output string
     * @param int    $max   The longest possible length of the output string
     *
     * @return string A handle-valid string
     *
     * @throws \Exception If the given value could not be normalized into a compliant handle string
     */
    public static function asHandle(string $value, int $min = 4, int $max = 30): string
    {
        $value = \strtolower($value);

        // If email remove provider
        $value = \preg_replace('/@.*[\.-](com|net|org|es)/', '', $value);

        // Replace known subs
        $value = \str_replace(['&', '@', 'ñ', '=', '≠'], ['and', 'at', 'nn', 'eq', 'nq'], $value);

        // Only lowercase a-z, numbers, underscore and middle dots in user handles
        $value = \preg_replace('/[^a-z0-9_.]|^\.|\.$/', '_', \strtolower($value));

        $value = \str_pad($value, $min, \hash('md5', $value));
        $value = \substr($value, 0, $max);

        if (strlen(str_replace('_', '', $value)) < 1) {
            throw new \Exception(\sprintf('The string \'%s\' could not be safely normalized for a handle', $value));
        }

        return $value;
    }
}
