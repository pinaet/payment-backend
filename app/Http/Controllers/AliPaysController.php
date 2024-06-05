<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\KPaymentsController;
use App\Log;
use App\Transaction;
use App\PaymentStatus;

class AliPaysController extends Controller
{
    private $method     = 'alipay';
    private $payment    = '';
    private $isolate    = false;

    public function __construct()
    {
        $this->payment  = new KPaymentsController;
        $this->isolate  = config('payment.isolate');
    }
    
    public function create()
    {
        $order            = json_decode(request()->order, true);
        $order['amount']  = $order['total_amount'];
        $payload          = $this->payment->createTempCharge($order);

        $payment['pkey']  = $this->payment->getPublicKey();
        $payment['ur_url']= $this->payment->getUiUrl();

        $data['payload']  = $payload;
        $data['payment']  = $payment;

        return $data;
    }

    public function store()
    {
        $order            = json_decode(request()->order  , true);
        $payload          = json_decode(request()->payload, true);
        $this->payment->storeCharge( $order, $payload );

        return '200 OK';
    }

    public function alipay()
    {
        //prepare all mendatory data

        //create charge api
        //prepare order data        
        $order['ref']               = 1;
        $order['amount']            = 12;
        $order['currency']          = $this->payment->getCurrency();
        $order['description']       = 'Awesome Product';
        $order['source_type']       = $this->method;
        $order['reference_order']   = '12345';

        $payload = $this->payment->createCharge($order);

        $transaction_state = '';
        $status            = '';
        if( !empty($payload['transaction_state']) ) $transaction_state  = $payload['transaction_state'];
        if( !empty($payload['status'])            ) $status             = $payload['status'];

        if( $transaction_state=='Initialize' && $status=='success' ) {
            //keep charge_id for later check
            session(['charge_id' => $payload['id']]);

            //prepare data for k-pay button
            $data                   = array();
            $data['ui_url']         = $this->payment->getUiUrl();
            $data['pkey']           = $this->payment->getPublicKey();
            $data['mid']            = $this->payment->getMerchantId($this->method);
            $data['currency']       = $order['currency'];
            $data['amount']         = $order['amount'];
            $data['method']         = $this->method;
            $data['redirect_url']   = $payload['redirect_url'];

            return view('pages.alipay-index', [
                'data' => $data,
            ]);
        }

        return view('welcome');
    }

    public function alipay_notify()
    {
        $payload    = request()->all();

        $attributes['type'] = 'AliPay';
        $attributes['log']  = 'notify';
        $attributes['log2'] = json_encode($payload);
        Log::create($attributes);


        /**
         * Generate checksum 
         * id(charge_id) + amount(with 4 decimal places) + currency + status + transaction_state + skey
         * and compare
         */

        $charge_id                = $payload['id'];
        $amount                   = $payload['amount'];
        $currency                 = $payload['currency'];
        $status                   = $payload['status'];
        $transaction_state        = $payload['transaction_state'];
        $skey                     = $this->payment->getSecretKey();
        $checksum                 = $payload['checksum'];

        $raw_str                  = $charge_id . number_format($amount, 4, '.', '') . $currency . $status . $transaction_state . $skey;
        $sha256_str               = hash('sha256', $raw_str);

        /** update transaction status */
        $trans                    = Transaction::where('charge_id', $charge_id)->first();

        $trans->status            = $status;
        $trans->transaction_state = $transaction_state;
        $trans->notify_result     = json_encode($payload);

        $same_id                  = $charge_id == $trans->charge_id ? true : false;

        if ($checksum == $sha256_str && $same_id) {
            if ($trans->status == 'success' && $trans->transaction_state == 'Authorized') {
                $trans->payment_status_id = PaymentStatus::where('payment_status', 'completed')->first()->id;
            }
            $trans->checksum_status   = 's';
            $trans->save();

            /**
             * call gate
             */
            $data = $this->payment->trimTrans( $trans );
            $this->payment->notifyGate( $data );

            return response('200 OK');
        } else {
            $trans->payment_status_id = PaymentStatus::where('payment_status', 'conflict')->first()->id;
            $trans->checksum_status   = 'f';
            $trans->save();

            /**
             * call gate
             */
            $data = $this->payment->trimTrans($trans);
            $this->payment->notifyGate($data);

            return response('409 Conflict');
        }
    }

    public function alipay_callback()
    {
        $payload = request()->all();

        $attributes['type'] = 'AliPay';
        $attributes['log']  = 'callback';
        $attributes['log2'] = json_encode($payload);
        Log::create($attributes);


        $charge_id                = $payload[$this->payment->getCallbackIdName()];
        $status                   = $payload['status'];

        /** update transaction status */
        $trans                    = Transaction::where('charge_id', $charge_id)->first();
        $trans->callback_result   = json_encode($payload);

        $same_id = $charge_id == $trans->charge_id ? true : false;

        if ($same_id && $status == 'true') {
            // if ($same_id && $status == 'true' && $trans->checksum_status == 's') { //this will work only notify always come before callback
            $payload = $this->payment->inquiryTransaction($charge_id);

            $trans->transaction_state = $payload['transaction_state'];
            $trans->status            = $payload['status'];
            $trans->inquiry_result    = json_encode($payload);

            /**
             * confirm payment was successful:
             *  status='success', transaction_state='Authorized', checksum_status=='s'
             */
            if ($trans->status == 'success' && $trans->transaction_state == 'Authorized') {
                $trans->payment_status_id = PaymentStatus::where('payment_status', 'completed')->first()->id;
                $msg = "The payment by AliPay was successful!";
            } else {
                $trans->payment_status_id = PaymentStatus::where('payment_status', 'declined')->first()->id;
                $msg = "The payment by AliPay was declined!";
            }
        } else {
            $trans->payment_status_id = PaymentStatus::where('payment_status', 'conflict')->first()->id;
            $msg = "The payment by AliPay could not confirm!";
        }
        $trans->save();


        if( $this->isolate==false ) {
            /**
             * call gate
             */
            $data = $this->payment->trimTrans($trans);

            return view('redirect', [
                'data'      => $data,
                'action'    => $this->payment->getCallbackUrl(),
                'msg'       => $msg,
            ]);
        }
        else {
            return view('pages.alipay-result', [
                'msg' => $msg,
            ]);
        }
    }
}
