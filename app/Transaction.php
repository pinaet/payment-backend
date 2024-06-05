<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected   $guarded   = [];

    public function payment_status()
    {
        return $this->hasOne('App\PaymentStatus', 'id', 'payment_status_id')->first();
    }
}
