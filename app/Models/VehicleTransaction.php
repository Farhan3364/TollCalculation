<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleTransaction extends Model
{
    use HasFactory;

    protected $table = 'vehicle_transactions';

    protected $fillable = [
        'id', 'entry_interchange_id', 'number_plate', 'entry_date_time', 'exit_interchange_id', 'exit_date_time'
    ];
}
