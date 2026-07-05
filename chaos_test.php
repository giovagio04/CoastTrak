<?php

use App\Models\User;
use App\Models\Outing;
use App\Models\ParticipationRequest;
use Illuminate\Support\Facades\DB;

echo "Inizio Chaos Testing...\n";
$report = [];

function recordCrash($nomeTest, $descrizione, $exception) {
    global $report;
    $report[] = [
        'test' => $nomeTest,
        'desc' => $descrizione,
        'error' => $exception->getMessage(),
        'file' => basename($exception->getFile()),
        'line' => $exception->getLine()
    ];
    echo "[CRASH] $nomeTest fallito!\n";
}

// 1. Creiamo un utente fake
$user = User::factory()->create(['name' => 'Chaos Tester']);

// TEST 1: Data Estrema in Outing
try {
    $outing = Outing::create([
        'type' => 'user',
        'is_full_trail' => false,
        'start_location' => 'A',
        'end_location' => 'B',
        'organizer_id' => $user->id,
        'date' => '99999-12-31', 
        'meeting_point' => 'Test',
        'max_participants' => 10,
        'difficulty' => 'facile',
        'status' => 'published'
    ]);
    echo "[PASS] Test 1: Data estrema accettata.\n";
} catch (\Exception $e) {
    recordCrash('Data Estrema Outing', 'Inserimento di data enorme nel DB (bypassing HTML validation)', $e);
}

// TEST 2: Limiti numerici su Max Participants (-5)
try {
    $outing2 = Outing::create([
        'type' => 'user',
        'is_full_trail' => false,
        'start_location' => 'A',
        'end_location' => 'B',
        'organizer_id' => $user->id,
        'date' => now()->addDays(5),
        'meeting_point' => 'Test',
        'max_participants' => -5,
        'difficulty' => 'facile',
        'status' => 'published'
    ]);
    echo "[PASS] Test 2: Max participants negativo accettato dal DB.\n";
} catch (\Exception $e) {
    recordCrash('Max Participants Negativo', 'Inserimento di partecipanti massimi in negativo', $e);
}

// TEST 3: Stringa gigante
try {
    $giantString = str_repeat("A", 1000000); // 1 MB
    Outing::create([
        'type' => 'user',
        'is_full_trail' => false,
        'start_location' => 'A',
        'end_location' => 'B',
        'organizer_id' => $user->id,
        'date' => now()->addDays(5),
        'meeting_point' => 'Test',
        'max_participants' => 5,
        'difficulty' => 'facile',
        'status' => 'published',
        'notes' => $giantString
    ]);
    echo "[PASS] Test 3: Stringa gigante salvata.\n";
} catch (\Exception $e) {
    recordCrash('Stringa Gigante Notes', 'Inserimento 1MB stringa nel DB nel campo notes', $e);
}

// TEST 4: Orphan Delete
try {
    $user->delete(); 
    $check = Outing::find($outing2->id);
    if ($check && $check->organizer === null) {
         throw new \Exception("Orphan record detectato: outing senza organizer_id in CASCADE (provoca null pointer in blade su \$outing->organizer->name)");
    }
    echo "[PASS] Test 4: Cascade funzionante.\n";
} catch (\Exception $e) {
    recordCrash('Orphan User Deletion', 'L\'eliminazione di un utente lascia le sue uscite orfane.', $e);
}

// TEST 5: Partecipazione a un'uscita cancellata e/o orfana
try {
    $user2 = User::factory()->create();
    $outing3 = Outing::create([
        'type' => 'user',
        'is_full_trail' => false,
        'organizer_id' => $user2->id,
        'date' => now()->addDays(5),
        'meeting_point' => 'Test',
        'max_participants' => 10,
        'difficulty' => 'facile',
        'status' => 'cancelled' // Stato cancellato
    ]);
    
    // Forziamo un'iscrizione a livello DB, poi carichiamo la relazione
    $req = ParticipationRequest::create([
        'user_id' => $user2->id,
        'outing_id' => $outing3->id,
        'status' => 'accepted'
    ]);
    
    if($req->outing->status === 'cancelled') {
        echo "[PASS] Test 5: DB accetta iscrizioni su uscite cancellate (se bypassiamo il controller).\n";
    }
} catch (\Exception $e) {
    recordCrash('Iscrizione uscita cancellata', 'Test logica backend su uscite cancellate', $e);
}

echo "\n--- CRASH REPORT ---\n";
echo json_encode($report, JSON_PRETTY_PRINT);
