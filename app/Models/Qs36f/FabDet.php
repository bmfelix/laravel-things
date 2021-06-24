<?php

namespace App\Models\Qs36f;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabDet extends Model
{
    use HasFactory;
    protected $table = 'QS36F.FABDET';
    public $timestamps = false;
    protected $connection = 'ibm';
    protected $primaryKey = null;
    public $incrementing = false;
}
