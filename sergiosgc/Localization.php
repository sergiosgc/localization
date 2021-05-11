<?php
namespace sergiosgc;

class Localization {
    public array $localeSources = [];
    public static ?Localization $singleton = null;
    public function __construct($localeSources = null) {
        if (is_null($localeSources)) {
            $localeSources = [ new LocaleSourceQueryArgument(), new LocaleSourceCookie(),new LocaleSourceHttpAccepts(), new LocaleSourceFallback() ];
        }
        $this->localeSources = $localeSources;
    }
    public static function singleton() { return static::$singleton ?? (static::$singleton = new Localization()); }
    public function prependSource(ILocaleSource $source) { array_unshift($this->localeSources, $source); }
    public function prependSources(array $sources) { $this->localeSources = array_values(array_merge($sources, $this->localeSources)); }
    public function appendSource(ILocaleSource $source) { array_push($this->localeSources, $source); }
    public function appendSources(array $sources) { $this->localeSources = array_values(array_merge($this->localeSources, $sources)); }
    public function getLocale() {
        $result = array_reduce( 
            $this->localeSources, 
            function(?string $result, ILocaleSource $localeSource) { return $result ?? $localeSource->getLocale(); },
            null
        );
        if (is_null($result)) throw new \Exception('No locale source produced a locale');
        return $result;
    }
    public function setLocale($locale = null, $categories = [ LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_NUMERIC, LC_TIME, LC_MESSAGES, LC_ALL ]) {
        $locale = $locale ?? $this->getLocale();
        foreach ($categories as $category) \setlocale($category, $locale);
    }
}