<?php declare(strict_types=1);
namespace sergiosgc;

class LocaleSourceFallback implements ILocaleSource {
    public string $fallback = 'en_US';
    public function __construct(string $fallback = 'en_US') {
        $this->fallback = $fallback;
    }
    public function getLocale(): ?string {
        return $this->fallback;
    }
}
