<?php

namespace Test\Depo\RequestKit;

use Depo\RequestKit\Locale;
use Depo\UniTester\TestCase;

class LocaleTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset locale to default before each test group
        Locale::setLocale('en');
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        Locale::setLocale('en');
    }

    protected function execute(): void
    {
        $this->testGetDefaultEnglishMessage();
        $this->testSetLocaleToFrench();
        $this->testMessageWithParameters();
        $this->testFallbackToEnglish();
        $this->testAddNewLanguage();
    }

    private function testGetDefaultEnglishMessage()
    {
        $message = Locale::get('error.required');
        $this->assertStrictEquals('Value is required, but got null or empty string.', $message);
    }

    private function testSetLocaleToFrench()
    {
        Locale::setLocale('fr');
        $message = Locale::get('error.required');
        $this->assertStrictEquals('La valeur est requise.', $message);
    }

    private function testMessageWithParameters()
    {
        // English
        Locale::setLocale('en');
        $messageEn = Locale::get('error.string.min_length', ['min' => 5]);
        $this->assertStrictEquals('Value must be at least 5 characters long.', $messageEn);

        // French
        Locale::setLocale('fr');
        $messageFr = Locale::get('error.string.min_length', ['min' => 10]);
        $this->assertStrictEquals('La valeur doit contenir au moins 10 caractères.', $messageFr);
    }

    private function testFallbackToEnglish()
    {
        // Add a message only in English
        Locale::addMessages('en', ['error.test' => 'This is a test.']);
        
        // Set locale to French
        Locale::setLocale('fr');

        // The key 'error.test' does not exist in French, so it should fall back to English.
        $message = Locale::get('error.test');
        $this->assertStrictEquals('This is a test.', $message);
    }

    private function testAddNewLanguage()
    {
        $spanishMessages = [
            'error' => [
                'required' => 'El valor es requerido.'
            ]
        ];
        Locale::addMessages('es', $spanishMessages);
        Locale::setLocale('es');

        $message = Locale::get('error.required');
        $this->assertStrictEquals('El valor es requerido.', $message);

        // Check that it falls back to English for non-translated messages
        $messageEquals = Locale::get('error.equals');
        $this->assertStrictEquals('The value does not match the expected value.', $messageEquals);
    }
}
