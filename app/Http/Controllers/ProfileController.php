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
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    public function showUser(\App\Models\User $user): View
    {
        if ($user->role === 'admin') {
            abort(403, 'Profilo non disponibile.');
        }

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user      = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $path;
        } elseif ($request->boolean('remove_profile_photo')) {
            if ($user->profile_photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
            }
            $validated['profile_photo_path'] = null;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.show')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $outingsToCancel = $user->outings()
            ->whereNotIn('status', ['concluded', 'rejected', 'cancelled'])
            ->with(['participationRequests' => function ($q) use ($user) {
                $q->where('status', 'accepted')
                  ->where('user_id', '!=', $user->id)
                  ->with('user');
            }])
            ->get();

        foreach ($outingsToCancel as $outing) {
            foreach ($outing->participationRequests as $participation) {
                if ($participation->user) {
                    $participation->user->notify(new \App\Notifications\SimpleNotification(
                        'Cammino annullato',
                        "Il cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} a cui eri iscritto è stato annullato perché l'organizzatore ha eliminato il proprio account.",
                        null
                    ));
                }
            }
        }

        $user->outings()
            ->whereNotIn('status', ['concluded', 'rejected', 'cancelled'])
            ->update(['status' => 'cancelled']);

        $user->outings()->update(['organizer_id' => null]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
