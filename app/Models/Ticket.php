<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'last_name',
        'first_name',
        'ticket_number',
        'amount',
        'reservation_date',
        'reservation_order'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

}
