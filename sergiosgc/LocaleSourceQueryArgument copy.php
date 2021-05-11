<?php declare(strict_types=1);
namespace sergiosgc;

class LocaleSourceQueryArgument implements ILocaleSource {
    public string $argumentName = 'lang';
    public function __construct(string $argumentName = 'lang') {
        $this->argumentName = $argumentName;
    }
    public function getLocale(): ?string {
        if (isset($_REQUEST[$this->argumentName]) && preg_match('/^[a-zA-Z]{2}(?:_[a-zA-Z]{2})?$/')) return $_REQUEST[$this->argumentName];
        return null;
    }
}