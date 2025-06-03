<?php

use Illuminate\Support\Facades\Crypt;

if (!function_exists('safeDecrypt')) {
    function safeDecrypt($encryptedText, $default = 'Error decrypting message')
    {
        try {
            return Crypt::decryptString($encryptedText);
        } catch (\Exception $e) {
            return $default;
        }
    }
}
