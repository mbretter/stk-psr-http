<?php

namespace Stk\Service;


interface AcceptLanguageInterface
{
    public function setAcceptLanguage(string $language = null): void;

    /**
     * @param array<int, array<int, string|float>> $languages
     * @return void
     */
    public function setAcceptLanguages(array $languages = []): void;
}
