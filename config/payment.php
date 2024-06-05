<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Write some description here
    |
    */
    'bank'              => env('PAYMENT_BANK', 'kbank-dev'),

    'currency'          => 'THB',

    'isolate'           => env('PAYMENT_ISOLATE', false),

    'kbank'             => [
        'callback_id'   => 'objectId',
        'callback_url'  => 'https://applications.harrowschool.ac.th/gate/public/order/callback',
        'notify_url'    => 'https://applications.harrowschool.ac.th/gate/public/order/notify',
        'pkey'          => 'pkey_test_683yajTS8Owc9Tt1xxaHlm7DvUZ7LmKt4F',
        'skey'          => 'skey_test_689Egn2ZbMDhj01zXiVQxmh1nDya0jRFdx',
        'base_url'      => 'https://dev-kpaymentgateway-services.kasikornbank.com',
        'ui_url'        => 'https://dev-kpaymentgateway.kasikornbank.com/ui/v2/kpayment.min.js',
        'alipay'        => [
            'mid'       => '401001086002001',
            'tid'       => '70341601',
            'currency'  => 'THB',
        ],
        'full-mcc'      => [ //full-mcc
            'mid'       => '401001086001001',
            'tid'       => '70341602',
            'currency'  => 'THB',
        ],
        'unionpay'      => [
            'mid'       => null,
            'tid'       => null,
            'currency'  => 'THB',
            'transaction_state' => 'Initialize',
        ],
        'wechat'        => [
            'mid'       => '401001086003001',
            'tid'       => '70341603',
            'currency'  => 'THB',
        ],
    ],

    'kbank-prod'        => [
        'callback_id'   => 'objectId',
        'callback_url'  => env( 'KBANK_CALLBACK_URL' , 'https://applications.harrowschool.ac.th/gate/public/order/callback' ),
        'notify_url'    => env( 'KBANK_NOTIFY_URL'   , 'https://applications.harrowschool.ac.th/gate/public/order/notify'   ),
        'pkey'          => env( 'KBANK_PKEY'         , 'pkey_prod_291oLBh7DrHbgidvT2WypEdLH1vxdBZtdGi'                      ),
        'skey'          => env( 'KBANK_SKEY'         , 'skey_prod_291B1WhGw2PXhmvyRh8fyF50thycmEjOhmC'                      ),
        'base_url'      => env( 'KBANK_BASE_URL'     , 'https://kpaymentgateway-services.kasikornbank.com'                  ),
        'ui_url'        => env( 'KBANK_UI_URL'       , 'https://kpaymentgateway.kasikornbank.com/ui/v2/kpayment.min.js'     ),
        'alipay'        => [
            'mid'       => '401012018653001',
            'tid'       => '74446674',
            'currency'  => 'THB',
        ],
        'full-mcc'      => [
            'mid'       => '',
            'tid'       => '',
            'currency'  => 'THB',
        ],
        'unionpay'      => [
            'mid'       => '401012018611001',
            'tid'       => '74446666',
            'currency'  => 'THB',
            'transaction_state' => 'Initialize',
        ],
        'wechat'        => [
            'mid'       => '401012018661001',
            'tid'       => '74446675',
            'currency'  => 'THB',
        ],
    ],

    'kbank-dev'         => [
        'callback_id'   => 'objectId',
        'callback_url'  => 'https://histest.harrowschool.ac.th/gate/public/order/callback',
        'notify_url'    => 'https://histest.harrowschool.ac.th/gate/public/order/notify',
        'pkey'          => 'pkey_test_683yajTS8Owc9Tt1xxaHlm7DvUZ7LmKt4F',
        'skey'          => 'skey_test_689Egn2ZbMDhj01zXiVQxmh1nDya0jRFdx',
        'base_url'      => 'https://dev-kpaymentgateway-services.kasikornbank.com',
        'ui_url'        => 'https://dev-kpaymentgateway.kasikornbank.com/ui/v2/kpayment.min.js',
        'alipay'        => [
            'mid'       => '401001086002001',
            'tid'       => '70341601',
            'currency'  => 'THB',
        ],
        'full-mcc'      => [ //full-mcc
            'mid'       => '401001086001001',
            'tid'       => '70341602',
            'currency'  => 'THB',
        ],
        'unionpay'      => [
            'mid'       => null,
            'tid'       => null,
            'currency'  => 'THB',
            'transaction_state' => 'Initialize',
        ],
        'wechat'        => [
            'mid'       => '401001086003001',
            'tid'       => '70341603',
            'currency'  => 'THB',
        ],
    ],

];
