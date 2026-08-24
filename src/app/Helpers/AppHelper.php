<?php

use Carbon\Carbon;
use Modules\Core\Models\Settings;
use Illuminate\Support\Facades\Cache;

if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}
if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS);
}
if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS);
}
if (!defined('WEEK_IN_SECONDS')) {
    define('WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS);
}
if (!defined('MONTH_IN_SECONDS')) {
    define('MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS);
}
if (!defined('YEAR_IN_SECONDS')) {
    define('YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS);
}

function is_enable_registration(): bool
{
    return !setting_item('user_disable_register');
}

function is_installed(): bool
{
    return file_exists(storage_path('installed'));
}
function setting_item($item, $default = '', $isArray = false)
{
    $res = Settings::item($item, $default);

    if ($isArray and !is_array($res)) {
        $res = (array) json_decode($res, true);
    }

    return $res;
}

function is_admin(): bool
{
    if (!auth()->check()) return false;
    if(!auth()->user()->hasRole(\Modules\Role\Models\Role::SUPERADMIN)) return false;
    if (auth()->user()->hasPermission('dashboard_access')) return true;
    return false;
}
function is_vendor(): bool
{
    if (!auth()->check()) return false;
    if(!auth()->user()->hasRole(\Modules\Role\Models\Role::CUSTOMER)) return false;
    if (auth()->user()->hasPermission('dashboard_access')) return true;
    return false;
}
function is_baseAdmin(): bool
{
    if (!auth()->check()) {
        return false;
    }

    return auth()->user()->hasRole(\Modules\Role\Models\Role::ADMIN);
}

function booking_status_to_text($status): string
{
    $key = 'booking.statuses.' . $status;
    $translated = __($key);

    return $translated === $key ? ucfirst((string) ($status ?? '')) : $translated;
}

function booking_gateway_to_text(?string $gateway): ?string
{
    if ($gateway === null || $gateway === '') {
        return null;
    }

    $key = 'booking.gateways.' . $gateway;
    $translated = __($key);

    return $translated === $key ? $gateway : $translated;
}

function format_money($price): string
{
    return number_format((float) $price, 0, ',', '.') . ' руб';
}

function format_money_main($price): string
{
    return format_money($price);
}

function display_date($date): string
{
    if (empty($date)) {
        return '';
    }

    return \Carbon\Carbon::parse($date)->format('d.m.Y');
}

function get_bookable_services()
{

    $all = [];

    // Modules
    $custom_modules = \Modules\ServiceProvider::getActivatedModules();
    if (!empty($custom_modules)) {
        foreach ($custom_modules as $moduleData) {
            $moduleClass = $moduleData['class'];
            if (class_exists($moduleClass)) {
                $services = call_user_func([$moduleClass, 'getBookableServices']);
                $all = array_merge($all, $services);
            }
        }
    }

    foreach ($all as $id => $class) {
        $all[$id] = get_class(app()->make($class));
    }
    return $all;
}

/**
 * @throws Exception
 */
function periodDate($startDate, $endDate, $day = true, $interval = '1 day')
{
    $begin = new \DateTime($startDate);
    $end = new \DateTime($endDate);

    if ($day) {
        $end = $end->modify('+1 day');
    }

    $interval = \DateInterval::createFromDateString($interval);

    return new \DatePeriod($begin, $interval, $end);
}

function setting_item_with_lang($item, $locale = '', $default = '', $withOrigin = true, $forceLocale = false)
{
    if (empty($locale)) {
        $locale = app()->getLocale();
    }

    if (!$withOrigin && $locale == setting_item('site_locale')) {
        return $default;
    }

    if (!$forceLocale) {
        if (
            empty(setting_item('site_locale'))
            || empty(setting_item('site_enable_multi_lang'))
            || $locale == setting_item('site_locale')
        ) {
            $locale = '';
        }
    }
    return Settings::item(
        $item . ($locale ? '_' . $locale : ''),
        $withOrigin ? setting_item($item, $default) : $default
    );
}

function app_get_locale($locale = false, $before = false, $after = false): string
{
    if (setting_item('site_enable_multi_lang') and app()->getLocale() != setting_item('site_locale')) {
        return $locale ? $before . $locale . $after : $before . app()->getLocale() . $after;
    }

    return '';
}

function plural_sutki(int $count): string
{
    $count = abs($count);

    if ($count === 1) {
        return '1 сутки';
    }

    return $count . ' суток';
}
