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
    public static function asHandle(string $value, int $min = 4, int $max = 32): string
    {
        $nval = \strtolower($value);

        // If email remove provider
        $nval = \preg_replace('/@.*[\.-](com|net|org|es)/', '', $nval);

        // Replace known subs
        $nval = \str_replace(['&', '@', 'ñ', '=', '≠'], ['and', 'at', 'nn', 'eq', 'nq'], $nval);

        // Only lowercase a-z, numbers, underscore and middle dots in user handles
        $nval = \preg_replace('/[^a-z0-9_.]|^\.|\.$/', '_', \strtolower($nval));

        $nval = \str_pad($nval, $min, \sprintf('#%s', \hash('md5', $value)));
        $nval = \substr($nval, 0, $max);

        if (strlen(str_replace('_', '', $nval)) < 1) {
            throw new \Exception(\sprintf('The string \'%s\' could not be safely normalized for a handle', $value));
        }

        return $nval;
    }
}
