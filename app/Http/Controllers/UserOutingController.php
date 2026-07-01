<?php

namespace App\Http\Controllers;

use App\Models\Outing;
use App\Models\Stage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserOutingController extends Controller
{
    public function create()
    {
        if (!auth()->user()->canCreateOuting()) {
            return redirect()->route('dashboard')->with('error', 'Devi aver completato l\'intero cammino (tutte le tappe) per poter proporre un\'uscita.');
        }

        $locations = ['Soverato', 'Petrizzi', 'Monterosso Calabro', 'Pizzo'];
        return view('user.outings.create', compact('locations'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canCreateOuting()) {
            abort(403);
        }

        $validated = $request->validate([
            'route_type' => 'required|in:standard,custom',
            'start_location' => 'required_if:route_type,custom|nullable|string',
            'end_location' => 'required_if:route_type,custom|nullable|string|different:start_location',
            'date' => 'required|date|after:today|before_or_equal:+3 years',
            'meeting_point' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:2|max:15',
            'difficulty' => 'required|in:facile,medio,difficile',
            'notes' => 'nullable|string'
        ]);

        if ($validated['route_type'] === 'standard') {
            $validated['is_full_trail'] = true;
            $validated['start_location'] = null;
            $validated['end_location'] = null;
        } else {
            $validated['is_full_trail'] = false;
        }
        unset($validated['route_type']);

        $validated['type'] = 'user';
        $validated['organizer_id'] = auth()->id();
        $validated['status'] = 'pending';
        // L'amministratore ha 24 ore di tempo per approvare l'uscita
        $validated['approval_deadline'] = Carbon::now()->addHours(24);

        Outing::create($validated);

        return redirect()->route('dashboard')->with('success', 'La tua uscita è stata proposta! L\'amministratore la valuterà entro 24 ore.');
    }

    /**
     * Cancel the specified outing by the organizer.
     */
    public function cancel(Request $request, Outing $outing)
    {
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Non hai i permessi per annullare questo cammino.');
        }

        if ($outing->status === 'cancelled') {
            return back()->with('error', 'Questa uscita è già stata annullata.');
        }

        $reason = $request->input('reason');
        $notes = $outing->notes;
        if ($reason) {
            $notes = $notes ? $notes . "\n\n[Motivazione Annullamento]: " . $reason : "[Motivazione Annullamento]: " . $reason;
        }

        $outing->update([
            'status' => 'cancelled',
            'notes' => $notes
        ]);

        return back()->with('success', 'Il cammino è stato annullato con successo.');
    }
}
