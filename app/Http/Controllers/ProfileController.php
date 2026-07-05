<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile dashboard (read-only).
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the specified user's profile.
     */
    public function showUser(\App\Models\User $user): View
    {
        // Nascondo il profilo degli admin: non devono essere visibili/esplorabili dagli utenti
        if ($user->role === 'admin') {
            abort(403, 'Profilo non disponibile.');
        }

        return view('profile.show', [
            'user' => $user,
        ]);
    }



    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('profile_photo')) {
            // Elimino la vecchia foto prima di salvare quella nuova, per non lasciare file orfani
            if ($user->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $path;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.show')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Se l'utente organizza ancora uscite attive, non può cancellare l'account:
        // la cancellazione a cascata farebbe sparire uscite, partecipazioni e credenziali degli altri
        $activeOutingsCount = $user->outings()
            ->whereIn('status', ['published', 'pending'])
            ->count();

        if ($activeOutingsCount > 0) {
            return Redirect::route('profile.show')->withErrors([
                'account_deletion' => "Non puoi eliminare l'account mentre sei organizzatore di {$activeOutingsCount} uscita/e attiva/e. Annulla prima le tue uscite pubblicati o in attesa di approvazione.",
            ], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
