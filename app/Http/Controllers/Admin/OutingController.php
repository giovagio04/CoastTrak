<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outing;
use App\Models\Stage;
use App\Models\Guide;
use Illuminate\Http\Request;

class OutingController extends Controller
{
    public function approve(Outing $outing)
    {
        if ($outing->type !== 'user' || $outing->status !== 'pending') {
            return back()->with('error', 'Questa uscita non può essere approvata.');
        }

        $outing->update(['status' => 'published']);

        // Iscrivi automaticamente l'organizzatore all'uscita appena approvata
        if ($outing->organizer_id) {
            \App\Models\ParticipationRequest::updateOrCreate(
                [
                    'user_id' => $outing->organizer_id,
                    'outing_id' => $outing->id,
                ],
                [
                    'status' => 'accepted',
                ]
            );
        }

        return back()->with('success', 'Uscita tra utenti approvata con successo e resa visibile al pubblico!');
    }
    public function reject(Request $request, Outing $outing)
    {
        if ($outing->type !== 'user' || $outing->status !== 'pending') {
            return back()->with('error', 'Questa uscita non può essere rifiutata.');
        }

        $reason = $request->input('reason');
        $notes = $outing->notes;
        if ($reason) {
            $notes = $notes ? $notes . "\n\n[Motivazione Rifiuto]: " . $reason : "[Motivazione Rifiuto]: " . $reason;
        }

        $outing->update([
            'status' => 'rejected',
            'notes' => $notes
        ]);

        return back()->with('success', 'Uscita rifiutata.');
    }

    public function cancel(Request $request, Outing $outing)
    {
        if ($outing->status === 'cancelled') {
            return back()->with('error', 'Questa uscita è già annullata.');
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

        return back()->with('success', 'Uscita annullata con successo.');
    }


    public function index(Request $request)
    {
        $query = Outing::with(['guide', 'participationRequests']);

        // Filtro per Tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtro per Stato
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordinamento di default per data di creazione discendente
        $outings = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.outings.index', compact('outings'));
    }

    public function show(Outing $outing)
    {
        $outing->load(['guide', 'organizer', 'participationRequests.user']);
        return view('admin.outings.show', compact('outing'));
    }

    public function create()
    {
        $locations = ['Soverato', 'Petrizzi', 'Monterosso Calabro', 'Pizzo'];
        $guides = Guide::all();
        return view('admin.outings.create', compact('locations', 'guides'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_type' => 'required|in:standard,custom',
            'start_location' => 'required_if:route_type,custom|nullable|string',
            'end_location' => 'required_if:route_type,custom|nullable|string|different:start_location',
            'guide_id' => 'nullable|exists:guides,id',
            'date' => 'required|date|after_or_equal:today|before_or_equal:+3 years',
            'meeting_point' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1|max:50',
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

        $validated['type'] = 'official';
        $validated['status'] = 'published'; // Le uscite ufficiali nascono già pubblicate

        Outing::create($validated);

        return redirect()->route('admin.outings.index')->with('success', 'Uscita ufficiale creata con successo!');
    }

    public function edit(Outing $outing)
    {
        $locations = ['Soverato', 'Petrizzi', 'Monterosso Calabro', 'Pizzo'];
        $guides = Guide::all();
        return view('admin.outings.edit', compact('outing', 'locations', 'guides'));
    }

    public function update(Request $request, Outing $outing)
    {
        $validated = $request->validate([
            'route_type' => 'required|in:standard,custom',
            'start_location' => 'required_if:route_type,custom|nullable|string',
            'end_location' => 'required_if:route_type,custom|nullable|string|different:start_location',
            'guide_id' => 'nullable|exists:guides,id',
            'date' => 'required|date|before_or_equal:+3 years',
            'meeting_point' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1|max:50',
            'difficulty' => 'required|in:facile,medio,difficile',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,published,concluded,cancelled,rejected'
        ]);

        if ($validated['route_type'] === 'standard') {
            $validated['is_full_trail'] = true;
            $validated['start_location'] = null;
            $validated['end_location'] = null;
        } else {
            $validated['is_full_trail'] = false;
        }
        unset($validated['route_type']);

        $outing->update($validated);

        return redirect()->route('admin.outings.index')->with('success', 'Uscita aggiornata!');
    }

    public function destroy(Outing $outing)
    {
        $outing->delete();
        return redirect()->route('admin.outings.index')->with('success', 'Uscita eliminata!');
    }
}
