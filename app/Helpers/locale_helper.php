<?php

declare(strict_types=1);

use App\Models\InfringementCaseModel;
use App\Models\LicenseModel;
use App\Models\UsageReportModel;

if (! function_exists('localized_date')) {
    /**
     * Format a date/datetime string for the current request locale.
     */
    function localized_date(?string $datetime, bool $withTime = false): string
    {
        if ($datetime === null || $datetime === '') {
            return '';
        }

        $ts = strtotime($datetime);
        if ($ts === false) {
            return $datetime;
        }

        $locale = service('request')->getLocale();

        if (class_exists(IntlDateFormatter::class)) {
            $dateType = IntlDateFormatter::MEDIUM;
            $timeType = $withTime ? IntlDateFormatter::SHORT : IntlDateFormatter::NONE;
            $fmt      = IntlDateFormatter::create(
                $locale === 'ja' ? 'ja_JP' : 'en_US',
                $dateType,
                $timeType,
                date_default_timezone_get(),
                IntlDateFormatter::GREGORIAN,
            );
            if ($fmt instanceof IntlDateFormatter) {
                $out = $fmt->format($ts);

                return $out !== false ? (string) $out : date('Y-m-d', $ts);
            }
        }

        if ($locale === 'ja') {
            return $withTime ? date('Y年n月j日 H:i', $ts) : date('Y年n月j日', $ts);
        }

        return $withTime ? date('M j, Y g:i A', $ts) : date('M j, Y', $ts);
    }
}

if (! function_exists('localized_month_year')) {
    /**
     * Chart / table label for a calendar month (Y-m).
     */
    function localized_month_year(string $ym): string
    {
        $ts = strtotime($ym . '-01');
        if ($ts === false) {
            return $ym;
        }

        $locale = service('request')->getLocale();

        if (class_exists(IntlDateFormatter::class)) {
            $fmt = IntlDateFormatter::create(
                $locale === 'ja' ? 'ja_JP' : 'en_US',
                IntlDateFormatter::NONE,
                IntlDateFormatter::NONE,
                date_default_timezone_get(),
                IntlDateFormatter::GREGORIAN,
                $locale === 'ja' ? 'y年M月' : 'MMM y',
            );
            if ($fmt instanceof IntlDateFormatter) {
                $out = $fmt->format($ts);

                return $out !== false ? (string) $out : date('M Y', $ts);
            }
        }

        return date('M Y', $ts);
    }
}

if (! function_exists('localized_license_status')) {
    function localized_license_status(string $status): string
    {
        $key = 'App.license_status_' . $status;
        $line = lang($key);
        if ($line !== $key) {
            return $line;
        }

        return LicenseModel::statusLabel($status);
    }
}

if (! function_exists('localized_case_status')) {
    function localized_case_status(string $status): string
    {
        $key = 'App.case_status_' . $status;
        $line = lang($key);
        if ($line !== $key) {
            return $line;
        }

        return InfringementCaseModel::statusLabel($status);
    }
}

if (! function_exists('localized_usage_type')) {
    function localized_usage_type(string $slug): string
    {
        $key = 'App.usage_type_' . $slug;
        $line = lang($key);
        if ($line !== $key) {
            return $line;
        }

        return UsageReportModel::usageTypeLabel($slug);
    }
}

if (! function_exists('localized_case_priority')) {
    function localized_case_priority(string $slug): string
    {
        $key = 'App.case_priority_' . $slug;
        $line = lang($key);
        if ($line !== $key) {
            return $line;
        }

        return InfringementCaseModel::priorityLabel($slug);
    }
}

if (! function_exists('localized_payment_status')) {
    function localized_payment_status(string $slug): string
    {
        $key = 'App.payment_status_' . $slug;
        $line = lang($key);
        if ($line !== $key) {
            return $line;
        }

        return LicenseModel::paymentLabel($slug);
    }
}

if (! function_exists('localized_detected_type')) {
    function localized_detected_type(string $slug): string
    {
        $key = 'App.detected_type_' . $slug;
        $line = lang($key);
        if ($line !== $key) {
            return $line;
        }

        return UsageReportModel::detectedTypeLabel($slug);
    }
}

if (! function_exists('current_lang_url')) {
    /**
     * URL to switch UI language while returning to the current page.
     */
    function current_lang_url(string $locale): string
    {
        return site_url('lang/' . $locale);
    }
}
