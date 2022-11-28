<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interchange extends Model
{
    use HasFactory;

    protected $table = 'interchanges';

    protected $fillable = ['id', 'name', 'km'];
}
