# 🥾 CoastTrak - Kalabria Coast to Coast

**CoastTrak** è una piattaforma web dedicata alla promozione e alla gestione del cammino **"Kalabria Coast to Coast"**, un suggestivo percorso escursionistico di 55 km che attraversa l'entroterra calabrese collegando la costa ionica (**Soverato**) con quella tirrenica (**Pizzo Calabro**).

La piattaforma permette di coordinare ed organizzare le escursioni (uscite) sul cammino, distinguendo tra eventi ufficiali ed eventi proposti direttamente dalla community (P2P), integrando un sistema di tracciamento dei progressi tramite una **Credenziale Digitale**.

---

## 🚀 Funzionalità Principali

### 👤 Utenti Non Registrati (Ospiti)
Gli ospiti possono esplorare e visualizzare le escursioni pubbliche disponibili sul cammino, consultando i dettagli come la data, il punto di ritrovo, il livello di difficoltà e le note aggiuntive per ciascuna escursione.

### 👥 Utenti Registrati (Community)
Gli utenti registrati possono prenotare e richiedere la partecipazione alle escursioni disponibili. Dispongono inoltre di una Credenziale Digitale personale che certifica le tappe e i cammini completati. Gli utenti esperti che hanno completato l'intero cammino almeno una volta sbloccano la possibilità di creare e proporre nuove uscite auto-organizzate per la community, le quali saranno soggette ad approvazione da parte dell'amministratore.

### 🔑 Amministratore (Admin)
L'amministratore gestisce il catalogo delle tappe ufficiali del cammino e si occupa di creare le uscite ufficiali condotte da guide esperte. Assegna e coordina le guide professionali, approva, rifiuta o annulla le uscite P2P proposte dagli utenti, e gestisce in modo completo tutti gli account del sistema.

---

## 🛠️ Stack Tecnologico

L'applicazione segue l'architettura **MVC (Model-View-Controller)**.

**Backend:** Sviluppato in PHP 8.3 utilizzando il framework **Laravel 13.x**.

**Database:** Gestione dei dati relazionali tramite MySQL / SQLite.

**Autenticazione:** Sistema di login e registrazione integrato con **Laravel Breeze** per garantire sessioni crittografate, hashing bcrypt delle password e funzionalità di recupero.

**Frontend:** Interfaccia responsive realizzata con HTML5, CSS3, **Bootstrap 5**, **FontAwesome 6** per l'iconografia e JavaScript Vanilla per le interazioni dinamiche lato client.

**Templating Engine:** Utilizzo del motore **Blade** per l'ereditarietà dei layout e la protezione automatica contro attacchi XSS.

---

## 📂 Struttura del Codice Sorgente (Custom)

I file principali sviluppati su misura per il progetto sono organizzati all'interno della struttura di Laravel.

**Modelli Eloquent (`app/Models/`):**
[User.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/User.php) gestisce i ruoli, i permessi e il controllo di sblocco per le uscite P2P. [Outing.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/Outing.php) definisce il modello delle uscite gestendo le tratte personalizzate. [Stage.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/Stage.php) mappa le tappe ufficiali del percorso. [Guide.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/Guide.php) rappresenta le guide escursionistiche. [ParticipationRequest.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/ParticipationRequest.php) gestisce le richieste di iscrizione e [DigitalCredential.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/DigitalCredential.php) certifica il completamento dei percorsi.

**Controller (`app/Http/Controllers/`):**
[OutingController.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Http/Controllers/OutingController.php) gestisce la visualizzazione delle uscite pubbliche. [UserOutingController.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Http/Controllers/UserOutingController.php) si occupa della proposta di uscite P2P. [ParticipationController.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Http/Controllers/ParticipationController.php) elabora le richieste di partecipazione. La cartella `Admin/` raccoglie i controller per l'approvazione delle escursioni e per la gestione di guide e utenti.

**Rotte (`routes/web.php`):**
Definizione centralizzata di tutte le rotte pubbliche, protette per gli utenti e riservate agli amministratori.

**Viste (`resources/views/`):**
Template Blade utilizzati per rendere le pagine dell'applicazione per gli utenti e per l'amministrazione.

**Database (`database/migrations/` & `database/seeders/`):**
File per la definizione dello schema del database e per il popolamento iniziale con dati di test.

---

## 💻 Installazione e Configurazione Locale

Segui questi passaggi per configurare ed avviare l'applicazione sul tuo server di sviluppo locale.

### Clonazione del repository
Clona il repository ed entra nella cartella di progetto:
```bash
git clone https://github.com/giovagio04/CoastTrak.git
cd CoastTrak
```

### Configurazione dipendenze PHP
Installa le librerie esterne necessarie tramite Composer:
```bash
composer install
```

### Configurazione dell'ambiente
Crea il file di ambiente locale copiando quello di esempio:
```bash
cp .env.example .env
```
Apri il file `.env` ed inserisci le credenziali di connessione del tuo database locale (es. MySQL su XAMPP).

### Chiave e Migrazioni Database
Genera la chiave crittografica e inizializza il database con i dati iniziali di prova:
```bash
php artisan key:generate
php artisan migrate --seed
```

### Installazione dipendenze Frontend
Compila le risorse JavaScript e CSS installando i pacchetti NPM richiesti:
```bash
npm install
npm run build
```

### Avvio del server di sviluppo
Per far partire l'applicazione sia in modalità server locale che con il compilatore di asset Vite, esegui:
```bash
npm run dev
```
In alternativa puoi avviare manualmente il server di backend con `php artisan serve`.
L'applicazione sarà disponibile all'indirizzo `http://localhost:8000`.
