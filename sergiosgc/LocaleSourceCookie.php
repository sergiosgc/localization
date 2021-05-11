<?php declare(strict_types=1);
namespace sergiosgc;

class LocaleSourceCookie implements ILocaleSource {
    public string $cookieName = 'lang';
    public function __construct(string $cookieName = 'lang') {
        $this->cookieName = $cookieName;
    }
    public function getLocale(): ?string {
        if (isset($_COOKIE[$this->cookieName]) && preg_match('/^[a-zA-Z]{2}(?:_[a-zA-Z]{2})?$/')) return $_COOKIE[$this->cookieName];
        return null;
    }
}