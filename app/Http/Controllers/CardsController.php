<?php

namespace App\Http\Controllers;

use App\Log;
use DateTime;
use App\Transaction;
use App\PaymentStatus;
use App\Services\TTBPayment;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    private $method     = 'card';
    private $payment    = '';
    private $ttb_pay    = '';
    private $isolate    = false;
    
    public function __construct()
    {
        $this->payment  = new KPaymentsController;
        $this->ttb_pay  = new TTBPayment;
        $this->isolate  = config('payment.isolate');
    }

    public function card(Request $request)
    {
        $payload    = request()->all();

        $attributes['type'] = 'Card_2C2P';
        $attributes['log']  = 'notify';
        $attributes['log2'] = json_encode($payload);
        Log::create($attributes);
    }

    public function create(Request $request)
    {
        /*
            "reference_order" => "123456_002"
            "amount" => "2.01"
            "currency" => "THB"
            "autoredirect" => "Y"
            "posturl" => "https://histest.harrowschool.ac.th/gate/public/order/t-callback"
            "customer_name" => "140.123456_002" //gate.transaction.id + reference_order
            "customer_email" => "naet_ph@harrowschool.ac.th"
            "product_name" => "Tuition Fee 2024"
         */
        $product_id      = $request->product_id;
        $reference_order = $request->reference_order;
        $amount          = $request->amount;
        $currency        = $request->currency;
        $customer_id     = $request->customer_id;
        $customer_name   = $request->customer_name;
        $customer_email  = $request->customer_email;
        $customer_phone  = $request->customer_phone;
        $autoredirect    = $request->autoredirect;
        $posturl         = $request->posturl;
        $product_name    = $request->product_name;

        return view('pages.please-wait-ttb', [
            'product_id'      => $product_id,
            'reference_order' => $reference_order,
            'amount'          => $amount,
            'currency'        => $currency,
            'customer_id'     => $customer_id,
            'customer_name'   => $customer_name,
            'customer_email'  => $customer_email,
            'customer_phone'  => $customer_phone,
            'autoredirect'    => $autoredirect,
            'posturl'         => $posturl,
            'product_name'    => $product_name,
        ]);
    }

    public function exec_request(Request $request)
    {
        $product_id      = $request->product_id;
        $reference_order = $request->reference_order;
        $amount          = $request->amount;
        $currency        = $request->currency;
        $customer_id     = $request->customer_id;
        $customer_name   = $request->customer_name;
        $customer_email  = $request->customer_email;
        $customer_phone  = $request->customer_phone;
        $autoredirect    = $request->autoredirect;
        $posturl         = $request->posturl;
        $product_name    = $request->product_name;

        $order_id        = $this->generateGUID();
        $json_string = '
        {
            "apiRequest": {
                "requestMessageID": "' . $order_id . '",
                "requestDateTime": "' . $this->getIsoDateTimeWithMilliseconds() . '"
            },
            "transactionAmount": {
                "amountText": "' . $this->formatAmount($amount) . '",
                "currencyCode": "' . $currency . '",
                "decimalPlaces": 2,
                "amount": ' . $amount . '
            },
            "notificationURLs": {
                "confirmationURL": "' . env('APP_URL_FULL') . '/ttb/2c2p/card/call/confirm",
                "failedURL": "' .       env('APP_URL_FULL') . '/ttb/2c2p/card/call/fail",
                "cancellationURL": "' . env('APP_URL_FULL') . '/ttb/2c2p/card/call/cancel",
                "backendURL": "' .      env('APP_URL_FULL') . '/ttb/2c2p/card/call/backend"
            },
            "generalPayerDetails": {
                "personType": "general"
            },
            "officeId": "' . env('TTB_OFFICE_ID') . '",
            "orderNo": "' . $reference_order . '",
            "productDescription": "' . $product_name . '",
            "mcpFlag": "'.env('TTB_MCP_Flag').'",
            "request3dsFlag": "'.env('TTB_3DS_Flag').'"
        }
        ';
        // $json = json_decode($json_string);///dd($json,$json_string,$amount);
        $payload                = $this->ttb_pay->ExecuteJose( $json_string );// dd($payload, json_decode($payload),json_decode($payload, true),json_decode($payload)->response );

        if(isset($payload['redirect'])){
            return view('pages.message', [
                'title'   => $payload['title'],
                'message' => $payload['message'],
            ]);
        }

        $payload                = json_decode($payload,true)['response']; //dd($payload->data->paymentIncompleteResult->paymentStatusInfo,$payload['data']['paymentIncompleteResult']['paymentStatusInfo']);

        $payment_url            = $payload['data']['paymentPage']['paymentPageURL'];
        $payment_status         = json_encode($payload['data']['paymentIncompleteResult']['paymentStatusInfo']);
        $controllerInternalID   = $payload['data']['paymentIncompleteResult']['controllerInternalID'];

        /**
         * store data
         */
        $attributes                         = array();
        $attributes['ref']                  = $customer_name; //customer_name
        $attributes['amount']               = $amount;
        $attributes['currency']             = $currency;
        $attributes['description']          = $product_name;
        $attributes['source_type']          = $this->method;
        $attributes['reference_order']      = $reference_order;
        $attributes['charge_id']            = $controllerInternalID;
        $attributes['order_id']             = $order_id;
        $attributes['transaction_state']    = 'Initialize';
        $attributes['status']               = $payment_status;
        $attributes['notify_result']        = '';
        $attributes['callback_result']      = '';
        $attributes['payment_status_id']    = PaymentStatus::where('payment_status', 'pending')->get()[0]->id;

        Transaction::create($attributes);

        if(env('TTB_LOG_CREATE', false)){
            $attributes             = array();
            $attributes['type']     = $this->method;
            $attributes['log']      = 'create';
            $attributes['log2']     = json_encode($attributes);
            $attributes['response'] = $json_string;
            Log::create($attributes);
        }

        //redirect user to the payment_url
        return redirect( $payment_url );
    }

    public function card_callback(Request $request, $result)
    {
        /*
        $response			= $_POST['RESPONSE'];
        $appcode			= $_POST['APPCODE'];
        $invoiceno			= $_POST['INVOICENO'];
        $pan				= $_POST['PAN'];
        $expdate			= $_POST['EXPDATE'];
        $amount				= $_POST['AMOUNT'];
         */

        //retrieve the record to update
        $payload    = $request->all();

        $attributes['type'] = $this->method;
        $attributes['log']  = $result;
        $attributes['log2'] = json_encode($payload);
        Log::create($attributes);


        $charge_id = $payload['controllerInternalId'];
        $trans = Transaction::where('charge_id',$charge_id)->first();


        if ($result == 'confirm') {
            $trans->transaction_state = 'Authorized';
            $trans->payment_status_id = PaymentStatus::where('payment_status', 'completed')->get()[0]->id;
            $msg                      = "The payment by Card was successful!";
        } elseif ($result == 'cancel') {
            $trans->transaction_state = 'Cancelled';
            $trans->payment_status_id = PaymentStatus::where('payment_status', 'declined')->get()[0]->id;
            $msg                      = "The payment by Card was declined!";
        } elseif ($result == 'fail') {
            $trans->transaction_state = 'Failed';
            $trans->payment_status_id = PaymentStatus::where('payment_status', 'conflict')->get()[0]->id;
            $msg                      = "The payment by Card could not confirm!";
        }
        $trans->status            = $result;
        $trans->inquiry_result    = json_encode($payload);
        $trans->save();


        if( $this->isolate==false ) {
            /**
             * call gate
             */
            $data = $this->payment->trimTrans($trans);
            $this->payment->notifyGate($data);

            return view('redirect', [
                'data'      => $data,
                'action'    => $this->payment->getCallbackUrl(),
                'msg'       => $msg,
            ]);
        }
        else {
            return view('pages.card-result', [
                'msg' => $msg,
            ]);
        }
    }

    public function backend(Request $request)
    {
        $payload    = request()->all();

        $attributes['type'] = 'Card_2C2P';
        $attributes['log']  = 'backend';
        $attributes['log2'] = json_encode($payload);
        Log::create($attributes);
    }

    public function getIsoDateTimeWithMilliseconds()
    {
        // Create a DateTime object for the current time
        $dateTime = new DateTime();

        // Format the date and time
        $isoDateTime = $dateTime->format("Y-m-d\TH:i:s");

        // Get microseconds (as a fraction of a second)
        $microseconds = $dateTime->format("u");

        // Convert microseconds to milliseconds
        $milliseconds = substr($microseconds, 0, 3);

        // Combine the formatted date and milliseconds
        $isoDateTimeWithMilliseconds = $isoDateTime . '.' . $milliseconds . 'Z';

        return $isoDateTimeWithMilliseconds;
    }

    public function generateGUID()
    {
        //UUID
        $data = openssl_random_pseudo_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100 (UUID version 4)
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10 (UUID variant)

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function formatAmount($amount)
    {
        // Format the amount to 2 decimal places
        $formattedAmount = number_format($amount, 2, '', '');

        // Pad the amount with leading zeros to make it 12 characters long
        return str_pad($formattedAmount, 12, '0', STR_PAD_LEFT);
    }
}
