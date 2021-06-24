<?php

namespace App\Models\Webspt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionVariable extends Model
{
    use HasFactory;
    protected $table = 'XL_WEBSPT.PW_SVARF';
    public $timestamps = false;
    protected $connection = 'ibm';
}
