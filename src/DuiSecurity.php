<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use Exception;
use yii\base\Component;
use yii\helpers\Json;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class DuiSecurity extends Component
{
    public const JWT_METHOD = 'RS256';
    public const DEFAULT_AUTH_SERVICE_NAME = 'auth';
    
    public $passwordSalt;
            
    public $tokenSecretKey;
    public $tokenLifetime;

    public $captchaHashData;
    public $captchaSecretKey;
    public $captchaLifetime;
        
    public $dbSecretKey;
    
    public $entityClassName;
    
    public function init()
    {
        parent::init();
        if (!extension_loaded('gd')) {
            throw new Exception('GD is not installed!');
        }
    }
    
    /**
     * Make password hash
     * @param string $password
     * @return string
     */
    public function encodePassword(string $password): string
    {
        return hash('sha512', $this->passwordSalt . $password);
    }

    /**
     * Encode session ID
     * @param string|float $time
     * @param mixed $user
     * @param string $passwordValue
     * @return string
     */
    public function encodeToken(string|float $time, mixed $user, string $passwordValue): string
    {
        $ivlen = openssl_cipher_iv_length($cipher = 'AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encryptedTextNext = openssl_encrypt(
            $time . '|' . $user['secret_key'],
            $cipher,
            $passwordValue . $user['secret_key'],
            $options = OPENSSL_RAW_DATA,
            $iv
        );
        $hmacNext = hash_hmac('sha256', $encryptedTextNext, $user['secret_key'], $as_binary = true);
        $cipherTextNext = base64_encode($iv . $hmacNext . $encryptedTextNext);

        $text = $time . '|' . $user['uid'] . '|' . $cipherTextNext;
        $encryptedText = openssl_encrypt($text, $cipher, $this->tokenSecretKey, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $encryptedText, $this->tokenSecretKey, $as_binary = true);

        return base64_encode($iv . $hmac . $encryptedText);
    }

    /**
     * Decode session ID (token)
     * @param string $token
     * @return string|bool
     */
    public function decodeToken(string $token): string|bool
    {
        $content = base64_decode($token);
        $ivlen = openssl_cipher_iv_length($cipher = 'AES-256-CBC');
        $iv = substr($content, 0, $ivlen);
        $sha2len = 32;
        $encryptedText = substr($content, $ivlen + $sha2len);
        return openssl_decrypt($encryptedText, $cipher, $this->tokenSecretKey, $options = OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Encode captcha string
     * @param string $str
     * @return string
     */
    public function encodeCaptchaString(string $str): string
    {
        $text = $str . '|' . Yii::$app->id . '|' . $this->captchaHashData . '|' . time();
        $ivlen = openssl_cipher_iv_length($cipher = 'AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);

        $encryptedText = openssl_encrypt($text, $cipher, $this->captchaSecretKey, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $encryptedText, $this->captchaSecretKey, $as_binary = true);
        return base64_encode($iv . $hmac . $encryptedText);
    }

    /**
     * Decode captcha string
     * @param string $encoded
     * @return string|false
     */
    public function decodeCaptchaString(string $encoded): string|false
    {
        $content = base64_decode($encoded);
        $ivlen = openssl_cipher_iv_length($cipher = 'AES-256-CBC');
        $iv = substr($content, 0, $ivlen);
        $sha2len = 32;
        $encryptedText = substr($content, $ivlen + $sha2len);
        return openssl_decrypt($encryptedText, $cipher, $this->captchaSecretKey, $options = OPENSSL_RAW_DATA, $iv);
    }

    /**
     * @param string $value
     * @return Expression|null
     */
    public function getEncrypted(string $value): ?Expression
    {
        $name = 'value' . time() . random_int(1000, 1000000);
        try {
            return new Expression(
                "AES_ENCRYPT(:$name, UNHEX(SHA2('". $this->dbSecretKey."', 512)))",
                [":$name" => $value]
            );
        } catch (Exception $ex) {
            return null;
        }
    }
    
    /**
     * @param string $value
     * @return Expression|null
     */
    public function getDecrypted(string $value): ?Expression
    {
        $name = 'value' . time() . random_int(1000, 1000000);
        return new Expression(
            "AES_DECRYPT(:$name, UNHEX(SHA2('".$this->dbSecretKey."', 512)))",
            [":$name" => $value]
        );
    }
    
    /**
     * @param mixed $db
     * @param string $value
     * @return string|null
     */
    public function getDecryptedString(mixed $db, string $value): ?string
    {
        try {
            return (new Query())->select($this->getDecrypted($value))->scalar($db);
        } catch (Exception $ex) {
            return null;
        }
    }
    
    /**
     * @param int $keySize
     * @return array
     */
    public function generateKeyPair(int $keySize = 512): array
    {
        $config = [
            "private_key_bits" => $keySize,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $publicKeyDetails = openssl_pkey_get_details($res);
        $publicKey = $publicKeyDetails["key"];

        return ['private' => $privateKey, 'public' => $publicKey];
    }
    
    /**
     * @return string
     */
    public function getAuthServiceName(): string
    {
        return self::DEFAULT_AUTH_SERVICE_NAME;
    }
    
    /**
     * @param string $jwt
     * @param string $encodeMethod
     * @return array|null
     */
    public function getGwtParams(string $jwt = null, string $encodeMethod = null): ?array
    {
        $params = null;
        if (!$jwt) {
            $authHeader = Yii::$app->request->getHeaders()->get('Authorization');
            $pattern = '/^Bearer\s+(.*?)$/';
            if ($authHeader !== null) {
                if ($pattern !== null) {
                    if (preg_match($pattern, $authHeader, $matches)) {
                        $jwt = $matches[1];
                    } else {
                        return null;
                    }
                }
            }
        }
        
        if (!$jwt) {
            return null;
        }

        $entity = new $this->entityClassName;
        $model = $entity::findOne([
            'name' => $this->getAuthServiceName(),
            'status' => $entity::STATUS_ACTIVE,
        ]);
        if (!$model) {
            return null;
        }

        $decoded = JWT::decode($jwt, new Key($model->public_key, self::JWT_METHOD));

        if ($decoded->client_name === $this->getAuthServiceName()) {
            return (array)$decoded;
        }
        
        return null;
    }
}
