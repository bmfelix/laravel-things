<?php

namespace App\Models\Qs36f;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnoMoveTag extends Model
{
    use HasFactory;
    protected $table = 'QS36F.ANOMOVETAG';
    public $timestamps = false;
    protected $connection = 'ibm';
    protected $primaryKey = 'id';
    public $incrementing = false;
}
