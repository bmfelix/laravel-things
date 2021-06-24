<?php

namespace App\Models\Nexus53;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMasterFile extends Model
{
    use HasFactory;
    protected $table = 'XL_NEXUS53.SMGRPMF';
    public $timestamps = false;
    protected $connection = 'ibm';
}
