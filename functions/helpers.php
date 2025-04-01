<?php

if (!function_exists('array_dot')) {

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param array $array The array to flatten.
     * @param string $rootKey The base key prefix (used internally for recursion).
     * @return array The flattened array with dot notation keys.
     */
    function array_dot(array $array, string $rootKey = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $key = strval($key);
            $key = $rootKey !== '' ? ($rootKey . '.' . $key) : $key;
            if (is_array($value)) {
                $result = $result + array_dot($value, $key);
                continue;
            }
            $result[$key] = $value;
        }

        return $result;
    }
}
if (!function_exists('str_starts_with')) {

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    function str_starts_with(string $haystack, string $needle): bool
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}
