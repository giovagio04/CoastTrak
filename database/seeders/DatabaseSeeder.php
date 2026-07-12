<?php

namespace Database\Seeders;

use App\Models\User;
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
        $admin = User::create([
            'name'     => 'Amministratore',
            'email'    => 'admin@coasttrack.it',
            'password' => Hash::make('password'),
            'date_of_birth' => '1980-01-01',
        ]);
        $admin->updateQuietly(['role' => 'admin']);

        $mario = User::create([
            'name'     => 'Mario Rossi',
            'email'    => 'mario@example.com',
            'password' => Hash::make('password'),
            'date_of_birth' => '1985-05-15',
        ]);

        $luigi = User::create([
            'name'     => 'Luigi Verdi',
            'email'    => 'luigi@example.com',
            'password' => Hash::make('password'),
            'date_of_birth' => '1990-10-20',
        ]);

        $maria = User::create([
            'name'     => 'Maria Bianchi',
            'email'    => 'maria@example.com',
            'password' => Hash::make('password'),
            'date_of_birth' => '1992-02-14',
            'profile_photo_path' => 'profile_photos/maria.png',
        ]);

        $giovanni = User::create([
            'name'     => 'Giovanni',
            'email'    => 'giovanni@example.com',
            'password' => Hash::make('password'),
            'date_of_birth' => '1995-07-22',
        ]);

        $francesco = User::create([
            'name'     => 'Francesco',
            'email'    => 'francesco@example.com',
            'password' => Hash::make('password'),
            'date_of_birth' => '1998-11-05',
        ]);

        $outingMariaPast = Outing::create([
            'type'             => 'official',
            'is_full_trail'    => true,
            'start_location'   => 'Soverato',
            'end_location'     => 'Pizzo',
            'date'             => Carbon::now()->subDays(30),
            'meeting_point'    => 'Piazza di Soverato',
            'max_participants' => 20,
            'difficulty'       => 'difficile',
            'status'           => 'concluded',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingMariaPast->id,
            'user_id'   => $maria->id,
            'status'    => 'accepted',
        ]);

        DigitalCredential::create([
            'user_id'      => $maria->id,
            'outing_id'    => $outingMariaPast->id,
            'completed_at' => Carbon::now()->subDays(29),
        ]);

        $outingMarioPast = Outing::create([
            'type'             => 'official',
            'is_full_trail'    => true,
            'start_location'   => 'Pizzo',
            'end_location'     => 'Soverato',
            'date'             => Carbon::now()->subDays(40),
            'meeting_point'    => 'Piazza di Pizzo',
            'max_participants' => 20,
            'difficulty'       => 'difficile',
            'status'           => 'concluded',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingMarioPast->id,
            'user_id'   => $mario->id,
            'status'    => 'accepted',
        ]);

        DigitalCredential::create([
            'user_id'      => $mario->id,
            'outing_id'    => $outingMarioPast->id,
            'completed_at' => Carbon::now()->subDays(39),
        ]);

        $outingOfficial = Outing::create([
            'type'             => 'official',
            'is_full_trail'    => false,
            'start_location'   => 'Soverato',
            'end_location'     => 'Petrizzi',
            'date'             => Carbon::now()->addDays(10),
            'meeting_point'    => 'Piazza di Soverato, ore 8:00',
            'max_participants' => 20,
            'difficulty'       => 'facile',
            'status'           => 'published',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingOfficial->id,
            'user_id'   => $giovanni->id,
            'status'    => 'pending',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingOfficial->id,
            'user_id'   => $luigi->id,
            'status'    => 'accepted',
        ]);

        $outingByMaria = Outing::create([
            'type'             => 'user',
            'is_full_trail'    => true,
            'start_location'   => 'Pizzo',
            'end_location'     => 'Soverato',
            'organizer_id'     => $maria->id,
            'date'             => Carbon::now()->addDays(15),
            'meeting_point'    => 'Piazza di Pizzo, ore 7:00',
            'max_participants' => 10,
            'difficulty'       => 'difficile',
            'status'           => 'published',
            'notes'            => 'Portare scarpe comode!',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingByMaria->id,
            'user_id'   => $maria->id,
            'status'    => 'accepted',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingByMaria->id,
            'user_id'   => $giovanni->id,
            'status'    => 'pending',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingByMaria->id,
            'user_id'   => $luigi->id,
            'status'    => 'accepted',
        ]);

        $outingByMario = Outing::create([
            'type'             => 'user',
            'is_full_trail'    => false,
            'start_location'   => 'Petrizzi',
            'end_location'     => 'Monterosso Calabro',
            'organizer_id'     => $mario->id,
            'date'             => Carbon::now()->addDays(20),
            'meeting_point'    => 'Piazza di Petrizzi',
            'max_participants' => 5,
            'difficulty'       => 'medio',
            'status'           => 'published',
            'notes'            => 'Percorso intermedio, acqua disponibile lungo la strada.',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingByMario->id,
            'user_id'   => $mario->id,
            'status'    => 'accepted',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingByMario->id,
            'user_id'   => $francesco->id,
            'status'    => 'pending',
        ]);

        ParticipationRequest::create([
            'outing_id' => $outingByMario->id,
            'user_id'   => $luigi->id,
            'status'    => 'accepted',
        ]);
    }
}
