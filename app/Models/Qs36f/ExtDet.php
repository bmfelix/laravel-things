<?php

namespace App\Models\Qs36f;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtDet extends Model
{
    use HasFactory;
    protected $table = 'QS36F.EXTDET';
    public $timestamps = false;
    protected $connection = 'ibm';
    protected $primaryKey = null;
    public $incrementing = false;
}
