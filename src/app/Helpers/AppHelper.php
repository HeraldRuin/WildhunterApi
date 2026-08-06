<?php

use Carbon\Carbon;
use Modules\Core\Models\Settings;
use Illuminate\Support\Facades\Cache;


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

function is_admin()
{
    if (!auth()->check()) return false;
    if(!auth()->user()->hasRole(\Modules\Role\Models\Role::SUPERADMIN)) return false;
    if (auth()->user()->hasPermission('dashboard_access')) return true;
    return false;
}
function is_vendor()
{
    if (!auth()->check()) return false;
    if(!auth()->user()->hasRole(\Modules\Role\Models\Role::CUSTOMER)) return false;
    if (auth()->user()->hasPermission('dashboard_access')) return true;
    return false;
}
function is_baseAdmin()
{
    if (!auth()->check()) return false;
    if(!auth()->user()->hasRole(\Modules\Role\Models\Role::ADMIN)) return false;
    if (auth()->user()->hasPermission('dashboard_access')) return true;
    return false;
}

function booking_status_to_text($status): string
{
    $key = 'booking.statuses.' . $status;
    $translated = __($key);

    return $translated === $key ? ucfirst((string) ($status ?? '')) : $translated;
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


    // Plugin Menu
//    $plugins_modules = \Plugins\ServiceProvider::getModules();
//    if (!empty($plugins_modules)) {
//        foreach ($plugins_modules as $module) {
//            $moduleClass = "\\Plugins\\" . ucfirst($module) . "\\ModuleProvider";
//            if (class_exists($moduleClass)) {
//                $services = call_user_func([$moduleClass, 'getBookableServices']);
//                $all = array_merge($all, $services);
//            }
//        }
//    }
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
