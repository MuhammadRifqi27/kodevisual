<?php

use Illuminate\Support\Number;

if (!function_exists('format_rupiah')) {
    /**
     * Format number to Rupiah currency
     *
     * @param  float|int  $amount
     * @param  bool  $withSymbol
     * @return string
     */
    function format_rupiah($amount, $withSymbol = true)
    {
        // Handle null or empty values
        if ($amount === null || $amount === '') {
            return $withSymbol ? 'Rp 0' : '0';
        }

        // Convert to float if it's a string
        $amount = floatval($amount);

        // Format using Laravel Number helper (Laravel 9+)
        if (class_exists('Illuminate\Support\Number') && function_exists('Number::format')) {
            $formatted = Number::format($amount, 0, ',', '.');
        } else {
            // Fallback for older Laravel versions
            $formatted = number_format($amount, 0, ',', '.');
        }

        return $withSymbol ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('format_rupiah_decimal')) {
    /**
     * Format number to Rupiah currency with decimal
     *
     * @param  float|int  $amount
     * @param  int  $decimals
     * @param  bool  $withSymbol
     * @return string
     */
    function format_rupiah_decimal($amount, $decimals = 2, $withSymbol = true)
    {
        if ($amount === null || $amount === '') {
            $amount = 0;
        }

        $amount = floatval($amount);

        if (class_exists('Illuminate\Support\Number') && function_exists('Number::format')) {
            $formatted = Number::format($amount, $decimals, ',', '.');
        } else {
            $formatted = number_format($amount, $decimals, ',', '.');
        }

        return $withSymbol ? 'Rp ' . $formatted : $formatted;
    }
}
