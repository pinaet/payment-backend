<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\KPaymentsController;
use App\Log;
use App\Transaction;
use App\PaymentStatus;

class WeChatController extends Controller
{
    private $method     = 'wechat';
    private $payment    = '';
    private $isolate    = false;

    public function __construct()
    {
        $this->payment  = new KPaymentsController;
        $this->isolate  = config('payment.isolate');
    }

    public function create(){
        $order            = json_decode( request()->order, true );
        $order['amount']  = $order['total_amount'];
        $payload          = $this->payment->createTempOrder($order);

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
        $this->payment->storeOrder( $order, $payload );

        return response('200 OK');
    }

    public function wechat()
    {
        /**
         * prepare order
         */
        $order['ref']               = 1;
        $order['amount']            = 1;
        $order['currency']          = $this->payment->getCurrency();
        $order['description']       = 'Awesome Product';
        $order['source_type']       = $this->method;
        $order['reference_order']   = '12345';

        $payload    = $this->payment->createOrder($order);

        //prepare all mendatory data
        $data = array();
        $data['ui_url']   = $this->payment->getUiUrl();
        $data['pkey']     = $this->payment->getPublicKey();
        $data['amount']   = $order['amount'];
        $data['method']   = $this->method;
        $data['order_id'] = $payload['id'];
        $data['currency'] = $this->payment->getCurrency();


        // dd( $data );

        return view('pages.wechat-index', [
            'data' => $data,
        ]);
    }

    public function wechat_notify()
    {
        $payload    = request()->all();

        $attributes['type'] = 'WeChatPay';
        $attributes['log']  = 'notify';
        $attributes['log2'] = json_encode($payload);
        Log::create($attributes);


        /**
         * Generate checksum 
         * id(charge_id) + amount(with 4 decimal places) + currency + status + transaction_state + skey
         * and compare
         */

        $charge_id                = $payload['id'];
        $order_id                 = $payload['order_id']; //different from AliPay and UnionPay
        $amount                   = $payload['amount'];
        $currency                 = $payload['currency'];
        $status                   = $payload['status'];
        $transaction_state        = $payload['transaction_state'];
        $skey                     = $this->payment->getSecretKey();
        $checksum                 = $payload['checksum'];

        $raw_str                  = $charge_id . number_format($amount, 4, '.', '') . $currency . $status . $transaction_state . $skey;
        $sha256_str               = hash('sha256', $raw_str);

        /** update transaction status */
        $trans                    = Transaction::where('order_id', $order_id)->first();

        $trans->charge_id         = $charge_id; //different from AliPay and UnionPay
        $trans->status            = $status;
        $trans->transaction_state = $transaction_state;
        $trans->notify_result     = json_encode($payload);

        $same_id                  = $order_id == $trans->order_id ? true : false;

        if ($checksum == $sha256_str && $same_id) {
            if ($trans->status == 'success' && $trans->transaction_state == 'Authorized') {
                $trans->payment_status_id = PaymentStatus::where('payment_status', 'completed')->first()->id;
            }
            $trans->checksum_status   = 's';
            $trans->save();

            /**
             * call gate
             */
            $data = $this->payment->trimTrans($trans);
            $this->payment->notifyGate($data);

            return response('200 OK');
        } else {
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

    public function wechat_callback()
    {
        $payload = request()->all();

        $attributes['type'] = 'WeChatPay';
        $attributes['log']  = 'callback';
        $attributes['log2'] = json_encode($payload);
        Log::create($attributes);


        $charge_id                = $payload['chargeId'];

        /** update transaction status */
        $trans                    = Transaction::where('charge_id', $charge_id)->first();
        $trans->callback_result   = json_encode($payload);

        $same_id = $charge_id == $trans->charge_id ? true : false;

        if ($same_id) { //different from AliPay and UnionPay
        // if ($same_id && $trans->checksum_status == 's') { //will work only notify come before callback
            $payload = $this->payment->inquiryTransactionQr($charge_id);

            $trans->transaction_state = $payload['transaction_state'];
            // $trans->status            = $payload['status']; //different from AliPay and UnionPay - status is '' at callback
            $trans->inquiry_result    = json_encode($payload);

            /**
             * confirm payment was successful:
             *  status='success', transaction_state='Authorized', checksum_status=='s'
             */
            if ($trans->status == 'success' && $trans->transaction_state == 'Authorized') {
                $trans->payment_status_id = PaymentStatus::where('payment_status', 'completed')->first()->id;
                $msg = "The payment by WeChatPay was successful!";
            } else {
                $trans->payment_status_id = PaymentStatus::where('payment_status', 'declined')->first()->id;
                $msg = "The payment by WeChatPay was declined!";
            }
        } else {
            $trans->payment_status_id = PaymentStatus::where('payment_status', 'conflict')->first()->id;
            $msg = "The payment by WeChatPay could not confirm!";
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
            return view('pages.wechat-result', [
                'msg' => $msg,
            ]);
        }
    }

    public function checkTrans( )
    {
        $data  = request()->all();
        $trans = Transaction::where( 'ref', $data['ref'] )
                            ->where( 'order_id', $data['order_id'])->first();

        $data['payment_status'] = $trans->payment_status()['payment_status'];
        $data['charge_id']      = $trans['charge_id'];

        return $data;
    }

    public function remove()
    {
        $data  = request()->all();
        $status= PaymentStatus::where( 'payment_status', 'pending' )->first()['id'];

        $trans = Transaction::where( 'ref', $data['ref'] )
                            ->where( 'order_id', $data['order_id'] )
                            ->where( 'payment_status_id', $status )
                            ->first();
        $trans->delete();

        return response( '200 OK' );
    }
}
