<?php

namespace App\Models\Nexus53;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSUserFile extends Model
{
    use HasFactory;
    protected $table = 'XL_NEXUS53.SMUSRF';
    public $timestamps = false;
    protected $connection = 'ibm';
}
