<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Stage;

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
        // 1. Creo l'Admin
        // NOTA: 'role' è escluso da $fillable per sicurezza.
        // Uso updateQuietly per forzare il ruolo admin senza passare da mass-assignment.
        $admin = User::create([
            'name'     => 'Amministratore',
            'email'    => 'admin@coasttrack.it',
            'password' => Hash::make('password'),
        ]);
        $admin->updateQuietly(['role' => 'admin']);

        // 2. Creo gli utenti normali
        $user1 = User::create([
            'name'     => 'Mario Rossi',
            'email'    => 'mario@example.com',
            'password' => Hash::make('password'),
        ]);

        $user2 = User::create([
            'name'     => 'Luigi Verdi',
            'email'    => 'luigi@example.com',
            'password' => Hash::make('password'),
        ]);

        $user3 = User::create([
            'name'     => 'Maria Bianchi',
            'email'    => 'maria@example.com',
            'password' => Hash::make('password'),
        ]);

        $user4 = User::create([
            'name'     => 'Stefano Neri',
            'email'    => 'stefano@example.com',
            'password' => Hash::make('password'),
        ]);

        $user5 = User::create([
            'name'     => 'Awais',
            'email'    => 'awais@example.com',
            'password' => Hash::make('password'),
        ]);

        $userGiulia = User::create([
            'name'     => 'Giulia Neri',
            'email'    => 'giulia@example.com',
            'password' => Hash::make('password'),
        ]);

        // 3. Creo le tre tappe del cammino
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


        // 4. Creo le uscite ufficiali
        $outing1 = Outing::create([
            'type' => 'official',
            'is_full_trail' => false,
            'start_location' => $stage1->start_point,
            'end_location' => $stage1->end_point,

            'date' => Carbon::now()->addDays(10),
            'meeting_point' => 'Piazza di Soverato, ore 8:00',
            'max_participants' => 20,
            'difficulty' => 'facile',
            'status' => 'published'
        ]);

        // Un'uscita del passato già conclusa
        $outingPast = Outing::create([
            'type' => 'official',
            'is_full_trail' => false,
            'start_location' => $stage1->start_point,
            'end_location' => $stage1->end_point,

            'date' => Carbon::now()->subDays(5),
            'meeting_point' => 'Piazza di Soverato',
            'max_participants' => 15,
            'difficulty' => 'facile',
            'status' => 'concluded'
        ]);

        // 6. Registro le partecipazioni all'uscita conclusa (così si creano le credenziali digitali)
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

        // Uscita completa del passato per far sì che Maria abbia completato tutto il cammino
        $outingFullPast = Outing::create([
            'type' => 'official',
            'is_full_trail' => true,
            'start_location' => 'Soverato',
            'end_location' => 'Pizzo',

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
            'stage_id' => null, // Metto null così indica il cammino intero
            'outing_id' => $outingFullPast->id,
            'completed_at' => Carbon::now()->subDays(9)
        ]);

        // Uscita ufficiale completa del passato per far sì che Mario possa organizzare uscite
        // (canCreateOuting() richiede almeno un cammino completo nello storico)
        $outingFullMario = Outing::create([
            'type'             => 'official',
            'is_full_trail'    => true,
            'start_location'   => 'Soverato',
            'end_location'     => 'Pizzo',
            'date'             => Carbon::now()->subDays(20),
            'meeting_point'    => 'Piazza di Soverato, ore 7:30',
            'max_participants' => 20,
            'difficulty'       => 'difficile',
            'status'           => 'concluded',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingFullMario->id,
            'user_id'   => $user1->id,
            'status'    => 'accepted',
        ]);

        DigitalCredential::create([
            'user_id'      => $user1->id,
            'stage_id'     => null, // null = cammino intero
            'outing_id'    => $outingFullMario->id,
            'completed_at' => Carbon::now()->subDays(19),
        ]);

        // 7. Uscite tra utenti (mario può crearne una perché ha completato il cammino completo)
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
            'status' => 'published', // Facciamo finta che sia già approvata dall'admin
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

        // Uscita completa organizzata da Maria Bianchi per i test
        $outingMaria = Outing::create([
            'type' => 'user',
            'is_full_trail' => true,
            'organizer_id' => $user3->id,
            'date' => Carbon::create(2026, 7, 5),
            'meeting_point' => 'Piazza di Soverato',
            'max_participants' => 5,
            'difficulty' => 'medio',
            'status' => 'published',
            'notes' => 'TEST ANTIGRAVITY'
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingMaria->id,
            'user_id' => $user3->id,
            'status' => 'accepted'
        ]);
    }
}
