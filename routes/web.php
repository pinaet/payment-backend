<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get(     '/'                              , function () { return view('welcome'); }           );

Route::get(     '/me'                            , 'PagesController@index'                           );
Route::get(     '/test'                          , 'PagesController@test'                            );
Route::get(     '/home'                          , 'PagesController@home'                            );
Route::get(     '/logout'                        , 'PagesController@Logout'                          );

Route::get(     '/kbank/callback'                , 'KPaymentsController@callback'                    );

Route::get(     '/kbank/qr'                      , 'QRsController@qr'                                );
Route::get(     '/kbank/qr/callback'             , 'QRsController@qr_callback'                       );
Route::get(     '/kbank/qr/notify'               , 'QRsController@qr_notify'                         );

Route::get(     '/kbank/wechat'                  , 'WeChatController@wechat'                         );
Route::post(    '/kbank/wechat/create'           , 'WeChatController@create'                         );
Route::post(    '/kbank/wechat/store'            , 'WeChatController@store'                          );
Route::post(    '/kbank/wechat/callback'         , 'WeChatController@wechat_callback'                );
Route::post(    '/kbank/wechat/notify'           , 'WeChatController@wechat_notify'                  );
Route::post(    '/kbank/wechat/check'            , 'WeChatController@checkTrans'                     );
Route::post(    '/kbank/wechat/remove'           , 'WeChatController@remove'                         );

Route::get(     '/kbank/alipay'                  , 'AliPaysController@alipay'                        );
Route::post(    '/kbank/alipay/create'           , 'AliPaysController@create'                        );
Route::post(    '/kbank/alipay/store'            , 'AliPaysController@store'                         );
Route::post(    '/kbank/alipay/callback'         , 'AliPaysController@alipay_callback'               );
Route::post(    '/kbank/alipay/notify'           , 'AliPaysController@alipay_notify'                 );

Route::get(     '/kbank/tpn-union'               , 'TpnUnionPaysController@unionpay'                 );
Route::post(    '/kbank/tpn-union/create'        , 'TpnUnionPaysController@create'                   );
Route::post(    '/kbank/tpn-union/store'         , 'TpnUnionPaysController@store'                    );
Route::post(    '/kbank/tpn-union/callback'      , 'TpnUnionPaysController@unionpay_callback'        );
Route::post(    '/kbank/tpn-union/notify'        , 'TpnUnionPaysController@unionpay_notify'          );

Route::get(     '/ttb/2c2p/card'                 , 'CardsController@card'                            );
Route::post(    '/ttb/2c2p/card/create'          , 'CardsController@create'                          );
Route::post(    '/ttb/2c2p/card/execute-request' , 'CardsController@exec_request'                    );
Route::get(     '/ttb/2c2p/card/call/{result}'   , 'CardsController@card_callback'                   );
Route::get(     '/ttb/2c2p/card/backend'         , 'CardsController@backend'                         );