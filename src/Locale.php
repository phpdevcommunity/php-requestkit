<?php

namespace Depo\RequestKit;

final class Locale
{
    private static string $currentLocale = 'en';
    private static array $messages = [];

    /**
     * Sets the active locale for error messages.
     * @param string $locale e.g., 'en', 'fr'
     */
    public static function setLocale(string $locale): void
    {
        self::$currentLocale = $locale;
    }

    /**
     * Adds a set of translation messages for a specific locale.
     * @param string $locale
     * @param array $messages
     */
    public static function addMessages(string $locale, array $messages): void
    {
        // array_replace_recursive is used to merge nested message arrays
        self::$messages[$locale] = array_replace_recursive(self::$messages[$locale] ?? [], $messages);
    }

    /**
     * Gets a translated message by its key.
     *
     * @param string $key The key of the message (e.g., 'error.required').
     * @param array $params Placeholders to replace in the message.
     * @return string
     */
    public static function get(string $key, array $params = []): string
    {
        if (empty(self::$messages['en'])) {
            self::initializeDefaultMessages('en');
        }
        if (self::$currentLocale === 'fr' && empty(self::$messages['fr'])) {
            self::initializeDefaultMessages('fr');
        }

        $messages = array_dot(self::$messages[self::$currentLocale]);
        $message = $messages[$key] ?? null;
        if ($message == null) {
            $messages = array_dot( self::$messages['en']);
            $message = $messages[$key] ?? $key;
        }
        unset($messages);
        foreach ($params as $param => $value) {
            $message = str_replace('{' . $param . '}', (string) $value, $message);
        }

        return $message;
    }

    /**
     * Initializes the default English messages.
     */
    private static function initializeDefaultMessages(string $locale): void
    {
        if ($locale === 'en') {
            self::addMessages('en', [
                'error' => [
                    'required' => 'Value is required, but got null or empty string.',
                    'equals' => 'The value does not match the expected value.',
                    'csrf' => 'Invalid CSRF token.',
                    'json' => 'Invalid JSON input: {error}',
                    'type' => [
                        'string' => 'Value must be a string, got: {type}.',
                        'int' => 'Value must be an integer, got: {type}.',
                        'float' => 'Value must be a float, got: {type}.',
                        'bool' => 'Value must be a boolean, got: {type}.',
                        'numeric' => 'Value must be numeric, got: {type}.',
                        'date' => 'Value must be a valid date.',
                        'datetime' => 'Value must be a valid datetime.',
                        'array' => 'Value must be an array.',
                    ],
                    'string' => [
                        'min_length' => 'Value must be at least {min} characters long.',
                        'max_length' => 'Value cannot be longer than {max} characters.',
                        'email' => 'Value must be a valid email address.',
                        'allowed' => 'Value is not allowed, allowed values are: {allowed}.',
                    ],
                    'int' => [
                        'min' => 'Value must be at least {min}.',
                        'max' => 'Value must be no more than {max}.',
                    ],
                    'array' => [
                        'min_items' => 'Value must have at least {min} item(s).',
                        'max_items' => 'Value must have at most {max} item(s).',
                        'integer_keys' => 'All keys must be integers.',
                    ],
                    'map' => [
                        'string_key' => 'Key "{key}" must be a string, got {type}.',
                    ]
                ],
            ]);
        }elseif ($locale === 'fr') {
            self::addMessages('fr', [
                'error' => [
                    'required' => 'La valeur est requise.',
                    'equals' => 'La valeur ne correspond pas à la valeur attendue.',
                    'csrf' => 'Jeton CSRF invalide.',
                    'json' => 'Entrée JSON invalide : {error}',
                    'type' => [
                        'string' => 'La valeur doit être une chaîne de caractères, reçu : {type}.',
                        'int' => 'La valeur doit être un entier, reçu : {type}.',
                        'float' => 'La valeur doit être un flottant, reçu : {type}.',
                        'bool' => 'La valeur doit être un booléen, reçu : {type}.',
                        'numeric' => 'La valeur doit être numérique, reçu : {type}.',
                        'date' => 'La valeur doit être une date valide.',
                        'datetime' => 'La valeur doit être une date et heure valide.',
                        'array' => 'La valeur doit être un tableau.',
                    ],
                    'string' => [
                        'min_length' => 'La valeur doit contenir au moins {min} caractères.',
                        'max_length' => 'La valeur ne peut pas dépasser {max} caractères.',
                        'email' => 'La valeur doit être une adresse e-mail valide.',
                        'allowed' => 'La valeur n\'est pas autorisée. Les valeurs autorisées sont : {allowed}.',
                    ],
                    'int' => [
                        'min' => 'La valeur doit être d\'au moins {min}.',
                        'max' => 'La valeur ne doit pas dépasser {max}.',
                    ],
                    'array' => [
                        'min_items' => 'La valeur doit contenir au moins {min} élément(s).',
                        'max_items' => 'La valeur ne doit pas dépasser {max} élément(s).',
                        'integer_keys' => 'Toutes les clés doivent être des entiers.',
                    ],
                    'map' => [
                        'string_key' => 'La clé "{key}" doit être une chaîne de caractères, reçu {type}.',
                    ]
                ],
            ]);
        }

    }
}
