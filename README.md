# 🥾 CoastTrak - Kalabria Coast to Coast

**CoastTrak** è una piattaforma web dedicata alla promozione e alla gestione del cammino **"Kalabria Coast to Coast"**, un suggestivo percorso escursionistico di 55 km che attraversa l'entroterra calabrese collegando la costa ionica (**Soverato**) con quella tirrenica (**Pizzo Calabro**).

La piattaforma permette di coordinare ed organizzare le escursioni (uscite) sul cammino, distinguendo tra eventi ufficiali ed eventi proposti direttamente dalla community (P2P), integrando un sistema di tracciamento dei progressi tramite una **Credenziale Digitale**.

---

## 🚀 Funzionalità Principali

### 👥 Profilo Utenti e Permessi
L'applicazione gestisce tre tipologie di utenti:

1. **Utenti Non Registrati (Ospiti):**
   * Esplorazione e visualizzazione delle escursioni pubbliche disponibili sul cammino.
   * Visualizzazione dei dettagli di ciascuna escursione (data, punto di ritrovo, difficoltà, note).

2. **Utenti Registrati (Community):**
   * Prenotazione e richiesta di partecipazione alle escursioni disponibili.
   * **Credenziale Digitale:** Un sistema che certifica le tappe e i cammini completati.
   * **Sblocco P2P (Peer-to-Peer):** Gli utenti esperti che hanno completato l'intero cammino (almeno una volta) sbloccano la possibilità di creare e proporre nuove uscite auto-organizzate per la community (soggette ad approvazione dell'amministratore).

3. **Amministratore (Admin):**
   * Gestione del catalogo delle tappe ufficiali del cammino.
   * Creazione e gestione delle uscite ufficiali condotte da guide esperte.
   * Assegnazione e gestione delle guide professionali.
   * Approvazione, rifiuto o annullamento delle uscite P2P proposte dagli utenti.
   * Gestione completa degli utenti del sistema.

---

## 🛠️ Stack Tecnologico

L'applicazione segue l'architettura **MVC (Model-View-Controller)**:

* **Backend:** PHP 8.3 con il framework **Laravel 13.x**
* **Database:** MySQL / SQLite
* **Autenticazione:** **Laravel Breeze** (sessioni crittografate, hashing bcrypt, recupero password)
* **Frontend:** HTML5, CSS3, **Bootstrap 5** per il responsive layout, **FontAwesome 6** per l'iconografia e JavaScript Vanilla per la dinamicità lato client.
* **Templating Engine:** **Blade** (ereditarietà dei layout, protezione XSS automatica)

---

## 📂 Struttura del Codice Sorgente (Custom)

I file principali sviluppati su misura per il progetto sono organizzati come segue:

* **`app/Models/`**: Modelli Eloquent per la mappatura del database:
  * [User.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/User.php): Gestione dei ruoli, permessi e controllo sblocco uscite P2P (`canCreateOuting`).
  * [Outing.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/Outing.php): Modello delle uscite (ufficiali e P2P) con gestione delle tratte personalizzate (`start_location`, `end_location`).
  * [Stage.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/Stage.php): Mappatura delle tappe ufficiali del percorso di 55km.
  * [Guide.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/Guide.php): Mappatura delle guide escursionistiche.
  * [ParticipationRequest.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/ParticipationRequest.php): Richieste di partecipazione degli utenti alle escursioni.
  * [DigitalCredential.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Models/DigitalCredential.php): Certificazione di completamento tappe/cammini.
* **`app/Http/Controllers/`**: Gestione del flusso logico:
  * [OutingController.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Http/Controllers/OutingController.php) (Pubblico): Visualizzazione delle uscite disponibili.
  * [UserOutingController.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Http/Controllers/UserOutingController.php): Proposta e creazione di uscite P2P.
  * [ParticipationController.php](file:///c:/Users/Giovanni/Desktop/CoastTrak/app/Http/Controllers/ParticipationController.php): Richieste e cancellazioni di partecipazione.
  * `Admin/`: Controller per il pannello amministratore (approvazione uscite, gestione guide e utenti).
* **`routes/web.php`**: Definizione centralizzata di rotte pubbliche, protette e per amministratori.
* **`resources/views/`**: Template Blade per le viste utente e amministratore.
* **`database/migrations/` & `database/seeders/`**: Schema del database relazionale e popolamento dati di test.

---

## 💻 Installazione e Configurazione Locale

Segui questi passaggi per avviare l'applicazione sul tuo server locale (es. tramite XAMPP o PHP CLI):

### 1. Clonare il repository ed entrare nella cartella
```bash
git clone https://github.com/giovagio04/CoastTrak.git
cd CoastTrak
```

### 2. Configurare le dipendenze PHP
Installa le dipendenze tramite Composer:
```bash
composer install
```

### 3. Configurare l'ambiente (`.env`)
Duplica il file di esempio `.env.example` creando il file `.env`:
```bash
cp .env.example .env
```
Apri il file `.env` ed inserisci le credenziali di connessione del tuo database locale (es. MySQL su XAMPP):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coasttrak
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generare la chiave dell'applicazione e preparare il database
Genera la chiave crittografica e applica le migrazioni popolando il database con i dati iniziali (Seeder):
```bash
php artisan key:generate
php artisan migrate --seed
```

### 5. Configurare le dipendenze Frontend
Installa i pacchetti NPM ed esegui la build degli asset:
```bash
npm install
npm run build
```

### 6. Avviare il server di sviluppo
Per avviare l'applicazione in modalità sviluppo (sia il server PHP che Vite):
```bash
npm run dev
```
In alternativa, puoi avviare manualmente il server Laravel con:
```bash
php artisan serve
```
L'applicazione sarà disponibile su `http://localhost:8000`.
