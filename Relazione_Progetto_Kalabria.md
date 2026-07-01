# Relazione di Progetto: Piattaforma Web "Kalabria Coast to Coast"

## 1. Introduzione al Progetto
Il progetto consiste nello sviluppo di una piattaforma web dedicata alla gestione e promozione del "Kalabria Coast to Coast", un cammino di 55 km che collega Soverato a Pizzo Calabro, attraversando l'entroterra calabrese. La piattaforma è pensata per gestire le escursioni (uscite) sia ufficiali (create dagli amministratori e guidate da guide esperte) sia auto-organizzate dagli utenti della community.

L'applicazione offre un sistema di autenticazione e gestione dei ruoli, permettendo:
- Agli **utenti non registrati** di esplorare le uscite pubbliche disponibili.
- Agli **utenti registrati** di iscriversi alle escursioni, ottenere la "Credenziale Digitale" (che certifica le tappe e i cammini completati), e, una volta maturata l'esperienza (completamento dell'intero percorso), di proporre nuove uscite auto-organizzate (peer-to-peer).
- All'**amministratore** di creare le tappe, gestire le uscite ufficiali, assegnare le guide ed approvare/rifiutare le uscite proposte dagli utenti.

## 2. Architettura e Tecnologie Utilizzate

L'applicazione è stata sviluppata adottando un'architettura **MVC (Model-View-Controller)**, che garantisce una netta separazione tra la logica di business, l'interfaccia utente e l'interazione con i dati.

Le tecnologie principali impiegate sono:
- **Backend:** PHP 8.x con framework Laravel
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla), e Bootstrap 5
- **Templating:** Blade Engine (nativo di Laravel)

### 2.1 Il Framework Laravel vs PHP Vanilla
Nel contesto del progetto, la scelta di adottare **Laravel** (rispetto allo sviluppo in PHP Vanilla, tipicamente introdotto all'inizio del corso) ha portato numerosi e significativi vantaggi:

1. **Routing Elegante e Sicuro:** Laravel gestisce le rotte in modo centralizzato (es. `web.php`), permettendo di definire facilmente middleware per il controllo degli accessi (es. limitare pagine solo all'admin). In PHP Vanilla, questo avrebbe richiesto inclusioni multiple di script di controllo in ogni singola pagina, con alto rischio di ridondanza e vulnerabilità.
2. **Sicurezza Integrata:** Laravel offre protezione nativa contro attacchi CSRF (Cross-Site Request Forgery) tramite il direttivo `@csrf` nei form, e protezione contro SQL Injection grazie all'ORM. Implementare tali difese manualmente in PHP Vanilla è complesso e prono a errori.
3. **Gestione del Database (Eloquent ORM e Migrations):** Invece di scrivere query SQL grezze con PDO o `mysqli`, l'uso di Eloquent ORM ha permesso di mappare le tabelle del database su oggetti PHP (Models). Le *Migrations* hanno garantito il versioning del database: la struttura (es. tabelle users, outings, participation_requests) è definita nel codice, rendendo il deploy e le modifiche strutturali (come il refactoring delle tappe) immediate e sicure, senza dover esportare/importare script `.sql`.

### 2.2 Frontend: Bootstrap 5 vs CSS Personalizzato
Per lo sviluppo dell'interfaccia si è scelto di utilizzare il framework CSS **Bootstrap 5**.
Rispetto alla stesura di fogli di stile CSS completamente personalizzati, i vantaggi sono stati:
- **Rapidità di Sviluppo (RAD):** L'utilizzo di classi di utilità pronte all'uso (`d-flex`, `mt-4`, `shadow-sm`) e componenti precostituiti (Card, Modal, Alert) ha abbattuto i tempi di realizzazione dell'UI.
- **Responsività Garantita:** Il sistema a griglia flessibile di Bootstrap garantisce che la piattaforma sia perfettamente usabile sia su dispositivi mobili che su desktop, requisito fondamentale per un portale dedicato ad escursionisti che potrebbero consultare il sito da smartphone.

### 2.3 Gestione dell'Autenticazione (Laravel Breeze)
Per la registrazione, il login e la gestione dei profili è stato integrato lo starter kit dell'autenticazione.
Rispetto a una soluzione scritta da zero (con `session_start()`, gestione manuale degli hash delle password con `password_hash()` e verifica dei cookie):
- Fornisce un sistema di sessioni crittografato e robusto.
- Gestisce automaticamente la validazione delle password, l'hashing bcrypt e le funzioni di "Ricordami", il tutto seguendo le migliori best practice di sicurezza web aggiornate agli standard odierni.

### 2.4 Motore di Templating: Blade
Laravel utilizza il motore **Blade**. Rispetto all'inserimento di codice PHP crudo (`<?php echo $var; ?>`) all'interno dell'HTML:
- **Ereditarietà (Layouts):** Abbiamo definito un layout master (es. `layouts/main.blade.php`) contenente l'header e il footer comuni. Le singole viste (View) estendono questo layout riempiendo solo le sezioni necessarie (`@section('content')`). Questo riduce drasticamente la duplicazione del codice rispetto ai classici `include('header.php')` manuali.
- **Sintassi Pulita:** Direttive come `@if`, `@foreach` e `{{ $variabile }}` (che sfugge automaticamente l'output prevenendo attacchi XSS) rendono il codice dell'interfaccia estremamente più leggibile e manutenibile per chiunque debba intervenire sul frontend.

## 3. Scelte Progettuali e Gestione della Logica Complessa
Durante lo sviluppo, si sono affrontate sfide logiche interessanti che sono state risolte sfruttando a pieno le potenzialità della tecnologia:

- **Dinamismo dei Form (Vanilla JS):** È stato impiegato JavaScript puro per migliorare la User Experience nei form di creazione delle uscite. Ad esempio, il sistema disabilita automaticamente nel menù a tendina del "Punto di Arrivo" la stessa località selezionata come "Punto di Partenza", prevenendo errori di inserimento lato client, alleggerendo il carico del server.
- **Integrità dei Dati e Validazione:** Tutte le richieste in ingresso passano attraverso le funzioni di validazione del Controller di Laravel (es. `$request->validate()`). Questo garantisce che nessun dato corrotto (es. date passate, numero di partecipanti negativo) possa raggiungere il database.
- **Logica Condizionale P2P:** La piattaforma include una logica di sblocco (gamification/sicurezza): un utente normale non può creare un'uscita a meno che la funzione `completedFullTrailsCount()` del suo Model non restituisca un valore maggiore di zero. L'approccio ad Oggetti (OOP) e l'ecosistema MVC hanno permesso di incapsulare questa logica elegantemente nel Model `User`, mantenendo i Controller snelli (Fat Model, Skinny Controller).

## 4. Conclusioni
L'adozione di un framework moderno (Laravel) combinato con librerie frontend consolidate (Bootstrap) ha permesso di realizzare una piattaforma web solida, sicura, manutenibile e scalabile. Le soluzioni tecniche adottate superano i limiti dei classici script procedurali, offrendo una struttura professionale che agevola il lavoro in team, il riuso del codice e la protezione nativa dalle più comuni vulnerabilità del web.
