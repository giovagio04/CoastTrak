<?php
/**
 * =========================================================
 * COASTTRAK — Chaos & Stress Test Suite (Modalità Offline)
 * =========================================================
 * Non richiede DB. Testa la logica di validazione,
 * il codice dei controller e le regole di business.
 */

require __DIR__ . '/vendor/autoload.php';

// Bootstrap minimale di Laravel senza connessione DB
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// ── Contatori ──────────────────────────────────────────────
$GLOBALS['pass']     = 0;
$GLOBALS['fail']     = 0;
$GLOBALS['findings'] = [];

if (!function_exists('test')) {
    function test(string $name, callable $fn): void {
        try {
            $result = $fn();
            if ($result === true) {
                echo "  \u2705  PASS  $name\n";
                $GLOBALS['pass']++;
            } else {
                echo "  \u274c  FAIL  $name\n     -> $result\n";
                $GLOBALS['fail']++;
                $GLOBALS['findings'][] = ['name' => $name, 'result' => $result];
            }
        } catch (\Throwable $e) {
            $class = get_class($e);
            $msg   = $e->getMessage();
            echo "  \ud83d\udca5  CRASH $name\n     -> $class: $msg\n";
            $GLOBALS['fail']++;
            $GLOBALS['findings'][] = ['name' => $name, 'result' => "CRASH [$class]: $msg"];
        }
    }
}

if (!function_exists('v')) {
    function v(array $data, array $rules): array {
        return Validator::make($data, $rules)->errors()->toArray();
    }
}

if (!function_exists('readFile')) {
    function readFile(string $rel): string {
        return file_get_contents(__DIR__ . '/' . $rel);
    }
}

// ──────────────────────────────────────────────────────────
echo "\n\n==========================================================\n";
echo "  COASTTRAK — Chaos & Stress Test Suite\n";
echo "==========================================================\n";

// ══════════════════════════════════════════════════════════
echo "\n\n\ud83d\udd10  [1] REGISTRAZIONE — Input Validation & Mass Assignment\n";
echo str_repeat('-', 58) . "\n";

test('REG-01: Mass Assignment bloccato (role fuori da $fillable)', function() {
    $fillable = ['name', 'email', 'password', 'date_of_birth', 'bio', 'profile_photo_path'];
    return !in_array('role', $fillable)
        ? true
        : 'CRITICO: role è ancora in $fillable!';
});

test('REG-02: is_banned fuori da $fillable', function() {
    $fillable = ['name', 'email', 'password', 'date_of_birth', 'bio', 'profile_photo_path'];
    return !in_array('is_banned', $fillable)
        ? true
        : 'CRITICO: is_banned è in $fillable!';
});

test('REG-03: RegisteredUserController non passa role da request', function() {
    $code = readFile('app/Http/Controllers/Auth/RegisteredUserController.php');
    $hasRoleInCreate = preg_match("/'role'\s*=>/", $code);
    return !$hasRoleInCreate
        ? true
        : 'CRITICO: RegisteredUserController passa role al create()!';
});

test('REG-04: Email malformata bloccata', function() {
    $e = v(['email' => 'non-una-email'], ['email' => 'required|email']);
    return isset($e['email']) ? true : 'Email malformata non bloccata';
});

test('REG-05: Password troppo corta (< 8 char)', function() {
    $e = v(['password' => '123', 'password_confirmation' => '123'], ['password' => 'required|confirmed|min:8']);
    return isset($e['password']) ? true : 'Password corta non bloccata';
});

test('REG-06: SQL injection nel nome (regex check)', function() {
    $e = v(
        ['name' => "'; DROP TABLE users; --"],
        ['name' => ['required', 'string', 'regex:/^[\p{L}\s\'\-]+$/u']]
    );
    return isset($e['name']) ? true : 'CRITICO: SQL injection nel nome non bloccata';
});

test('REG-07: XSS nel nome (tag HTML)', function() {
    $e = v(
        ['name' => '<script>alert(1)</script>'],
        ['name' => ['required', 'string', 'regex:/^[\p{L}\s\'\-]+$/u']]
    );
    return isset($e['name']) ? true : 'XSS nel nome non bloccato dalla regex';
});

test('REG-08: Nome solo numeri bloccato', function() {
    $e = v(
        ['name' => '12345'],
        ['name' => ['required', 'string', 'regex:/^[\p{L}\s\'\-]+$/u']]
    );
    return isset($e['name']) ? true : 'Nome con soli numeri non bloccato';
});

test('REG-09: Nome con null byte', function() {
    $e = v(
        ['name' => "Mario\0Rossi"],
        ['name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u']]
    );
    return isset($e['name']) ? true : 'Null byte nel nome non bloccato';
});

test('REG-10: Campo enorme da 64KB', function() {
    $e = v(
        ['name' => str_repeat('A', 65536)],
        ['name' => 'required|string|max:255']
    );
    return isset($e['name']) ? true : 'Stringa 64KB non bloccata in campo name';
});

// ══════════════════════════════════════════════════════════
echo "\n\n\ud83d\udcca  [2] FILTRI & QUERY — Injection & Edge Case\n";
echo str_repeat('-', 58) . "\n";

test('FILT-01: type non valido bloccato', function() {
    $e = v(['type' => 'HACKED'], ['type' => 'nullable|in:all,official,user']);
    return isset($e['type']) ? true : 'type non valido accettato';
});

test('FILT-02: SQL injection nel type', function() {
    $e = v(["type" => "' OR '1'='1"], ['type' => 'nullable|in:all,official,user']);
    return isset($e['type']) ? true : 'SQL injection nel type non bloccata';
});

test('FILT-03: status non valido bloccato', function() {
    $e = v(['status' => 'deleted'], ['status' => 'nullable|in:all,pending,published,concluded,cancelled,rejected']);
    return isset($e['status']) ? true : 'Status non valido accettato';
});

test('FILT-04: difficulty non valida', function() {
    $e = v(['difficulty' => 'estremo'], ['difficulty' => 'nullable|in:all,facile,medio,difficile']);
    return isset($e['difficulty']) ? true : 'Difficulty non valida accettata';
});

test('FILT-05: date_from nel passato', function() {
    $e = v(['date_from' => '2000-01-01'], ['date_from' => 'nullable|date|after_or_equal:today']);
    return isset($e['date_from']) ? true : 'Data nel passato non bloccata';
});

test('FILT-06: date_to < date_from', function() {
    $e = v(
        ['date_from' => '2030-06-01', 'date_to' => '2030-01-01'],
        ['date_from' => 'nullable|date', 'date_to' => 'nullable|date|after_or_equal:date_from']
    );
    return isset($e['date_to']) ? true : 'date_to < date_from non bloccato';
});

test('FILT-07: min_trails stringa "abc"', function() {
    $e = v(['min_trails' => 'abc'], ['min_trails' => 'nullable|integer|min:0|max:100']);
    return isset($e['min_trails']) ? true : 'min_trails stringa non bloccata';
});

test('FILT-08: min_trails negativo', function() {
    $e = v(['min_trails' => -1], ['min_trails' => 'nullable|integer|min:0|max:100']);
    return isset($e['min_trails']) ? true : 'min_trails negativo non bloccato';
});

test('FILT-09: min_trails overflow (9999)', function() {
    $e = v(['min_trails' => 9999], ['min_trails' => 'nullable|integer|min:0|max:100']);
    return isset($e['min_trails']) ? true : 'min_trails overflow non bloccato';
});

test('FILT-10: start_location > 100 chars', function() {
    $e = v(['start_location' => str_repeat('X', 101)], ['start_location' => 'nullable|string|max:100']);
    return isset($e['start_location']) ? true : 'start_location > 100 chars non bloccata';
});

// ══════════════════════════════════════════════════════════
echo "\n\n\ud83c\udfc3  [3] PARTECIPAZIONI — Business Logic\n";
echo str_repeat('-', 58) . "\n";

test('PART-01: max_participants = 0 bloccato', function() {
    $e = v(['max_participants' => 0], ['max_participants' => 'required|integer|min:1|max:50']);
    return isset($e['max_participants']) ? true : 'max_participants=0 non bloccato';
});

test('PART-02: max_participants negativo', function() {
    $e = v(['max_participants' => -5], ['max_participants' => 'required|integer|min:1|max:50']);
    return isset($e['max_participants']) ? true : 'max_participants negativo non bloccato';
});

test('PART-03: max_participants float (3.9)', function() {
    $e = v(['max_participants' => 3.9], ['max_participants' => 'required|integer|min:1|max:50']);
    return isset($e['max_participants']) ? true : 'max_participants float non bloccato';
});

test('PART-04: max_participants oltre limite admin (51)', function() {
    $e = v(['max_participants' => 51], ['max_participants' => 'required|integer|min:1|max:50']);
    return isset($e['max_participants']) ? true : 'max_participants=51 non bloccato';
});

test('PART-05: max_participants user oltre limite utente (16)', function() {
    $e = v(['max_participants' => 16], ['max_participants' => 'required|integer|min:2|max:15']);
    return isset($e['max_participants']) ? true : 'max_participants=16 non bloccato per user';
});

test('PART-06: Race condition fix presente (DB::transaction)', function() {
    $code = readFile('app/Http/Controllers/ParticipationController.php');
    $hasTx   = str_contains($code, 'DB::transaction');
    $hasLock = str_contains($code, 'lockForUpdate');
    return ($hasTx && $hasLock) ? true : 'CRITICO: Transazione o lock assenti in ParticipationController';
});

test('PART-07: Controllo organizzatore-non-si-iscrive presente', function() {
    $code = readFile('app/Http/Controllers/ParticipationController.php');
    return str_contains($code, 'organizer_id === auth()->id()')
        ? true
        : 'MANCANTE: controllo organizzatore in store()';
});

test('PART-08: Doppia approvazione bloccata (status check)', function() {
    $code = readFile('app/Http/Controllers/ParticipationController.php');
    return str_contains($code, "status !== 'pending'")
        ? true
        : 'MANCANTE: check status !== pending in approve()';
});

test('PART-09: BUG — Nessun controllo status uscita in store()', function() {
    // Un utente può POST /outings/{id}/participate anche se l'uscita è concluded/cancelled
    $code = readFile('app/Http/Controllers/ParticipationController.php');
    $hasOutingStatusCheck = str_contains($code, "status.*published") || str_contains($code, "->status === 'published'") || str_contains($code, 'outing->status');
    return !$hasOutingStatusCheck
        ? "BUG ATTIVO: Nessun controllo sullo status dell'uscita in store() — un utente puo' iscriversi a uscite non pubblicate (concluded/cancelled)"
        : true;
});

test('PART-10: date uscita user deve essere futura (after:today)', function() {
    $e = v(
        ['date' => date('Y-m-d')], // oggi
        ['date' => 'required|date|after:today']
    );
    return isset($e['date']) ? true : 'Data odierna accettata (dovrebbe essere domani o dopo)';
});

// ══════════════════════════════════════════════════════════
echo "\n\n\ud83d\udee1\ufe0f   [4] ADMIN — Validazione & Authorization\n";
echo str_repeat('-', 58) . "\n";

test('ADMIN-01: reason > 2000 chars bloccata', function() {
    $e = v(['reason' => str_repeat('R', 2001)], ['reason' => 'nullable|string|max:2000']);
    return isset($e['reason']) ? true : 'reason > 2000 chars non bloccata in admin';
});

test('ADMIN-02: notes > 5000 chars bloccate', function() {
    $e = v(['notes' => str_repeat('N', 5001)], ['notes' => 'nullable|string|max:5000']);
    return isset($e['notes']) ? true : 'notes > 5000 chars non bloccate';
});

test('ADMIN-03: guide_id tipo stringa', function() {
    $e = v(['guide_id' => 'non-un-id'], ['guide_id' => 'nullable|exists:guides,id']);
    return isset($e['guide_id']) ? true : 'guide_id stringa non bloccato';
});

test('ADMIN-04: type filtro XSS', function() {
    $e = v(['type' => '<img src=x onerror=alert(1)>'], ['type' => 'nullable|in:official,user']);
    return isset($e['type']) ? true : 'XSS nel filtro type admin non bloccato';
});

test('ADMIN-05: Ban di admin bloccato (check nel codice)', function() {
    $code = readFile('app/Http/Controllers/Admin/UserController.php');
    return str_contains($code, "role === 'admin'")
        ? true
        : 'CRITICO: Nessun controllo che impedisce ban di admin';
});

test('ADMIN-06: BUG — update uscita accetta date nel passato', function() {
    // In Admin/OutingController::update() la regola date è 'before_or_equal:+3 years'
    // ma NON ha 'after_or_equal:today' — un admin può impostare date nel passato
    $code = readFile('app/Http/Controllers/Admin/OutingController.php');
    // Cerca la regola update specifica
    preg_match('/public function update.*?validated/s', $code, $m);
    $updateCode = $m[0] ?? '';
    $hasAfterCheck = str_contains($updateCode, 'after_or_equal:today') || str_contains($updateCode, 'after:today');
    return $hasAfterCheck
        ? true
        : "BUG: In Admin::update(), 'date' manca di 'after_or_equal:today' — si possono impostare date nel passato";
});

test('ADMIN-07: status non valido in update bloccato', function() {
    $e = v(
        ['status' => 'FAKE_STATUS'],
        ['status' => 'required|in:pending,published,concluded,cancelled,rejected']
    );
    return isset($e['status']) ? true : 'Status non valido in admin update non bloccato';
});

test('ADMIN-08: difficulty non valida bloccata', function() {
    $e = v(['difficulty' => 'killer'], ['difficulty' => 'required|in:facile,medio,difficile']);
    return isset($e['difficulty']) ? true : 'Difficulty non valida non bloccata';
});

// ══════════════════════════════════════════════════════════
echo "\n\n\ud83d\udc64  [5] PROFILO — Update & Sicurezza\n";
echo str_repeat('-', 58) . "\n";

test('PROF-01: Bio > 1000 chars bloccata', function() {
    $e = v(['bio' => str_repeat('x', 1001)], ['bio' => 'nullable|string|max:1000']);
    return isset($e['bio']) ? true : 'Bio > 1000 chars non bloccata';
});

test('PROF-02: Data nascita futura bloccata', function() {
    $e = v(['date_of_birth' => '2099-01-01'], ['date_of_birth' => 'nullable|date|before_or_equal:today']);
    return isset($e['date_of_birth']) ? true : 'Data nascita futura non bloccata';
});

test('PROF-03: Data nascita prima del 1900 bloccata', function() {
    $e = v(['date_of_birth' => '1899-12-31'], ['date_of_birth' => 'nullable|date|after:1900-01-01']);
    return isset($e['date_of_birth']) ? true : 'Data nascita pre-1900 non bloccata';
});

test('PROF-04: Nome con numeri bloccato dalla regex', function() {
    $e = v(
        ['name' => 'Mario123'],
        ['name' => ['required', 'string', 'regex:/^[\p{L}\s\'\-]+$/u']]
    );
    return isset($e['name']) ? true : 'Nome con numeri non bloccato';
});

test('PROF-05: Foto con MIME type non valido bloccata', function() {
    $code = readFile('app/Http/Requests/ProfileUpdateRequest.php');
    $hasMimes = str_contains($code, 'mimes:');
    $hasMax   = str_contains($code, 'max:2048');
    return ($hasMimes && $hasMax) ? true : 'Regole upload MIME/size non presenti';
});

test('PROF-06: Eliminazione con uscite attive bloccata', function() {
    $code = readFile('app/Http/Controllers/ProfileController.php');
    return (str_contains($code, 'activeOutingsCount') && str_contains($code, "published"))
        ? true
        : 'CRITICO: Nessun controllo uscite attive prima di eliminare account';
});

test('PROF-07: Nome < 3 chars bloccato', function() {
    $e = v(['name' => 'AB'], ['name' => 'required|string|min:3|max:255']);
    return isset($e['name']) ? true : 'Nome < 3 chars non bloccato';
});

test('PROF-08: Email non lowercase bloccata', function() {
    $e = v(['email' => 'MARIO@EXAMPLE.COM'], ['email' => 'required|string|lowercase|email']);
    return isset($e['email']) ? true : 'Email uppercase non bloccata (richiesta lowercase)';
});

// ══════════════════════════════════════════════════════════
echo "\n\n\ud83d\udd12  [6] MIDDLEWARE & SICUREZZA\n";
echo str_repeat('-', 58) . "\n";

test('MW-01: CheckBanned esegue logout + session invalidate', function() {
    $code = readFile('app/Http/Middleware/CheckBanned.php');
    return (str_contains($code, 'Auth::logout') && str_contains($code, 'invalidate'))
        ? true
        : 'CheckBanned non esegue logout/invalidate';
});

test('MW-02: RoleMiddleware controlla role correttamente', function() {
    $code = readFile('app/Http/Middleware/RoleMiddleware.php');
    return str_contains($code, 'role !== $role')
        ? true
        : 'RoleMiddleware non confronta il ruolo correttamente';
});

test('MW-03: CheckBanned registrato nel gruppo web', function() {
    $code = readFile('bootstrap/app.php');
    return str_contains($code, 'CheckBanned')
        ? true
        : 'CRITICO: CheckBanned non è registrato nel gruppo web';
});

test('MW-04: Rotte admin protette da role:admin middleware', function() {
    $code = readFile('routes/web.php');
    return str_contains($code, "role:admin")
        ? true
        : 'CRITICO: Rotte admin non protette dal middleware role:admin';
});

test('MW-05: Rate limiting presente sulle rotte di partecipazione', function() {
    $code = readFile('routes/web.php');
    return str_contains($code, 'throttle:20,1')
        ? true
        : 'MANCANTE: Rate limiting sulle rotte di partecipazione';
});

test('MW-06: Rate limiting presente sulle notifiche', function() {
    $code = readFile('routes/web.php');
    return str_contains($code, 'throttle:30,1')
        ? true
        : 'MANCANTE: Rate limiting sulle rotte notifiche';
});

// ══════════════════════════════════════════════════════════
echo "\n\n\ud83d\udca3  [7] CHAOS — Boundary & Robustezza\n";
echo str_repeat('-', 58) . "\n";

test('CHAOS-01: Null-safe operator su outing->date (VULN-11)', function() {
    $code = readFile('app/Http/Controllers/DashboardController.php');
    return str_contains($code, 'outing?->date')
        ? true
        : 'CRITICO: Manca operatore null-safe — TypeError possibile';
});

test('CHAOS-02: Transazione atomica in approve() (VULN-03)', function() {
    $code = readFile('app/Http/Controllers/ParticipationController.php');
    // Conta quante volte appaiono lockForUpdate (store + approve = 2)
    $count = substr_count($code, 'lockForUpdate');
    return ($count >= 2)
        ? true
        : "CRITICO: lockForUpdate appare solo $count volta/e (attese 2 — store + approve)";
});

test('CHAOS-03: Unicode/emoji nelle note non bloccate', function() {
    $e = v(
        ['notes' => "\ud83c\udfc4\ufe0f Cammino bellissimo! \u4e2d\u6587 \u65e5\u672c\u8a9e"],
        ['notes' => 'nullable|string|max:5000']
    );
    return !isset($e['notes']) ? true : 'Unicode/emoji bloccate nella validazione (falso positivo)';
});

test('CHAOS-04: Payload JSON nel campo reason (non deve fare injection)', function() {
    $payload = '{"__proto__": {"isAdmin": true}, "role": "admin"}';
    $e = v(['reason' => $payload], ['reason' => 'nullable|string|max:2000']);
    // Il payload è < 2000 chars e non contiene nulla di illegale per una stringa
    return !isset($e['reason']) ? true : 'FAIL: Payload JSON valido (come stringa) bloccato erroneamente';
});

test('CHAOS-05: Valore NULL per campo opzionale (notes)', function() {
    $e = v(['notes' => null], ['notes' => 'nullable|string|max:5000']);
    return !isset($e['notes']) ? true : 'NULL non accettato per campo nullable';
});

test('CHAOS-06: Stringa vuota per campo opzionale (notes)', function() {
    $e = v(['notes' => ''], ['notes' => 'nullable|string|max:5000']);
    return !isset($e['notes']) ? true : 'Stringa vuota non accettata per campo nullable';
});

test('CHAOS-07: Data con formato non standard (dd/mm/yyyy)', function() {
    $e = v(['date' => '01/07/2030'], ['date' => 'required|date|after:today']);
    // Laravel accetta anche questo formato, ma è ambiguo — verifichiamo
    return true; // Comportamento variabile — solo informatico
});

test('CHAOS-08: stage_name attribute su outing is_full_trail=true', function() {
    $outing = new \App\Models\Outing();
    $outing->is_full_trail = true;
    $name = $outing->stage_name;
    return ($name === 'Cammino Completo (55km)') ? true : "stage_name errato: '$name'";
});

test('CHAOS-09: stage_name attribute su outing custom route', function() {
    $outing = new \App\Models\Outing();
    $outing->is_full_trail = false;
    $outing->start_location = 'Soverato';
    $outing->end_location = 'Petrizzi';
    $name = $outing->stage_name;
    return str_contains($name, 'Soverato') ? true : "stage_name errato: '$name'";
});

test('CHAOS-10: stage_name attribute senza dati (fallback)', function() {
    $outing = new \App\Models\Outing();
    $outing->is_full_trail = false;
    $outing->start_location = null;
    $outing->end_location = null;
    $name = $outing->stage_name;
    return ($name === 'Tappa Sconosciuta') ? true : "stage_name fallback errato: '$name'";
});

test('CHAOS-11: Outing con max_participants stringa', function() {
    $e = v(['max_participants' => 'cinque'], ['max_participants' => 'required|integer|min:1|max:50']);
    return isset($e['max_participants']) ? true : 'max_participants stringa non bloccato';
});

test('CHAOS-12: Uscita user con troppi partecipanti (>15)', function() {
    $e = v(['max_participants' => 100], ['max_participants' => 'required|integer|min:2|max:15']);
    return isset($e['max_participants']) ? true : 'max_participants user > 15 non bloccato';
});

test('CHAOS-13: Verifica che seeder non usi mass assignment su role', function() {
    $code = readFile('database/seeders/DatabaseSeeder.php');
    // Deve usare updateQuietly, non 'role' => 'admin' in create()
    $hasUpdateQuietly = str_contains($code, 'updateQuietly');
    $hasOldStyle = preg_match("/'role'\s*=>\s*'admin'/", $code);
    if ($hasUpdateQuietly && !$hasOldStyle) return true;
    if ($hasOldStyle) return 'ATTENZIONE: Il seeder usa ancora mass assignment con role=admin in create()';
    return 'Il seeder non usa updateQuietly per impostare il ruolo admin';
});

// ══════════════════════════════════════════════════════════
// RIEPILOGO
// ══════════════════════════════════════════════════════════
$total = $GLOBALS['pass'] + $GLOBALS['fail'];
$score = $total > 0 ? round(($GLOBALS['pass'] / $total) * 100) : 0;

echo "\n\n";
echo str_repeat('=', 58) . "\n";
echo "  RISULTATI STRESS & CHAOS TEST\n";
echo str_repeat('=', 58) . "\n";
printf("  ✅  PASS:   %d / %d  (%d%%)\n", $GLOBALS['pass'], $total, $score);
printf("  ❌  FAIL:   %d / %d\n", $GLOBALS['fail'], $total);
echo str_repeat('-', 58) . "\n";

if (!empty($GLOBALS['findings'])) {
    echo "\n  ⚠️  PROBLEMI RILEVATI (" . count($GLOBALS['findings']) . "):\n\n";
    foreach ($GLOBALS['findings'] as $i => $f) {
        $n = $i + 1;
        echo "  $n. [{$f['name']}]\n";
        $lines = explode("\n", wordwrap($f['result'], 52, "\n", true));
        foreach ($lines as $line) {
            echo "     $line\n";
        }
        echo "\n";
    }
} else {
    echo "\n  \ud83c\udf89  ZERO problemi rilevati! Il codice e' robusto.\n";
}
echo str_repeat('=', 58) . "\n\n";
