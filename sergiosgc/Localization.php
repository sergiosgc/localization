<?php
namespace sergiosgc;

class Localization {
    public static $localePath = null;
    public static $categories = [
        LC_COLLATE => true,
        LC_CTYPE => true, 
        LC_MONETARY => true,
        LC_NUMERIC => false, 
        LC_TIME => true, 
        LC_MESSAGES => true, 
        LC_ALL => false,
    ];
    public static $locale = null;
    public static $localeFallback = 'en_US';
    public static $httpParamName = 'ui:locale';
    public static $cookieName = 'sergiosgc_locale';
    public static $cookieValidity = 60 * 60 * 365;
    public static $gettextDomain = 'messages';
    public static $sources = [
        ['sergiosgc\Localization', 'fieldSource'],
        ['sergiosgc\Localization', 'cookieSource'],
        ['sergiosgc\Localization', 'httpParamSource'],
        ['sergiosgc\Localization', 'httpHeaderSource'],
        ['sergiosgc\Localization', 'fallbackFieldSource'],
    ];
    public static function fieldSource() { return static::$locale; }
    public static function fallbackFieldSource() { return static::$localeFallback; }
    public static function cookieSource() {
        return array_key_exists(static::$cookieName, $_COOKIE) ? $_COOKIE[static::$cookieName] : null;
    }
    public static function httpParamSource() { if (isset($_REQUEST[static::$httpParamName])) return $_REQUEST[static::$httpParamName]; }
    public static function httpHeaderSource() {
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
        $shortToLongLanguageMap = [];
        $dh = opendir(static::$localePath);
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..' || !is_dir(sprintf('%s/%s', static::$localePath, $file)) || strlen($file) < 3) continue;
            $lang = $file[0] . $file[1];
            $shortToLongLanguageMap[$lang] = $file;
        }
        closedir($dh);
        $dh = opendir(static::$localePath);
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..' || !is_dir(sprintf('%s/%s', static::$localePath, $file)) || strlen($file) != 2) continue;
            unset($shortToLongLanguageMap[$file]);
        }
        closedir($dh);
        $accepts = array_merge(
            array_reduce(
                array_keys($accepts),
                function($acc, $lang) use ($accepts, $shortToLongLanguageMap) {
                    $acc[explode('-', $lang, 2)[0]] = $accepts[$lang];
                    if (isset($shortToLongLanguageMap[$lang])) $acc[$shortToLongLanguageMap[$lang]] = $accepts[$lang];
                    return $acc;
                },
                []),
            $accepts);
        arsort($accepts, SORT_NUMERIC);
        foreach (array_keys($accepts) as $lang) if (is_dir(sprintf('%s/%s', static::$localePath, strtr($lang, ['-' => '_'])))) { return strtr($lang, ['-' => '_']); }
        return null;
    }
    public static function getLocale() {
        foreach (static::$sources as $source) if (is_callable($source)) {
            if ($locale = call_user_func($source)) return $locale;
        } else throw new \Exception('Locale source is not callable');
        throw new \Exception('No locale source produced a locale');
    }
    public static function setCookie() {
        $cookie = [];
        $cookie[] = sprintf('%s=%s', static::$cookieName, static::getLocale());
        $cookie[] = sprintf('Max-Age=%s', static::$cookieValidity);
        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on') $cookie[] = 'Secure';
        $cookie[] = 'Path=/';
        $cookie[] = 'SameSite=strict';
        header(sprintf('Set-Cookie: %s', implode('; ', $cookie)));
    }
    public static function bindLocale() {
        if (! static::$localePath) throw new \Exception('bindLocale() can only be called after setting sergiosgc\\Localization::$localePath');
        $locale = static::getLocale();
        foreach (static::$categories as $category => $enabled) if ($enabled) setlocale($category, $locale);
        bindtextdomain(static::$gettextDomain, static::$localePath);
        textdomain(static::$gettextDomain);
    }

}