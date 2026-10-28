<?php

if (!function_exists('format_money')) {
    /**
     * Format money amount with currency symbol
     *
     * @param float $amount
     * @param string $currency
     * @param int $decimals
     * @return string
     */
    function format_money($amount, $currency = 'EUR', $decimals = 2)
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        $formatted = number_format($amount, $decimals, '.', ',');

        // For USD, GBP, symbol goes before; for EUR, after
        if (in_array($currency, ['USD', 'GBP', 'INR'])) {
            return $symbol . $formatted;
        }

        return $formatted . ' ' . $symbol;
    }
}
