<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\KPaymentsController;
use App\Payment;

class QRsController extends Controller
{
    public function qr()
    {
        $payment   = new Payment;
        $k_payment = new KPaymentsController;

        //prepare all mendatory data
        $data = array();
        $data['ui_url']   = $payment->getUiUrl();
        $data['pkey']     = $payment->getPublicKey();
        $data['total']    = 12;
        $data['method']   = 'qr';
        $data['order_id'] = $k_payment->createOrder('qr');
        $data['mid']      = $payment->getMerchantId('qr');
        $data['currency'] = $payment->getCurrency();

        // dd( $data );

        return view('pages.qr.index', [
            'data' => $data,
        ]);
    }
}
