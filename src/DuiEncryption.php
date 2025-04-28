<?php
/**
 * @copyright Copyright © 2025 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Exception;

/**
 * Class for encryption/decryption
 */
class DuiEncryption
{
    private static string $secretKey;
    private static string $ivSecret;

    public static function encrypt(string $plainText): string
    {
        self::$secretKey = getenv('SECURITY_COOKIE_SECRET_KEY');
        self::$ivSecret = getenv('SECURITY_COOKIE_IV_SECRET');
        
        if (empty(self::$secretKey)) {
            throw new Exception('Encryption key is not set.');
        }

        $iv = substr(hash('sha256', self::$ivSecret), 0, 16);
        $encrypted = openssl_encrypt($plainText, 'AES-128-CBC', self::$secretKey, 0, $iv);

        if ($encrypted === false) {
            throw new Exception('Encryption failed.');
        }

        return base64_encode($encrypted);
    }

    public static function decrypt(string $encryptedText): string
    {
        self::$secretKey = getenv('SECURITY_COOKIE_SECRET_KEY');
        self::$ivSecret = getenv('SECURITY_COOKIE_IV_SECRET');
        
        if (empty(self::$secretKey)) {
            throw new Exception('Decryption key is not set.');
        }

        $iv = substr(hash('sha256', self::$ivSecret), 0, 16);
        $decrypted = openssl_decrypt(base64_decode($encryptedText), 'AES-128-CBC', self::$secretKey, 0, $iv);

        if ($decrypted === false) {
            throw new Exception('Decryption failed.');
        }

        return $decrypted;
    }
}