<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Stage;
use App\Models\Guide;
use App\Models\Outing;
use App\Models\ParticipationRequest;
use App\Models\DigitalCredential;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creazione Admin
        $admin = User::create([
            'name' => 'Amministratore',
            'email' => 'admin@coasttrack.it',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Creazione Utenti normali
        $user1 = User::create([
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $user2 = User::create([
            'name' => 'Luigi Verdi',
            'email' => 'luigi@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $user3 = User::create([
            'name' => 'Maria Bianchi',
            'email' => 'maria@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $user4 = User::create([
            'name' => 'Stefano Neri',
            'email' => 'stefano@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $user5 = User::create([
            'name' => 'Awais',
            'email' => 'awais@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $userGiulia = User::create([
            'name' => 'Giulia Neri',
            'email' => 'giulia@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // 3. Tappe del cammino
        $stage1 = Stage::create([
            'number' => 1,
            'name' => 'Tappa 1 – Soverato → Petrizzi',
            'description' => 'La prima tappa del cammino che dal Mar Ionio entra nell\'entroterra.',
            'distance_km' => 12.5,
            'start_point' => 'Soverato',
            'end_point' => 'Petrizzi',
            'difficulty' => 'facile'
        ]);

        $stage2 = Stage::create([
            'number' => 2,
            'name' => 'Tappa 2 – Petrizzi → Monterosso Calabro',
            'description' => 'Tappa centrale impegnativa tra i boschi delle Serre.',
            'distance_km' => 23.5,
            'start_point' => 'Petrizzi',
            'end_point' => 'Monterosso Calabro',
            'difficulty' => 'difficile'
        ]);

        $stage3 = Stage::create([
            'number' => 3,
            'name' => 'Tappa 3 – Monterosso Calabro → Pizzo',
            'description' => 'L\'arrivo sul Mar Tirreno.',
            'distance_km' => 19.0,
            'start_point' => 'Monterosso Calabro',
            'end_point' => 'Pizzo',
            'difficulty' => 'medio'
        ]);

        // 4. Guide
        $guide1 = Guide::create([
            'name' => 'Giuseppe Trek',
            'description' => 'Esperto del territorio calabrese.',
            'experience_count' => 15,
            'specializations' => 'Flora e fauna locale'
        ]);

        // 5. Uscite Ufficiali
        $outing1 = Outing::create([
            'type' => 'official',
            'is_full_trail' => false,
            'start_location' => $stage1->start_point,
            'end_location' => $stage1->end_point,
            'guide_id' => $guide1->id,
            'date' => Carbon::now()->addDays(10),
            'meeting_point' => 'Piazza di Soverato, ore 8:00',
            'max_participants' => 20,
            'difficulty' => 'facile',
            'status' => 'open'
        ]);

        // Uscita passata (conclusa)
        $outingPast = Outing::create([
            'type' => 'official',
            'is_full_trail' => false,
            'start_location' => $stage1->start_point,
            'end_location' => $stage1->end_point,
            'guide_id' => $guide1->id,
            'date' => Carbon::now()->subDays(5),
            'meeting_point' => 'Piazza di Soverato',
            'max_participants' => 15,
            'difficulty' => 'facile',
            'status' => 'concluded'
        ]);

        // 6. Partecipazioni all'uscita conclusa (Genera la Credenziale)
        ParticipationRequest::create([
            'outing_id' => $outingPast->id,
            'user_id' => $user1->id,
            'status' => 'accepted'
        ]);

        DigitalCredential::create([
            'user_id' => $user1->id,
            'stage_id' => $stage1->id,
            'outing_id' => $outingPast->id,
            'completed_at' => Carbon::now()->subDays(4)
        ]);

        // Uscita completa passata (conclusa) per permettere a Maria di aver completato l'intero cammino
        $outingFullPast = Outing::create([
            'type' => 'official',
            'is_full_trail' => true,
            'start_location' => 'Soverato',
            'end_location' => 'Pizzo',
            'guide_id' => $guide1->id,
            'date' => Carbon::now()->subDays(10),
            'meeting_point' => 'Piazza di Soverato',
            'max_participants' => 15,
            'difficulty' => 'difficile',
            'status' => 'concluded'
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingFullPast->id,
            'user_id' => $user3->id,
            'status' => 'accepted'
        ]);

        DigitalCredential::create([
            'user_id' => $user3->id,
            'stage_id' => null, // null indica il cammino completo
            'outing_id' => $outingFullPast->id,
            'completed_at' => Carbon::now()->subDays(9)
        ]);

        // 7. Uscite tra utenti (create da user1 perché ha completato una tappa)
        $outingUser = Outing::create([
            'type' => 'user',
            'is_full_trail' => false,
            'start_location' => $stage2->start_point,
            'end_location' => $stage2->end_point,
            'organizer_id' => $user1->id,
            'date' => Carbon::now()->addDays(15),
            'meeting_point' => 'Piazza di Petrizzi',
            'max_participants' => 5,
            'difficulty' => 'difficile',
            'status' => 'published', // simuliamo che sia già approvata
            'notes' => 'Portare acqua in abbondanza!'
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingUser->id,
            'user_id' => $user1->id,
            'status' => 'accepted'
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingUser->id,
            'user_id' => $user2->id,
            'status' => 'pending'
        ]);

        // 8. Notifiche di test per gli utenti
        $user1->notify(new \App\Notifications\SimpleNotification(
            'Benvenuto su CoastTrak!',
            'Grazie per esserti iscritto alla piattaforma. Esplora i cammini e partecipa alle uscite.'
        ));

        $user1->notify(new \App\Notifications\SimpleNotification(
            'Nuova uscita approvata',
            'La tua uscita da Petrizzi a Monterosso Calabro è stata approvata dall\'amministratore.',
            route('outings.show', $outingUser->id)
        ));

        $user1->notify(new \App\Notifications\SimpleNotification(
            'Richiesta di partecipazione',
            'Luigi Verdi ha chiesto di unirsi alla tua uscita da Petrizzi a Monterosso.'
        ));

        $user1->notify(new \App\Notifications\SimpleNotification(
            'Previsioni Meteo',
            'Meteo favorevole previsto per il weekend! Ricorda di idratarti adeguatamente.'
        ));

        $user1->notify(new \App\Notifications\SimpleNotification(
            'Suggerimento Equipaggiamento',
            'Consigliamo scarpe da trekking impermeabili per affrontare il fango presente sul sentiero della Tappa 1.'
        ));

        $user1->notify(new \App\Notifications\SimpleNotification(
            'Tappa Completata!',
            'Ottimo lavoro! Hai completato con successo la tappa Soverato -> Petrizzi.'
        ));

        $user1->notify(new \App\Notifications\SimpleNotification(
            'Timbro Credenziale',
            'La tua credenziale digitale è stata timbrata con successo per la Tappa 1.'
        ));

        $user1->notify(new \App\Notifications\SimpleNotification(
            'Messaggio dalla Guida',
            'Giuseppe Trek ha risposto alla tua domanda sulla difficoltà del percorso.'
        ));

        $user1->notify(new \App\Notifications\SimpleNotification(
            'Avviso Maltempo',
            'Uscita programmata per domani posticipata a causa di allerta meteo arancione.'
        ));

        $user1->notify(new \App\Notifications\SimpleNotification(
            'Stato del Sentiero',
            'Attenzione: segnalata una leggera deviazione sulla Tappa 2 vicino a Monterosso Calabro.'
        ));
    }
}
