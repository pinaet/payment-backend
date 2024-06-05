<?php

namespace App\Services;

class SecurityData
{
    /**
     * JWE Key Id.
     *
     * @var string
     */
    public static string $EncryptionKeyId = "";

    /**
     * Access Token.
     *
     * @var string
     */
    public static string $AccessToken = "";

    /**
     * Token Type - Used in JWS and JWE header.
     *
     * @var string
     */
    public static string $TokenType = "JWT";

    /**
     * JWS (JSON Web Signature) Signature Algorithm - This parameter identifies the cryptographic algorithm used to
     * secure the JWS.
     *
     * @var string
     */
    public static string $JWSAlgorithm = "PS256";

    /**
     * JWE (JSON Web Encryption) Key Encryption Algorithm - This parameter identifies the cryptographic algorithm
     * used to secure the JWE.
     *
     * @var string
     */
    public static string $JWEAlgorithm = "RSA-OAEP";

    /**
     * JWE (JSON Web Encryption) Content Encryption Algorithm - This parameter identifies the content encryption
     * algorithm used on the plaintext to produce the encrypted ciphertext.
     *
     * @var string
     */
    public static string $JWEEncrptionAlgorithm = "A128CBC-HS256"; // it reqlly should be A128CBC-HS256

    /**
     * Merchant Signing Private Key is used to cryptographically sign and create the request JWS.
     *
     * @var string
     */
    public static string $MerchantSigningPrivateKey = "";
    /**
     * PACO Encryption Public Key is used to cryptographically encrypt and create the request JWE.
     *
     * @var string
     */
    public static string $PacoEncryptionPublicKey = "";
    /**
     * PACO Signing Public Key is used to cryptographically verify the response JWS signature.
     *
     * @var string
     */
    public static string $PacoSigningPublicKey = "";
    /**
     * Merchant Decryption Private Key used to cryptographically decrypt the response JWE.
     * @var string
     */
    public static string $MerchantDecryptionPrivateKey = "";

    public static function boot()
    {
        self::$EncryptionKeyId              = env('ENCRYPTION_KEY_ID');
        self::$AccessToken                  = env('ACCESS_TOKEN');
        self::$MerchantSigningPrivateKey    = env('MERCHANT_SIGNING_PRIVATE_KEY');
        self::$MerchantDecryptionPrivateKey = env('MERCHANT_DECRYPTION_PRIVATE_KEY');
        self::$PacoEncryptionPublicKey      = env('PACO_ENCRYPTION_PUBLIC_KEY');
        self::$PacoSigningPublicKey         = env('PACO_SIGNING_PUBLIC_KEY');
    }
}