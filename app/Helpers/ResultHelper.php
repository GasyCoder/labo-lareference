<?php
// app/Helpers/ResultHelper.php

if (!function_exists('formatResult')) {
    /**
     * Formate les résultats en chaîne propre.
     *
     * @param mixed $data
     * @return string
     */
    function formatResult($data) {
        if (is_null($data)) {
            return 'N/A';
        }

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? implode(', ', array_map('trim', $decoded)) : trim($decoded);
            }
            return trim($data);
        }

        if (is_array($data)) {
            return implode(', ', array_map('trim', $data));
        }

        return (string) $data;
    }
}
