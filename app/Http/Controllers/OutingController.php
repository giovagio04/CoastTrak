<?php

namespace App\Http\Controllers;

use App\Models\Outing;
use Illuminate\Http\Request;

class OutingController extends Controller
{
    public function index()
    {
        // Recupero solo le uscite visibili al pubblico
        $outings = Outing::whereIn('status', ['published'])
            ->with(['organizer', 'guide'])
            ->orderBy('date', 'asc')
            ->get();

        return view('outings.index', compact('outings'));
    }

    public function show(Outing $outing)
    {
        // Controllo accessi: se l'uscita non è pubblica, solo admin o organizzatore possono vederla
        if (!in_array($outing->status, ['published', 'concluded'])) {
            if (!auth()->check() || (auth()->user()->role !== 'admin' && $outing->organizer_id !== auth()->id())) {
                abort(403, 'Accesso negato: l\'uscita non è ancora visibile al pubblico.');
            }
        }

        $outing->load(['organizer', 'guide', 'participationRequests.user']);
        
        return view('outings.show', compact('outing'));
    }
}
