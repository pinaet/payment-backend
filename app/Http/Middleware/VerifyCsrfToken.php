<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/kbank/callback',

        '/kbank/wechat/create',
        '/kbank/wechat/store',
        '/kbank/wechat/callback',
        '/kbank/wechat/notify',
        '/kbank/wechat/check',
        '/kbank/wechat/remove',

        '/kbank/alipay/create',
        '/kbank/alipay/store',
        '/kbank/alipay/callback',
        '/kbank/alipay/notify',

        '/kbank/tpn-union/create',
        '/kbank/tpn-union/store',
        '/kbank/tpn-union/callback',
        '/kbank/tpn-union/notify',

        '/ttb/2c2p/card/create',
        '/ttb/2c2p/card/call/{result}',
        '/ttb/2c2p/card/backend',
    ];
}
