<?php

/**
 * PHP AphEncrypter
 *
 */
namespace APH;
/**
 * AphEncrypter
 *
 * This class encrypts and decrypts the given value. It uses OpenSSL extension
 * with AES-256 cipher for encryption and HMAC-SHA-256 for hash.
 * The encryption and hash can use different keys.
 */
class Encrypter
{
    /**
     * The encryption key
     *
     * @var string
     */
    const key = "87CAD2D5834BB7ADD18D5ED39BE3C3AW";

    /**
     * The authentication key
     *
     * @var string
     */
    const authKey = "87CAD2D5834BB7ADD18D5ED39BE3C3AW";

    /**
     * Create a new Aphencrypter instance
     *
     * @param string      $key     The encryption key
     * @param string|null $authKey The authentication key
     *
     * @throws RuntimeException
     */
    public function __construct()
    {
        if (!extension_loaded('openssl')) {
            echo('OpenSSL extension is not available.');
        }

        if (!extension_loaded('mbstring')) {
            echo('Multibyte String extension is not available.');
        }

    }


    /**
     * Encrypt the given value
     *
     * @param  mixed  $value     The value to encrypt
     * @param  bool   $serialize Serialize the value
     * @return string
     */
    static function encrypt($value, $serialize = true)
    {
        $iv = random_bytes(16);

        // Encrypt the given value
        $encrypted = openssl_encrypt(
            $serialize ? serialize($value) : $value,
            'AES-256-CBC', self::key, 0, $iv
        );

        if ($encrypted !== false) {
            // Create a keyed hash for the encrypted value
            $hmac = self::hash($iv . $encrypted);

            return base64_encode($iv . $hmac . $encrypted);
        }
    }

    /**
     * Encrypt the given string without serialization
     *
     * @param  string $value The string to encrypt
     * @return string
     */
    static function encryptString($value)
    {
        return self::encrypt($value, false);
    }

    /**
     * Decrypt the given value
     *
     * @param  string $value       The value to decrypt
     * @param  bool   $unserialize Unserialize the value
     * @return mixed
     */
    static function decrypt($value, $unserialize = true)
    {
        $value = base64_decode($value);

        $iv         = mb_substr($value, 0, 16, '8bit');
        $hmac       = mb_substr($value, 16, 32, '8bit');
        $encrypted  = mb_substr($value, 48, null, '8bit');

        // Create a keyed hash for the decrypted value
        $hmacNew = self::hash($iv . $encrypted);

        if (self::hashEquals($hmac, $hmacNew)) {
            // Decrypt the given value
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', self::key, 0, $iv);

            if ($decrypted !== false) {
                return $unserialize ? unserialize($decrypted) : $decrypted;
            }
        }
    }

    /**
     * Decrypt the given string without unserialization
     *
     * @param  string $value The string to decrypt
     * @return string
     */
    static function decryptString($value)
    {
        return self::decrypt($value, false);
    }

    /**
     * Create a keyed hash for the given value
     *
     * @param  string $value Value to hash
     * @return string
     */
    static function hash($value)
    {
        return hash_hmac('sha256', $value, self::authKey, true);
    }

    /**
     * Compare hashes
     *
     * @param  string $original Original hash
     * @param  string $new      New hash
     * @return bool
     */
    static function hashEquals($original, $new)
    {
        // PHP version >= 5.6
        if (function_exists('hash_equals')) {
            return hash_equals($original, $new);
        }

        // PHP version < 5.6
        if (!is_string($original) || !is_string($new)) {
            return false;
        }

        if ($originalLength = mb_strlen($original) !== mb_strlen($new)) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $originalLength; ++$i) {
            $result |= ord($original[$i]) ^ ord($new[$i]);
        }

        return $result === 0;
    }
}