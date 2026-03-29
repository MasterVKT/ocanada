<?php
declare(strict_types=1);

use CodeIgniter\I18n\Time;

if (! function_exists('format_date_fr')) {
    function format_date_fr(string $date): string
    {
        try {
            $time = Time::createFromFormat('Y-m-d', $date, 'Africa/Douala');
        } catch (Throwable) {
            return $date;
        }

        return $time->toLocalizedString('dd/MM/yyyy');
    }
}

if (! function_exists('format_heure')) {
    function format_heure(string $time): string
    {
        return substr($time, 0, 5);
    }
}

if (! function_exists('format_xaf')) {
    function format_xaf(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' XAF';
    }
}

