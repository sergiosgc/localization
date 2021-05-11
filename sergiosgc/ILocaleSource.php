<?php declare(strict_types=1);
namespace sergiosgc;

interface ILocaleSource {
    public function getLocale(): ?string;
}
