<?php

if (! function_exists('format_duration')) {
    /**
     * 215 -> "3:35", 3725 -> "1:02:05".
     */
    function format_duration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remaining = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $remaining);
        }

        return sprintf('%d:%02d', $minutes, $remaining);
    }
}

if (! function_exists('format_count')) {
    /**
     * 1250 -> "1,2 rb", 2400000 -> "2,4 jt".
     */
    function format_count(int $value): string
    {
        if ($value >= 1_000_000_000) {
            return number_format($value / 1_000_000_000, 1, ',', '.').' m';
        }

        if ($value >= 1_000_000) {
            return number_format($value / 1_000_000, 1, ',', '.').' jt';
        }

        if ($value >= 1_000) {
            return number_format($value / 1_000, 1, ',', '.').' rb';
        }

        return (string) $value;
    }
}

if (! function_exists('track_json')) {
    /**
     * JSON aman untuk ditempel di atribut HTML.
     *
     * @param  array<string, mixed>|array<int, array<string, mixed>>  $value
     */
    function track_json(array $value): string
    {
        return (string) json_encode(
            $value,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES
        );
    }
}
