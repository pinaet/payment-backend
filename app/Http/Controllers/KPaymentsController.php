<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

use App\Log;
use App\PaymentStatus;
use App\Transaction;

class KPaymentsController extends Controller
{

    private     $bank;

    /**
     * payment config retrieval
     */
    function __construct()
    {
        $this->bank = env('PAYMENT_BANK', 'kbank-dev');
    }

    public function getPublicKey()
    {
        return config('payment.' . $this->bank . '.pkey');
    }

    public function getSecretKey()
    {
        return config('payment.' . $this->bank . '.skey');
    }

    public function getBaseUrl()
    {
        return config('payment.' . $this->bank . '.base_url');
    }

    public function getCurrency()
    {
        return config('payment.currency');
    }

    public function getMerchantId($type = 'wechat')
    {
        return config('payment.' . $this->bank . '.' . $type . '.mid');
    }

    public function getTerminalId($type = 'wechat')
    {
        return config('payment.' . $this->bank . '.' . $type . '.tid');
    }

    public function getUiUrl()
    {
        return config('payment.' . $this->bank . '.ui_url');
    }

    public function getTransactionState($type = 'wechat')
    {
        return config('payment.' . $this->bank . '.' . $type . '.transaction_state');
    }

    public function getCallbackIdName()
    {
        return config('payment.' . $this->bank . '.callback_id');
    }

    public function getCallbackUrl()
    {
        return config('payment.' . $this->bank . '.callback_url');
    }

    public function getNotifyUrl()
    {
        return config('payment.' . $this->bank . '.notify_url');
    }



    /**
     * K-Payment API - create
     */
    public function createTempOrder( $order )
    {
        return $this->api($order, '/qr/v2/order');
    }

    public function storeOrder( $order, $payload )
    {
        /**
         * log transaction 
         */
        $attributes['ref']                  = $order['ref'];
        $attributes['amount']               = $payload['amount'];
        $attributes['currency']             = $payload['currency'];
        $attributes['description']          = $payload['description'];
        $attributes['source_type']          = $order['source_type'];
        $attributes['reference_order']      = $payload['reference_order'];
        $attributes['charge_id']            = '';
        $attributes['order_id']             = $payload['id']; //different
        $attributes['transaction_state']    = '';
        $attributes['status']               = $payload['status'];
        $attributes['notify_result']        = '';
        $attributes['callback_result']      = '';
        $attributes['payment_status_id']    = PaymentStatus::where('payment_status', 'pending')->get()[0]->id;

        Transaction::create($attributes); 
    }

    public function createTempCharge( $order )
    {
        return $this->api($order, '/card/v2/charge');
    }

    public function storeCharge( $order, $payload )
    {
        /**
         * store data
         */
        $attributes['ref']                  = $order['ref'];
        $attributes['amount']               = $payload['amount'];
        $attributes['currency']             = $payload['currency'];
        $attributes['description']          = $payload['description'];
        $attributes['source_type']          = $order['source_type'];
        $attributes['reference_order']      = $payload['reference_order'];
        $attributes['charge_id']            = $payload['id'];
        $attributes['order_id']             = '';
        $attributes['transaction_state']    = $payload['transaction_state'];
        $attributes['status']               = $payload['status'];
        $attributes['notify_result']        = '';
        $attributes['callback_result']      = '';
        $attributes['payment_status_id']    = PaymentStatus::where('payment_status', 'pending')->get()[0]->id;

        Transaction::create($attributes);
    }

    public function createOrder( $order )
    {
        $payload = $this->api( $order, '/qr/v2/order');

        // dd('order', $order, 'payload', $payload);
        /**
         * log transaction 
         */
        $this->storeOrder($order, $payload);

        return $payload;
    }

    public function createCharge($order)
    {
        $payload =  $this->api( $order, '/card/v2/charge' );
        // dd( 'order', $order, 'payload', $payload );

        /**
         * store data
         */
        $this->storeCharge( $order, $payload );

        return $payload;
    }

    public function api( $order, $end_point )
    {
        $http   = new Client;

        $query  = [
            'amount'            => $order['amount'],
            'currency'          => $order['currency'],
            'description'       => $order['description'],
            'source_type'       => $order['source_type'],
            'reference_order'   => $order['reference_order'],
            'additional_data'   => [
                'mid' => $this->getMerchantId($order['source_type']),
                'tid' => $this->getTerminalId($order['source_type']),
            ]
        ];

        try {
            $response = $http->post($this->getBaseUrl() . $end_point, [
                'headers' => [
                    'x-api-key' => $this->getSecretKey(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $query,
            ]);
        } 
        catch (\Exception $e) {
            $attributes['type'] = 'error';
            $attributes['log' ] = json_encode($order);
            $attributes['log2'] = $e->getMessage();
            Log::create( $attributes );
        }
        
        $payload    = json_decode((string) $response->getBody(), true);
        
        return $payload;
    }



    /**
     * K-Payment API - create
     */
    public function inquiryTransaction( $id )
    {
        $payload = $this->inquiryApi( $id, '/card/v2/charge/' );
        return $payload;
    }

    public function inquiryTransactionQr( $id )
    {
        $payload = $this->inquiryApi( $id, '/qr/v2/qr/' );
        return $payload;
    }

    public function inquiryApi( $id, $end_point )
    {
        $http    = new Client;

        try {
            $response = $http->get($this->getBaseUrl() . $end_point . $id, [
                'headers' => [
                    'x-api-key' => $this->getSecretKey(),
                ]
            ]);
        } catch (\Exception $e) {
            $attributes['type'] = 'error';
            $attributes['log']  = $end_point . $id;
            $attributes['log2'] = $e->getMessage();
            Log::create($attributes);
        }

        $payload    = json_decode((string) $response->getBody(), true);

        return $payload;
    }



    public function callback()
    {
        $payload = request()->all();

        $attributes['type'] = 'callback';
        $attributes['log']  = 'callback from KBank payment';
        $attributes['log2'] = json_encode($payload);
        Log::create($attributes);
    }


    /**
     * notify gate
     */
    public function trimTrans( $trans ){
        $data['transaction_id']     = $trans->id;
        $data['ref']                = $trans->ref;
        $data['amount']             = $trans->amount;
        $data['currency']           = $trans->currency;
        $data['description']        = $trans->description;
        $data['source_type']        = $trans->source_type;
        $data['reference_order']    = $trans->reference_order;
        $data['payment_status']     = $trans->payment_status()->payment_status;

        return $data;
    }

    public function notifyGate( $data )
    {
        $http    = new Client;

        try {
            $http->post($this->getNotifyUrl(), [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => ['data' => $data],
            ]);
        } catch (\Exception $e) {
            $attributes['type'] = 'notifyGate - error';
            $attributes['log'] = json_encode( $data );
            $attributes['log2'] = $e->getMessage();
            Log::create($attributes);
        }
    }
}
