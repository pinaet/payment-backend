<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

use App\Http\Controllers\KPaymentsController;
use App\Log;
use App\PaymentStatus;
use App\Transaction;

class PagesController extends Controller
{
    public function test()
    {

        $http    = new Client;

        $payment = new KPaymentsController;
        $trans   = Transaction::find(58);
        $data  = $payment->trimTrans( $trans );

        // dd( $data );

        try {
            $response = $http->post($payment->getNotifyUrl(), [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [ 'data' => $data ],
            ]);
        } catch (\Exception $e) {
            $attributes['type'] = 'notifyGate - error';
            $attributes['log'] = json_encode($data);
            $attributes['log2'] = $e->getMessage();
            Log::create($attributes);
        }
        $payload    = json_decode((string) $response->getBody(), true);
        dd($payload );
    }
}
