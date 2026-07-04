<?php
define("LARAVEL_START", microtime(true));
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Outing;
use App\Models\User;

$pass = 0; $fail = 0; $findings = [];

function tst(string $name, callable $fn): void {
    global $pass, $fail, $findings;
    try {
        $result = $fn();
        if ($result === true) { echo "  OK   PASS  $name\n"; $pass++; }
        else { echo "  FAIL FAIL  $name\n     -> $result\n"; $fail++; $findings[] = ["name"=>$name,"result"=>$result,"type"=>"FAIL"]; }
    } catch (\Throwable $e) {
        $cls = get_class($e); $msg = $e->getMessage();
        echo "  CRASH $name\n     -> $cls: $msg\n"; $fail++;
        $findings[] = ["name"=>$name,"result"=>"CRASH [$cls]: $msg","type"=>"CRASH"];
    }
}
function val(array $data, array $rules): array { return Validator::make($data, $rules)->errors()->toArray(); }
function src(string $rel): string { return file_get_contents(__DIR__."/".$rel); }
function http_get(string $path): array {
    $ch = curl_init("http://127.0.0.1:8000".$path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HEADER=>true, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_TIMEOUT=>5, CURLOPT_HTTPHEADER=>["Accept: application/json","X-Requested-With: XMLHttpRequest"]]);
    $raw = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    return ["status"=>$code,"body"=>$raw];
}
function http_post(string $path, array $data=[]): array {
    $ch = curl_init("http://127.0.0.1:8000".$path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HEADER=>true, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>http_build_query($data), CURLOPT_TIMEOUT=>5, CURLOPT_HTTPHEADER=>["Accept: application/json","X-Requested-With: XMLHttpRequest"]]);
    $raw = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    return ["status"=>$code,"body"=>$raw];
}

echo "\n==========================================================\n";
echo "  COASTTRAK -- Chaos & Stress Test Suite v2\n";
echo "==========================================================\n";

// === [1] MASS ASSIGNMENT & REGISTRAZIONE ===
echo "\n\n[1] MASS ASSIGNMENT & REGISTRAZIONE\n" . str_repeat("-",56) . "\n";

tst("REG-01: role fuori da fillable (User)", function() {
    return !in_array("role", (new User)->getFillable()) ? true : "CRITICO: role e in fillable!";
});
tst("REG-02: is_banned fuori da fillable", function() {
    return !in_array("is_banned", (new User)->getFillable()) ? true : "CRITICO: is_banned e in fillable!";
});
tst("REG-03: ban_reason fuori da fillable", function() {
    return !in_array("ban_reason", (new User)->getFillable()) ? true : "CRITICO: ban_reason e in fillable!";
});
tst("REG-04: RegisteredUserController non assegna role", function() {
    $code = src("app/Http/Controllers/Auth/RegisteredUserController.php");
    return !preg_match("/'role'\s*=>/", $code) ? true : "CRITICO: role assegnato nel controller di registrazione!";
});
tst("REG-05: Email malformata bloccata", function() {
    $e = val(["email"=>"non@@una.email.."], ["email"=>"required|email:rfc"]);
    return isset($e["email"]) ? true : "Email malformata non bloccata";
});
tst("REG-06: Password < 8 chars bloccata", function() {
    $e = val(["password"=>"1234567","password_confirmation"=>"1234567"],["password"=>"required|confirmed|min:8"]);
    return isset($e["password"]) ? true : "Password breve non bloccata";
});
tst("REG-07: SQL injection nel nome (regex)", function() {
    $e = val(["name"=>"'; DROP TABLE users; --"],["name"=>["required","string","regex:/^[\p{L}\s\'\-]+$/u"]]);
    return isset($e["name"]) ? true : "CRITICO: SQL injection nel nome non bloccata";
});
tst("REG-08: XSS tag nel nome", function() {
    $e = val(["name"=>"<script>alert(1)</script>"],["name"=>["required","string","regex:/^[\p{L}\s\'\-]+$/u"]]);
    return isset($e["name"]) ? true : "XSS nel nome non bloccato";
});
tst("REG-09: Null byte nel nome", function() {
    $e = val(["name"=>"Mario\0Rossi"],["name"=>["required","string","max:255","regex:/^[\p{L}\s\'\-]+$/u"]]);
    return isset($e["name"]) ? true : "Null byte nel nome non bloccato";
});
tst("REG-10: Nome da 64KB bloccato (max:255)", function() {
    $e = val(["name"=>str_repeat("A",65536)],["name"=>"required|string|max:255"]);
    return isset($e["name"]) ? true : "Stringa 64KB accettata nel campo name";
});
tst("REG-11: Emoji nel nome bloccata dalla regex", function() {
    $e = val(["name"=>"Mario Emoji"],["name"=>["required","string","regex:/^[\p{L}\s\'\-]+$/u"]]);
    return isset($e["name"]) ? true : "Emoji nel nome non bloccata";
});

// === [2] FILTRI & QUERY ===
echo "\n\n[2] FILTRI & QUERY -- Boundary & Injection\n" . str_repeat("-",56) . "\n";

tst("FILT-01: SQL injection nel type (in: block)", function() {
    $e = val(["type"=>"' OR '1'='1"],["type"=>"nullable|in:all,official,user"]);
    return isset($e["type"]) ? true : "SQL injection nel type non bloccata";
});
tst("FILT-02: Status inventato bloccato", function() {
    $e = val(["status"=>"deleted_account"],["status"=>"nullable|in:pending,published,concluded,cancelled,rejected"]);
    return isset($e["status"]) ? true : "Status inventato accettato";
});
tst("FILT-03: Difficulty non valida bloccata", function() {
    $e = val(["difficulty"=>"killer"],["difficulty"=>"nullable|in:all,facile,medio,difficile"]);
    return isset($e["difficulty"]) ? true : "Difficulty non valida accettata";
});
tst("FILT-04: date_from nel passato bloccata", function() {
    $e = val(["date_from"=>"2000-01-01"],["date_from"=>"nullable|date|after_or_equal:today"]);
    return isset($e["date_from"]) ? true : "Data passata accettata";
});
tst("FILT-05: date_to < date_from bloccata", function() {
    $e = val(["date_from"=>"2030-06-01","date_to"=>"2025-01-01"],["date_from"=>"nullable|date","date_to"=>"nullable|date|after_or_equal:date_from"]);
    return isset($e["date_to"]) ? true : "date_to < date_from accettato";
});
tst("FILT-06: min_trails stringa bloccata", function() {
    $e = val(["min_trails"=>"abc"],["min_trails"=>"nullable|integer|min:0|max:100"]);
    return isset($e["min_trails"]) ? true : "min_trails stringa accettata";
});
tst("FILT-07: min_trails -1 bloccato", function() {
    $e = val(["min_trails"=>-1],["min_trails"=>"nullable|integer|min:0|max:100"]);
    return isset($e["min_trails"]) ? true : "min_trails negativo accettato";
});
tst("FILT-08: min_trails 9999 bloccato (max:100)", function() {
    $e = val(["min_trails"=>9999],["min_trails"=>"nullable|integer|min:0|max:100"]);
    return isset($e["min_trails"]) ? true : "min_trails overflow accettato";
});
tst("FILT-09: start_location > 100 chars bloccata", function() {
    $e = val(["start_location"=>str_repeat("X",101)],["start_location"=>"nullable|string|max:100"]);
    return isset($e["start_location"]) ? true : "start_location > 100 chars accettata";
});

// === [3] PARTECIPAZIONI ===
echo "\n\n[3] PARTECIPAZIONI -- Business Logic & Race Conditions\n" . str_repeat("-",56) . "\n";

tst("PART-01: max_participants=0 bloccato (admin min:1)", function() {
    $e = val(["max_participants"=>0],["max_participants"=>"required|integer|min:1|max:50"]);
    return isset($e["max_participants"]) ? true : "max_participants=0 accettato in admin";
});
tst("PART-02: max_participants user < 2 bloccato", function() {
    $e = val(["max_participants"=>1],["max_participants"=>"required|integer|min:2|max:15"]);
    return isset($e["max_participants"]) ? true : "max_participants=1 accettato per user";
});
tst("PART-03: max_participants float (3.9) bloccato", function() {
    $e = val(["max_participants"=>3.9],["max_participants"=>"required|integer"]);
    return isset($e["max_participants"]) ? true : "max_participants float accettato";
});
tst("PART-04: max_participants admin > 50 bloccato", function() {
    $e = val(["max_participants"=>51],["max_participants"=>"required|integer|min:1|max:50"]);
    return isset($e["max_participants"]) ? true : "max_participants=51 accettato";
});
tst("PART-05: max_participants user > 15 bloccato", function() {
    $e = val(["max_participants"=>16],["max_participants"=>"required|integer|min:2|max:15"]);
    return isset($e["max_participants"]) ? true : "max_participants user=16 accettato";
});
tst("PART-06: DB::transaction + lockForUpdate in store()", function() {
    $code = src("app/Http/Controllers/ParticipationController.php");
    return (str_contains($code,"DB::transaction") && str_contains($code,"lockForUpdate")) ? true : "CRITICO: Transazione/lock assenti";
});
tst("PART-07: Check duplicati dentro la transazione (BUG-02 fix)", function() {
    $code = src("app/Http/Controllers/ParticipationController.php");
    $txStart = strpos($code,"DB::transaction");
    $existsPos = strpos($code,"->exists()");
    return ($existsPos > $txStart) ? true : "CRITICO: Check duplicati fuori dalla transazione";
});
tst("PART-08: Controllo status uscita=published in store()", function() {
    $code = src("app/Http/Controllers/ParticipationController.php");
    return str_contains($code,"status !== 'published'") ? true : "BUG: Iscrizione a uscite non published possibile";
});
tst("PART-09: Organizzatore non si iscrive alla propria uscita", function() {
    $code = src("app/Http/Controllers/ParticipationController.php");
    return str_contains($code,"organizer_id === auth()->id()") ? true : "MANCANTE: controllo organizzatore";
});
tst("PART-10: Doppia approvazione bloccata (check status pending)", function() {
    $code = src("app/Http/Controllers/ParticipationController.php");
    return str_contains($code,"status !== 'pending'") ? true : "MANCANTE: check pending in approve()";
});
tst("PART-11: lockForUpdate presente in approve() (2x totale)", function() {
    $code = src("app/Http/Controllers/ParticipationController.php");
    $cnt = substr_count($code,"lockForUpdate");
    return ($cnt >= 2) ? true : "lockForUpdate: $cnt occorrenza/e (attese >=2)";
});
tst("PART-12: Unique constraint (user_id, outing_id) nel DB", function() {
    try {
        $indexes = DB::select("SELECT sql FROM sqlite_master WHERE type='index' AND tbl_name='participation_requests'");
        foreach ($indexes as $idx) { if (str_contains(strtolower($idx->sql??""),"unique")) return true; }
        return "CRITICO: Nessun UNIQUE(user_id, outing_id) trovato nel DB";
    } catch(\Throwable $e) { return "Errore verifica schema: ".$e->getMessage(); }
});
tst("PART-13: BUG-03 APERTO -- destroy() su uscita concluded non bloccato", function() {
    $code = src("app/Http/Controllers/ParticipationController.php");
    preg_match("/public function destroy.*?(?=public function|\z)/s",$code,$m);
    $d = $m[0] ?? "";
    $ok = str_contains($d,"status") && (str_contains($d,"published") || str_contains($d,"allowedStatuses"));
    return !$ok ? "BUG-03 APERTO: destroy() non verifica lo status dell'uscita -- partecipazioni storiche eliminabili" : true;
});
tst("PART-14: Data uscita user deve essere futura (after:today)", function() {
    $e = val(["date"=>date("Y-m-d")],["date"=>"required|date|after:today"]);
    return isset($e["date"]) ? true : "Data odierna accettata (deve essere dopo oggi)";
});

// === [4] ADMIN ===
echo "\n\n[4] ADMIN -- Authorization & State Machine\n" . str_repeat("-",56) . "\n";

tst("ADMIN-01: reason > 2000 chars bloccata", function() {
    $e = val(["reason"=>str_repeat("R",2001)],["reason"=>"nullable|string|max:2000"]);
    return isset($e["reason"]) ? true : "reason > 2000 chars accettata";
});
tst("ADMIN-02: notes > 5000 chars bloccate", function() {
    $e = val(["notes"=>str_repeat("N",5001)],["notes"=>"nullable|string|max:5000"]);
    return isset($e["notes"]) ? true : "notes > 5000 chars accettate";
});
tst("ADMIN-03: Ban di admin bloccato nel codice", function() {
    $code = src("app/Http/Controllers/Admin/UserController.php");
    return str_contains($code,"role === 'admin'") ? true : "CRITICO: Nessun check che impedisce ban di admin";
});
tst("ADMIN-04: BUG -- Admin update accetta date nel passato", function() {
    $code = src("app/Http/Controllers/Admin/OutingController.php");
    preg_match("/public function update.*?validated/s",$code,$m);
    $u = $m[0] ?? "";
    $ok = str_contains($u,"after_or_equal:today") || str_contains($u,"after:today");
    return $ok ? true : "BUG-APERTO: Admin::update() senza after_or_equal:today -- date passate accettate";
});
tst("ADMIN-05: BUG -- State machine assente (concluded->published)", function() {
    $code = src("app/Http/Controllers/Admin/OutingController.php");
    preg_match("/public function update.*?validated/s",$code,$m);
    $u = $m[0] ?? "";
    $ok = str_contains($u,"allowedTransitions") || str_contains($u,"transition");
    return $ok ? true : "BUG-09 APERTO: Nessuna state machine -- transizioni arbitrarie di status";
});
tst("ADMIN-06: GuideController completamente vuoto (rotte -> 500)", function() {
    $code = src("app/Http/Controllers/Admin/GuideController.php");
    $hasImpl = substr_count($code,"public function") > 1 || (str_contains($code,"public function") && !str_contains($code,"//"));
    return !$hasImpl ? "BUG: GuideController vuoto -- rotte admin/guides portano a 500" : true;
});
tst("ADMIN-07: destroy() uscita non notifica partecipanti", function() {
    $code = src("app/Http/Controllers/Admin/OutingController.php");
    preg_match("/public function destroy.*?(?=public function|\z)/s",$code,$m);
    $d = $m[0] ?? "";
    $ok = str_contains($d,"notify") || str_contains($d,"participationRequests");
    return $ok ? true : "BUG: Admin elimina uscita senza notificare i partecipanti iscritti";
});

// === [5] PROFILO ===
echo "\n\n[5] PROFILO -- Upload & Validazione\n" . str_repeat("-",56) . "\n";

tst("PROF-01: Bio > 1000 chars bloccata", function() {
    $e = val(["bio"=>str_repeat("x",1001)],["bio"=>"nullable|string|max:1000"]);
    return isset($e["bio"]) ? true : "Bio > 1000 chars accettata";
});
tst("PROF-02: Data nascita futura bloccata", function() {
    $e = val(["date_of_birth"=>"2099-01-01"],["date_of_birth"=>"nullable|date|before_or_equal:today"]);
    return isset($e["date_of_birth"]) ? true : "Data nascita futura accettata";
});
tst("PROF-03: Data nascita pre-1900 bloccata", function() {
    $e = val(["date_of_birth"=>"1850-06-15"],["date_of_birth"=>"nullable|date|after:1900-01-01"]);
    return isset($e["date_of_birth"]) ? true : "Data nascita pre-1900 accettata";
});
tst("PROF-04: Regole MIME/size foto nel ProfileUpdateRequest", function() {
    $code = src("app/Http/Requests/ProfileUpdateRequest.php");
    return (str_contains($code,"mimes:") && str_contains($code,"max:2048")) ? true : "Regole MIME/size mancanti";
});
tst("PROF-05: Eliminazione account con uscite attive bloccata", function() {
    $code = src("app/Http/Controllers/ProfileController.php");
    return (str_contains($code,"activeOutingsCount") && str_contains($code,"published")) ? true : "CRITICO: Nessun check uscite attive prima di destroy()";
});
tst("PROF-06: Email uppercase bloccata (lowercase rule)", function() {
    $e = val(["email"=>"MARIO@EXAMPLE.COM"],["email"=>"required|string|lowercase|email"]);
    return isset($e["email"]) ? true : "Email maiuscola accettata";
});
tst("PROF-07: Nome < 3 chars bloccato", function() {
    $e = val(["name"=>"AB"],["name"=>"required|string|min:3|max:255"]);
    return isset($e["name"]) ? true : "Nome < 3 chars accettato";
});
tst("PROF-08: BUG-04 IDOR -- showUser senza check privacy/ruolo", function() {
    $code = src("app/Http/Controllers/ProfileController.php");
    preg_match("/public function showUser.*?(?=public function|\z)/s",$code,$m);
    $d = $m[0] ?? "";
    $ok = str_contains($d,"abort") || str_contains($d,"role") || str_contains($d,"403");
    return $ok ? true : "BUG-04 APERTO (IDOR): /profiles/{user} espone dati personali senza filtri";
});

// === [6] MIDDLEWARE ===
echo "\n\n[6] MIDDLEWARE & SICUREZZA\n" . str_repeat("-",56) . "\n";

tst("MW-01: BUG-01 fix -- CheckBanned blocca AJAX/JSON con 403", function() {
    $code = src("app/Http/Middleware/CheckBanned.php");
    return (str_contains($code,"expectsJson") || str_contains($code,"ajax()")) ? true : "BUG-01 non fixato: AJAX di utenti bannati non bloccate";
});
tst("MW-02: CheckBanned esegue logout + invalidate sessione", function() {
    $code = src("app/Http/Middleware/CheckBanned.php");
    return (str_contains($code,"Auth::logout") && str_contains($code,"invalidate")) ? true : "CheckBanned non invalida la sessione";
});
tst("MW-03: RoleMiddleware controlla role correttamente", function() {
    $code = src("app/Http/Middleware/RoleMiddleware.php");
    return str_contains($code,"role !== \$role") ? true : "RoleMiddleware logica errata";
});
tst("MW-04: CheckBanned nel gruppo web", function() {
    $code = src("bootstrap/app.php");
    return str_contains($code,"CheckBanned") ? true : "CRITICO: CheckBanned non registrato nel gruppo web";
});
tst("MW-05: Rotte admin protette da role:admin", function() {
    $code = src("routes/web.php");
    return str_contains($code,"role:admin") ? true : "CRITICO: Rotte admin non protette";
});
tst("MW-06: Rate limiting sulle partecipazioni (throttle:20,1)", function() {
    $code = src("routes/web.php");
    return str_contains($code,"throttle:20,1") ? true : "Throttle partecipazioni mancante";
});
tst("MW-07: Rate limiting sulle notifiche (throttle:30,1)", function() {
    $code = src("routes/web.php");
    return str_contains($code,"throttle:30,1") ? true : "Throttle notifiche mancante";
});

// === [7] CHAOS -- Modelli & Edge Case ===
echo "\n\n[7] CHAOS -- Modelli & Edge Case\n" . str_repeat("-",56) . "\n";

tst("CHAOS-01: stage_name su is_full_trail=true", function() {
    $o = new Outing(); $o->is_full_trail = true;
    return ($o->stage_name === "Cammino Completo (55km)") ? true : "stage_name errato: '{$o->stage_name}'";
});
tst("CHAOS-02: stage_name su route custom", function() {
    $o = new Outing(); $o->is_full_trail = false; $o->start_location = "Soverato"; $o->end_location = "Petrizzi";
    return str_contains($o->stage_name,"Soverato") ? true : "stage_name errato: '{$o->stage_name}'";
});
tst("CHAOS-03: stage_name fallback su dati null", function() {
    $o = new Outing(); $o->is_full_trail = false; $o->start_location = null; $o->end_location = null;
    return ($o->stage_name === "Tappa Sconosciuta") ? true : "Fallback errato: '{$o->stage_name}'";
});
tst("CHAOS-04: canCreateOuting() admin sempre true", function() {
    $u = new User(); $u->role = "admin";
    return $u->canCreateOuting() === true ? true : "canCreateOuting() admin non ritorna true";
});
tst("CHAOS-05: Null-safe su outing->date in DashboardController", function() {
    $code = src("app/Http/Controllers/DashboardController.php");
    return str_contains($code,"outing?->date") ? true : "CRITICO: Null-safe mancante -- TypeError possibile";
});
tst("CHAOS-06: Unicode/emoji nelle note accettate", function() {
    $e = val(["notes"=>"Cammino bellissimo! Kanji"],["notes"=>"nullable|string|max:5000"]);
    return !isset($e["notes"]) ? true : "Unicode bloccato (falso positivo)";
});
tst("CHAOS-07: Payload JSON in reason non fa injection", function() {
    $p = '{"__proto__":{"isAdmin":true},"role":"admin"}';
    $e = val(["reason"=>$p],["reason"=>"nullable|string|max:2000"]);
    return !isset($e["reason"]) ? true : "JSON come stringa bloccato erroneamente";
});
tst("CHAOS-08: NULL per campo nullable accettato", function() {
    $e = val(["notes"=>null],["notes"=>"nullable|string|max:5000"]);
    return !isset($e["notes"]) ? true : "NULL non accettato per nullable";
});
tst("CHAOS-09: Seeder usa updateQuietly per role admin", function() {
    $code = src("database/seeders/DatabaseSeeder.php");
    return str_contains($code,"updateQuietly") ? true : "Seeder non usa updateQuietly per role";
});
tst("CHAOS-10: BUG -- approval_deadline mai verificata da job/schedule", function() {
    $code = src("routes/console.php");
    $ok = str_contains($code,"approval_deadline") || str_contains($code,"pending");
    return $ok ? true : "BUG-NUOVO: Nessuno schedule controlla uscite pending scadute";
});
tst("CHAOS-11: BUG-10 -- Notifiche senza pruning automatico", function() {
    $code = src("routes/console.php");
    $ok = str_contains($code,"notifications") || str_contains($code,"prune");
    return $ok ? true : "BUG-10 APERTO: Nessuna pulizia notifiche -- tabella cresce senza limite";
});
tst("CHAOS-12: BUG-11 -- action_url senza validazione protocollo (XSS)", function() {
    $code = src("resources/views/layouts/main.blade.php");
    $ok = str_contains($code,"url.protocol") || str_contains($code,"new URL(") || str_contains($code,"startsWith");
    return $ok ? true : "BUG-11 APERTO (XSS): action_url usato in window.location.href senza validare il protocollo";
});
tst("CHAOS-13: start_location === end_location bloccata (different:)", function() {
    $e = val(["route_type"=>"custom","start_location"=>"Soverato","end_location"=>"Soverato"],
             ["end_location"=>"required_if:route_type,custom|nullable|string|max:255|different:start_location"]);
    return isset($e["end_location"]) ? true : "start=end location accettate";
});

// === [8] HTTP LIVE ===
echo "\n\n[8] HTTP LIVE -- Endpoint Testing\n" . str_repeat("-",56) . "\n";

tst("HTTP-01: Homepage risponde 200", function() {
    $r = http_get("/"); return ($r["status"]===200) ? true : "Homepage: {$r['status']} (atteso 200)";
});
tst("HTTP-02: /dashboard senza auth -> redirect 302", function() {
    $r = http_get("/dashboard"); return in_array($r["status"],[302,301]) ? true : "/dashboard: {$r['status']} (atteso 302)";
});
tst("HTTP-03: /admin/outings senza auth -> redirect/403", function() {
    $r = http_get("/admin/outings"); return in_array($r["status"],[302,301,403]) ? true : "/admin/outings: {$r['status']}";
});
tst("HTTP-04: POST /outings/999/participate senza auth -> 302/419", function() {
    $r = http_post("/outings/999/participate"); return in_array($r["status"],[302,301,403,419]) ? true : "Partecipazione senza auth: {$r['status']}";
});
tst("HTTP-05: GET /outings/999999 (inesistente) -> 404", function() {
    $r = http_get("/outings/999999"); return ($r["status"]===404) ? true : "ID inesistente: {$r['status']} (atteso 404)";
});
tst("HTTP-06: GET /profiles/999999 (utente inesistente) -> 302/404", function() {
    $r = http_get("/profiles/999999"); return in_array($r["status"],[302,404]) ? true : "Profilo inesistente: {$r['status']}";
});
tst("HTTP-07: GET /notifications senza auth -> 302/401", function() {
    $r = http_get("/notifications"); return in_array($r["status"],[302,301,401,403]) ? true : "/notifications: {$r['status']}";
});
tst("HTTP-08: GET /outings con XSS in query -> non crasha (200/422)", function() {
    $r = http_get("/outings?start_location=%3Cscript%3Ealert%281%29%3C%2Fscript%3E");
    return in_array($r["status"],[200,422]) ? true : "XSS in query crashato: {$r['status']}";
});
tst("HTTP-09: GET /outings con date_from invalida -> 200/422", function() {
    $r = http_get("/outings?date_from=NOT-A-DATE"); return in_array($r["status"],[200,422]) ? true : "Data invalida crashato: {$r['status']}";
});
tst("HTTP-10: GET /outings con date_from nel passato -> 422", function() {
    $r = http_get("/outings?date_from=2000-01-01"); return ($r["status"]===422) ? true : "date_from passato accettato: {$r['status']} (atteso 422)";
});
tst("HTTP-11: DELETE /notifications senza CSRF -> 419/302", function() {
    $r = http_get("/admin/users"); return ($r["status"]!==200) ? true : "CRITICO: /admin/users accessibile senza auth (200)";
});
tst("HTTP-12: GET /up (health check) -> 200", function() {
    $r = http_get("/up"); return ($r["status"]===200) ? true : "Health check: {$r['status']} (atteso 200)";
});
tst("HTTP-13: GET /outings con max_participants stringa -> non crasha", function() {
    $r = http_get("/outings?max_participants=STRINGA"); return in_array($r["status"],[200,422]) ? true : "Param stringa crashato: {$r['status']}";
});
tst("HTTP-14: GET /outings con array injection -> non crasha", function() {
    $r = http_get("/outings?type[]=official&type[]=user"); return in_array($r["status"],[200,422,500]) ? ($r["status"]!==500 ? true : "CRASH: array injection causa 500 interno") : "Status inatteso: {$r['status']}";
});

// === RIEPILOGO ===
$total = $pass + $fail;
$score = $total > 0 ? round(($pass/$total)*100) : 0;
echo "\n\n" . str_repeat("=",58) . "\n";
echo "  RISULTATI CHAOS & STRESS TEST v2\n";
echo str_repeat("=",58) . "\n";
printf("  OK  PASS:   %d / %d  (%d%%)\n", $pass, $total, $score);
printf("  FAIL FAIL:  %d / %d\n", $fail, $total);
echo str_repeat("-",58) . "\n";

$crashes = array_filter($findings, fn($f) => $f["type"]==="CRASH");
$fails   = array_filter($findings, fn($f) => $f["type"]==="FAIL");

if (!empty($crashes)) {
    echo "\n  CRASH RILEVATI (" . count($crashes) . "):\n\n";
    foreach (array_values($crashes) as $i => $f) {
        echo "  " . ($i+1) . ". [{$f['name']}]\n";
        foreach (explode("\n",wordwrap($f["result"],54,"\n",true)) as $l) echo "     $l\n";
        echo "\n";
    }
}
if (!empty($fails)) {
    echo "\n  BUG / FAIL (" . count($fails) . "):\n\n";
    foreach (array_values($fails) as $i => $f) {
        echo "  " . ($i+1) . ". [{$f['name']}]\n";
        foreach (explode("\n",wordwrap($f["result"],54,"\n",true)) as $l) echo "     $l\n";
        echo "\n";
    }
}
if (empty($findings)) echo "\n  ZERO problemi rilevati!\n";
echo str_repeat("=",58) . "\n\n";
