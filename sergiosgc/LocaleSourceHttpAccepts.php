<?php declare(strict_types=1);
namespace sergiosgc;

class LocaleSourceHttpAccepts implements ILocaleSource {
    public ?array $supported = null;
    public function __construct($supported = null) {
        $this->supported = $supported;
    }
    public function getLocale(): ?string {
        if (!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) return null;
        $accepts = array_reduce(
            explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']),
            function($acc, $lang) {
                if (false === strpos($lang, ';q=')) {
                    $acc[trim($lang)] = 1;
                    return $acc;
                }
                list($lang, $weight) = explode(';q=', $lang, 2);
                if ( 0 != (float) $weight) {
                    $acc[trim($lang)] = (float) $weight;
                    return $acc;
                }
                $acc[trim($lang)] = 1;
                return $acc;
            },
            []);
        $accepts = array_reduce(array_keys($accepts), function($result, $key) use ($accepts) { $result[strtr($key, '-', '_')] = $accepts[$key]; return $result; }, []);
        arsort($accepts);
        
        if (count($accepts) == 0) return null;
        if (is_null($this->supported)) return array_keys($accepts)[0];
        $this->supported = array_map('\strtolower', $this->supported);
        foreach ($accepts as $locale => $priority) if (in_array(explode('_', $locale, 2)[0], $this->supported)) return $locale;
        return null;
    }
}
