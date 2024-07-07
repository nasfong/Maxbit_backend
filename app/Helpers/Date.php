<?php

/** Date Format */
if (!function_exists('time_ago')) {
    function time_ago($date)
    {
        return isset($date) ? Carbon\Carbon::parse($date)->diffForHumans() : null;
    }
}


// Out: 0/00/0000
if (!function_exists('dateDisplay')) {
    function dateDisplay($var = null)
    {
        return $var ? Carbon\Carbon::parse($var)->format('d/m/Y') : null;
    }
}

// Out: 00/00/0000 00:00 00
if (!function_exists('dateTimeDisplay')) {
    function dateTimeDisplay($var = null)
    {
        return $var ? Carbon\Carbon::parse($var)->format('d/m/Y H:i A') : null;
    }
}

// Date add database 0000-00-00
if (!function_exists('dateAdd')) {
    function dateAdd($var = null)
    {
        return $var ? Carbon\Carbon::createFromFormat(config('setting.date_format'), $var)->format('Y-m-d') : null;
    }
}

// Date add database 0000-00-00 00:00:00
if (!function_exists('dateTimeAdd')) {
    function dateTimeAdd($var = null)
    {
        return $var ? Carbon\Carbon::createFromFormat('d/m/Y H:i A', $var)->format('Y-m-d H:i:s') : null;
    }
}
