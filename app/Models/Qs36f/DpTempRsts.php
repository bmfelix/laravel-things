<?php

namespace App\Models\Qs36f;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DpTempRsts extends Model
{
    use HasFactory;
    protected $table = 'QS36F.DPTEMPRSTS';
    public $timestamps = false;
    protected $connection = 'ibm';
    protected $primaryKey = null;
    public $incrementing = false;
}
