<?php

namespace Stk\Service;


interface AcceptLanguageInterface
{
    public function setAcceptLanguage(string $language = null);

    public function setAcceptLanguages(array $languages = []);
}
