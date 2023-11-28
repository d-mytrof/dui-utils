<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Response;

class DuiClient extends Component
{
    public const ENCRYPTION_ALGO = 'AES-256-CBC';
    public const SHA_ENCRYPTION_ALGO = 'sha256';
    public const SHA_LENGTH = 32;
    public const TOKEN_DATA_ITEMS_COUNT = 2;
    public const HEADER_CLIENT_ID = 'X-Api-Client';
    public const HEADER_AUTHORIZATION = 'Authorization';
    public const HEADER_BEARER = 'Bearer';
    public const HEADER_ACCEPT = 'Accept';
    public const HEADER_CONTENT_TYPE = 'Content-Type';

    public $tokenLifetime = 60 * 10; //10 minutes

    public $clientPublicKey;
    public $clientPrivateKey;
    public $endpoint;
    public $verifySSLHost = true;

    public $systemPublicKey;
    public $systemPrivateKey;
    
    public $entityClassName;


    /**
     * Check token is valid
     * @param string $token
     * @param mixed $model
     * @return bool
     */
    public function tokenIsValid(string $token, mixed $model): bool
    {
        $data = $this->decodeClientToken($token);
        if (!is_array($data) || count($data) < self::TOKEN_DATA_ITEMS_COUNT) {
            return false;
        }

        return ((string)$data[0] !== (string)$this->clientPublicKey ||
                $data[1] < time() || (string)$data[0] !== (string)$model->client_id) ? false : true;
    }

    /**
     * Generate client token
     * @return string|null
     */
    public function generateClientToken(): ?string
    {
        try {
            $ivlen = openssl_cipher_iv_length(self::ENCRYPTION_ALGO);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $encrypted = openssl_encrypt(
                json_encode([
                $this->clientPublicKey,
                time() + $this->tokenLifetime,
                    ]),
                self::ENCRYPTION_ALGO,
                $this->clientPrivateKey,
                OPENSSL_RAW_DATA,
                $iv
            );
            $hmac = hash_hmac(self::SHA_ENCRYPTION_ALGO, $encrypted, $this->clientPrivateKey, true);
            return base64_encode($iv . $hmac . $encrypted);
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Decode client token
     * @param string $token
     * @return array|null
     */
    public function decodeClientToken(string $token): ?array
    {
        try {
            $content = base64_decode($token);
            $ivlen = openssl_cipher_iv_length(self::ENCRYPTION_ALGO);
            $iv = substr($content, 0, $ivlen);
            $encrypted = substr($content, $ivlen + self::SHA_LENGTH);

            if (!is_string($encrypted)) {
                return null;
            }

            $data = openssl_decrypt($encrypted, self::ENCRYPTION_ALGO, $this->clientPrivateKey, OPENSSL_RAW_DATA, $iv);

            return is_string($data) ? json_decode($data) : null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * Build API action endpoint
     * @param string $action
     * @return string
     */
    public function buildEndpoint(string $action): string
    {
        return $this->endpoint . '/' . $action;
    }

    /**
     * Send request to other client
     * @param string $action
     * @param string $requestMethod
     * @param array $params
     * @return Response|null
     */
    public function send(string $action, string $requestMethod, array $params = []): ?Response
    {
        try {
            $request = (new Client([
                        'transport' => CurlTransport::class,
                            ]))->createRequest();
            if ($this->verifySSLHost === false) {
                $request->setOptions([
                    'SSL_VERIFYPEER' => false,
                    'SSL_VERIFYHOST' => false,
                ]);
            }
            $response = $request->setFormat(Client::FORMAT_JSON)
                    ->setHeaders([
                        self::HEADER_ACCEPT => 'application/json',
                        self::HEADER_CONTENT_TYPE => 'application/json',
                        self::HEADER_CLIENT_ID => $this->clientPublicKey,
                        self::HEADER_AUTHORIZATION => self::HEADER_BEARER . ' ' . $this->generateClientToken()
                    ])
                    ->setMethod($requestMethod)
                    ->setUrl($this->buildEndpoint($action))
                    ->setData($params)
                    ->send();
            return $response;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encode string
     * @param string $value
     * @return string|null
     */
    public function encodeString(string $value): ?string
    {
        try {
            $ivlen = openssl_cipher_iv_length(self::ENCRYPTION_ALGO);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $encrypted = openssl_encrypt(
                $value,
                self::ENCRYPTION_ALGO,
                $this->clientPrivateKey,
                OPENSSL_RAW_DATA,
                $iv
            );
            $hmac = hash_hmac(self::SHA_ENCRYPTION_ALGO, $encrypted, $this->clientPrivateKey, true);
            return base64_encode($iv . $hmac . $encrypted);
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Decode string
     * @param string $value
     * @return string|null
     */
    public function decodeString(string $value): ?string
    {
        try {
            $content = base64_decode($value);
            $ivlen = openssl_cipher_iv_length(self::ENCRYPTION_ALGO);
            $iv = substr($content, 0, $ivlen);
            $encrypted = substr($content, $ivlen + self::SHA_LENGTH);

            if (!is_string($encrypted)) {
                return null;
            }
            $data = openssl_decrypt($encrypted, self::ENCRYPTION_ALGO, $this->clientPrivateKey, OPENSSL_RAW_DATA, $iv);

            return (string)$data;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * @return bool
     */
    public function getClientByHeaders(): bool
    {
        $request = Yii::$app->request;
        Yii::$app->client->clientPublicKey = $request->getHeaders()->get('x-api-client');
        if (Yii::$app->client->clientPublicKey) {
            $entity = new $this->entityClassName;
            $model = $entity::findOne([
                'client_id' => Yii::$app->appSecurity->getEncrypted(Yii::$app->client->clientPublicKey),
                'client_type' => $entity::TYPE_CLIENT,
                'status' => $entity::STATUS_ACTIVE,
            ]);

            if (!$model) {
                return false;
            }

            Yii::$app->client->clientPrivateKey = $model->private_key;
            return true;
        }

        return false;
    }
}
