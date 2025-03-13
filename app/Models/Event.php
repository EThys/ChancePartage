<?php

namespace App\Models;

use App\Models\Reservation;
use App\Models\Winner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'event_date', 'max_participants', 'status'
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function winners()
    {
        return $this->hasMany(Winner::class);
    }
}
