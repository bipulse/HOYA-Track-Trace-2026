<?php

/**
 * Lightweight i18n helpers.
 *
 * Goals:
 * - Normalize locale values (DB vs HTML)
 * - Provide file-based translation fallback (so templates can be fully tokenized)
 * - Avoid corrupting already-UTF8 strings (legacy code used utf8_encode)
 */

function tnt_is_valid_utf8(string $value): bool
{
    return (bool)preg_match('//u', $value);
}

function tnt_ensure_utf8(?string $value): string
{
    if ($value === null) return '';
    if ($value === '') return '';

    // If it's already valid UTF-8, return as-is.
    if (tnt_is_valid_utf8($value)) return $value;

    // Legacy DB content is sometimes ISO-8859-1.
    return utf8_encode($value);
}

function tnt_normalize_locale_db(?string $locale): string
{
    $locale = trim((string)$locale);
    if ($locale === '') return '';

    $locale = str_replace('_', '-', $locale);

    // Accept e.g. nl-NL, nl-nl, fr-FR
    if (!preg_match('/^[A-Za-z]{2}-[A-Za-z]{2}$/', $locale)) return '';

    return strtolower($locale);
}

function tnt_normalize_locale_html(?string $locale): string
{
    $db = tnt_normalize_locale_db($locale);
    if ($db === '') return 'en';

    [$lang, $region] = explode('-', $db, 2);
    return strtolower($lang) . '-' . strtoupper($region);
}

function tnt_i18n_catalog(): array
{
    static $catalog = null;
    if ($catalog !== null) return $catalog;

    $path = __DIR__ . '/i18n/translations.php';
    if (!is_file($path)) {
        $catalog = [];
        return $catalog;
    }

    /** @var mixed $loaded */
    $loaded = require $path;
    $catalog = is_array($loaded) ? $loaded : [];
    return $catalog;
}

function tnt_file_translations(string $localeDb): array
{
    $localeDb = tnt_normalize_locale_db($localeDb);

    $catalog = tnt_i18n_catalog();
    $fallback = $catalog['en-us'] ?? [];

    if ($localeDb === '') return $fallback;

    $byLocale = $catalog[$localeDb] ?? [];
    $byLang = $catalog[substr($localeDb, 0, 2)] ?? [];

    // Later entries override earlier ones.
    return array_merge($fallback, $byLang, $byLocale);
}

function tnt_merge_lang(array $base, array $overrides): array
{
    foreach ($overrides as $key => $value) {
        if (!is_string($key) || $key === '') continue;
        if ($value === null) continue;
        if (is_string($value) && $value === '') continue;
        $base[$key] = $value;
    }

    return $base;
}

function tnt_lang_for_render(string $localeDb, array $dbLang = []): array
{
    $base = tnt_file_translations($localeDb);
    return tnt_merge_lang($base, $dbLang);
}
