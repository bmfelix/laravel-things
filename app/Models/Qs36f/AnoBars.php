<?php

namespace App\Models\Qs36f;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnoBars extends Model
{
    use HasFactory;
    protected $table = 'QS36F.ANOBARS';
    public $timestamps = false;
    protected $connection = 'ibm';
    protected $primaryKey = 'id';
    public $incrementing = false;
}
