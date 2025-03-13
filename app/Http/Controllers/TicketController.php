<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Resources\ticketCollection;

class TicketController extends Controller
{
    public function index()
    {
        $tickets=Ticket::with(['reservation'])->get();
        return new ticketCollection(resource: $tickets);
    }
}
