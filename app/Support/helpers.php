<?php

if (! function_exists('inr')) {
    /**
     * Format an amount as Indian Rupees, e.g. ₹1,24,999 (decimals only when needed).
     */
    function inr(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $decimals = fmod($amount, 1.0) == 0.0 ? 0 : 2;

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter('en_IN', NumberFormatter::DECIMAL);
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);

            return config('shop.currency_symbol').$formatter->format($amount);
        }

        // Fallback when the intl extension is missing: Indian digit grouping by hand (12,34,567).
        [$whole, $fraction] = array_pad(explode('.', number_format(abs($amount), $decimals, '.', '')), 2, null);
        $lastThree = substr($whole, -3);
        $rest = substr($whole, 0, -3);
        $grouped = ($rest !== '' ? preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest).',' : '').$lastThree;

        return ($amount < 0 ? '-' : '').config('shop.currency_symbol').$grouped.($fraction !== null ? '.'.$fraction : '');
    }
}
