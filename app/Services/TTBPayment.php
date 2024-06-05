<?php

namespace App\Services;

use App\Log;
use Carbon\Carbon;
use App\Services\SecurityData;
use App\Services\ActionRequest;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class TTBPayment extends ActionRequest
{
    /**
     * @throws GuzzleException
     */
    public function Execute(): string
    {
        $now = Carbon::now();
        $orderNo = $now->getPreciseTimestamp(3);

        $request = [
            "apiRequest" => [
                "requestMessageID" => $this->Guid(),
                "requestDateTime" => $now->utc()->format('Y-m-d\TH:i:s.v\Z'),
                "language" => "en-US",
            ],
            "officeId" => "DEMOOFFICE",
            "orderNo" => $orderNo,
            "productDescription" => "desc for '$orderNo'",
            "paymentType" => "CC",
            "paymentCategory" => "ECOM",
            "creditCardDetails" => [
                "cardNumber" => "4706860000002325",
                "cardExpiryMMYY" => "1225",
                "cvvCode" => "761",
                "payerName" => "Demo Sample"
            ],
            "storeCardDetails" => [
                "storeCardFlag" => "N",
                "storedCardUniqueID" => "{{guid}}"
            ],
            "installmentPaymentDetails" => [
                "ippFlag" => "N",
                "installmentPeriod" => 0,
                "interestType" => null
            ],
            "mcpFlag" => "N",
            "request3dsFlag" => "N",
            "transactionAmount" => [
                "amountText" => "000000100000",
                "currencyCode" => "THB",
                "decimalPlaces" => 2,
                "amount" => 1000
            ],
            "notificationURLs" => [
                "confirmationURL" => "http://example-confirmation.com",
                "failedURL" => "http://example-failed.com",
                "cancellationURL" => "http://example-cancellation.com",
                "backendURL" => "http://example-backend.com"
            ],
            "deviceDetails" => [
                "browserIp" => "1.0.0.1",
                "browser" => "Postman Browser",
                "browserUserAgent" => "PostmanRuntime/7.26.8 - not from header",
                "mobileDeviceFlag" => "N"
            ],
            "purchaseItems" => [
                [
                    "purchaseItemType" => "ticket",
                    "referenceNo" => "2322460376026",
                    "purchaseItemDescription" => "Bundled insurance",
                    "purchaseItemPrice" => [
                        "amountText" => "000000100000",
                        "currencyCode" => "THB",
                        "decimalPlaces" => 2,
                        "amount" => 1000
                    ],
                    "subMerchantID" => "string",
                    "passengerSeqNo" => 1
                ]
            ],
            "customFieldList" => [
                [
                    "fieldName" => "TestField",
                    "fieldValue" => "This is test"
                ]
            ]
        ];

        $stringRequest = json_encode($request);

        //third-party http client https://github.com/guzzle/guzzle
        $response = $this->client->post('api/1.0/Payment/NonUi', [
            'headers' => [
                'Accept' => 'application/json',
                'apiKey' => SecurityData::$AccessToken,
                'Content-Type' => 'application/json; charset=utf-8'
            ],
            'body' => $stringRequest
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function ExecuteJose( $request_string )
    {
        $now = Carbon::now();

        $request = json_decode($request_string);

        $payload = [
            "request" => $request,
            "iss" => SecurityData::$AccessToken,
            "aud" => "PacoAudience",
            "CompanyApiKey" => SecurityData::$AccessToken,
            "iat" => $now->unix(),
            "nbf" => $now->unix(),
            "exp" => $now->addHour()->unix(),
        ];

        $stringPayload = json_encode($payload);
        $signingKey = $this->GetPrivateKey(SecurityData::$MerchantSigningPrivateKey);
        $encryptingKey = $this->GetPublicKey(SecurityData::$PacoEncryptionPublicKey);

        $body = $this->EncryptPayload($stringPayload, $signingKey, $encryptingKey);

        try { 
            //third-party http client https://github.com/guzzle/guzzle
            $response = $this->client->post( env('TTB_END_POINT'), [
                'headers' => [
                    'Accept' => 'application/jose',
                    'CompanyApiKey' => SecurityData::$AccessToken,
                    'Content-Type' => 'application/jose; charset=utf-8'
                ],
                'body' => $body
            ]);
        }
        catch (ClientException $e) {
            // Handle 400-level errors here
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            $payload = json_decode($this->decrypt($responseBodyAsString),true)['response'];
        
            $attributes['type']     = 'client_error';
            $attributes['log']      = $request_string;
            $attributes['log2']     = $e->getMessage();
            $attributes['response'] = json_encode($payload); // Store the response body
            Log::create($attributes);
        
            $title      = 'Payment unsuccessful';  
            $message    = $payload['apiResponse']['responseDescription']; //'Invoice number is duplicated.';
            // Optionally, you can rethrow the exception or handle it as needed

            return ['redirect' => true, 'title' => $title, 'message' => $message];
        }
        catch (\Exception $e) {
            $attributes['type'] = 'error';
            $attributes['log' ] = $request_string;
            $attributes['log2'] = $e->getMessage();
            Log::create( $attributes );
        }

        $token = $response->getBody()->getContents();

        return $this->decrypt($token);
    }

    public function decrypt( $token ): string
    {
        $decryptingKey = $this->GetPrivateKey(SecurityData::$MerchantDecryptionPrivateKey);
        $signatureVerificationKey = $this->GetPublicKey(SecurityData::$PacoSigningPublicKey);

        return $this->DecryptToken($token, $decryptingKey, $signatureVerificationKey);
    }
}