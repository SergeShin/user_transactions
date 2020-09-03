<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const TYPE_DEBIT = "debit";
    const TYPE_CREDIT = "credit";

    protected $fillable = ['amount', 'type'];
}
