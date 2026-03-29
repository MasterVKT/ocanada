# Ô Canada — HR Management Application
# Cursor Project Rules — v1.0 — March 2026
# Language: French UI / English rules
# Client: Ô Canada, Douala, Cameroon

---

## 1. PROJECT IDENTITY AND SCOPE

You are building **Ô Canada**, a web-based HR management system for a 13-employee company in Douala, Cameroon. This application fully replaces a paper-based HR process. Every feature, rule, and decision in this codebase must be anchored to one of the following functional domains:

- **Attendance tracking** — clock-in/clock-out via a dedicated kiosk terminal, automatic late/absent detection
- **Leave management** — employee requests, admin approval workflow, OHADA-compliant balance calculation
- **Visitor management** — agent-operated registration, QR-code badge, real-time tracking
- **Real-time unified presence view** — single screen showing all employees + visitors currently on-site
- **Anti-fraud kiosk mode** — IP-restricted terminal, PIN-authenticated, physically isolated
- **HR documents** — secure upload/download per employee
- **Internal notifications** — in-app only, no email infrastructure required
- **Shift and planning** — weekly schedule assignment, basis for late detection
- **Reports and exports** — PDF (A4) and CSV, server-side generation
- **Financial dashboard** — absenteeism cost estimation (informational only)
- **AI modules** — leave writing assistant + HR chatbot (Anthropic API, server-side only)

### What is explicitly OUT OF SCOPE

Never implement or suggest: biometric hardware integration, payroll calculation engine, recruitment module, predictive analytics, email sending infrastructure (unless SMTP is explicitly configured), external HR system integrations, mobile native apps.

### Localization Context

| Parameter | Value |
|---|---|
| UI language | French (FR-CM) |
| Timezone | `Africa/Douala` (UTC+1) — use this in every date/time operation |
| Date format displayed | DD/MM/YYYY |
| Time format displayed | HH:MM (24-hour) |
| Currency | XAF (Franc CFA) — for absenteeism cost estimates only |
| Working week | Monday–Friday (Saturday configurable) |
| Labor law reference | Cameroonian Labor Code + OHADA regulations |

---

## 2. TECHNOLOGY STACK

### Mandatory Stack — No Deviations Allowed

| Layer | Technology | Version | Notes |
|---|---|---|---|
| Backend framework | CodeIgniter 4 | **4.5.x LTS** | Latest stable CI4 only |
| Backend language | PHP | **8.2+** | Use 8.x features actively |
| Database | MySQL | **8.0+** | InnoDB, utf8mb4 only |
| Frontend CSS | Bootstrap | **5.3.x** | No Tailwind, no custom CSS frameworks |
| Frontend JS | Vanilla JavaScript | **ES6+** | No React, Vue, Angular, jQuery |
| Charts | Chart.js | **4.x** | Via CDN only |
| QR Code | qrcode.js | **1.5.x** | Local file in `public/assets/js/` — not CDN |
| Icons | Bootstrap Icons | **1.11.x** | Via CDN |
| PDF generation | DOMPDF | **2.x** | Via Composer |
| AI API | Anthropic Claude | `claude-sonnet-4-20250514` | Server-side PHP only |
| Fonts | Google Fonts | Inter (main), Roboto Mono (technical data) | Via CDN |

### What is Forbidden in the Stack

- **No SPA frameworks** (React, Vue, Angular, Svelte, Next.js, etc.)
- **No jQuery** — use Vanilla JS exclusively
- **No Tailwind CSS** — use Bootstrap 5 utility classes only
- **No ORM beyond CI4's Query Builder** (no Eloquent, no Doctrine)
- **No Node.js in production** — only for tooling if needed
- **No CDN for qrcode.js** — must be served locally (offline reliability)
- **Never call the Anthropic API from client-side JavaScript** — always via PHP backend

### Navigation Model

This is a **traditional SSR (Server-Side Rendered) web application**. Navigation uses standard HTML `<a>` links with full page reloads. AJAX is used **only** for these specific interactions:

1. Chatbot messages (chatbot.js → `/ia/chatbot`)
2. Leave writing assistant (inline → `/ia/assistant-conge`)
3. Real-time presence view auto-refresh (every 2 minutes)
4. Notification badge count polling
5. Kiosk confirmation display after clock operation

For everything else: form submits → redirect → flash message. No inline updates, no reactive state.

---

## 3. PROJECT DIRECTORY STRUCTURE

This structure is **fixed**. Do not create new top-level directories or reorganize without explicit instruction.

```
ocanada/                            # Project root
│
├── docs/                           # Spécifications fonctionnelles et documentation projet (source de vérité)
├── bootstrap-5.3.8-dist/           # Distribution locale Bootstrap 5.3.8 utilisée pour les styles de l'application
├── app/                            # CodeIgniter application code
│   │
│   ├── Config/
│   │   ├── App.php                 # baseURL, locale (fr), timezone (Africa/Douala), session config
│   │   ├── Database.php            # MySQL credentials (read from .env)
│   │   ├── Routes.php              # ALL routes defined here with filter declarations
│   │   └── Filters.php             # Filter class aliases + global/per-route registration
│   │
│   ├── Controllers/
│   │   ├── BaseController.php      # Parent: currentUser, renderView(), jsonResponse(), db
│   │   ├── AuthController.php      # GET/POST login, logout, forgot-password, reset-password
│   │   ├── KiosqueController.php   # GET index, POST pointer-arrivee, POST pointer-depart
│   │   ├── RealtimeController.php  # GET unified real-time view (admin + agent)
│   │   ├── NotificationsController.php  # GET list, POST mark-read, POST mark-all-read
│   │   ├── AIController.php        # POST /ia/assistant-conge, POST /ia/chatbot
│   │   ├── ProfileController.php   # GET/POST personal profile + PIN change
│   │   ├── VisitorController.php   # CRUD visitors (agent + admin)
│   │   │
│   │   ├── Admin/
│   │   │   ├── DashboardController.php    # KPIs, charts, quick actions
│   │   │   ├── EmployeesController.php    # CRUD employees (wizard 3 steps)
│   │   │   ├── PresencesController.php    # Daily view, history, manual correction
│   │   │   ├── LeaveController.php        # All leaves, approval, balances management
│   │   │   ├── PlanningController.php     # Shift models CRUD + employee assignment
│   │   │   ├── DocumentsController.php    # Upload, list, download, delete HR docs
│   │   │   ├── ReportsController.php      # Generate PDF + CSV reports
│   │   │   ├── FinanceController.php      # Absenteeism cost dashboard
│   │   │   ├── AuditController.php        # Read-only audit log view
│   │   │   └── ConfigController.php       # System config + public holidays management
│   │   │
│   │   ├── Employee/
│   │   │   ├── DashboardController.php    # Personal KPIs, mini calendar, quick actions
│   │   │   ├── LeaveController.php        # Submit request, view history + AI assistant
│   │   │   ├── PresencesController.php    # Personal attendance history
│   │   │   ├── PlanningController.php     # Read-only personal schedule (2 weeks)
│   │   │   └── DocumentsController.php    # Read-only personal documents
│   │   │
│   │   └── Agent/
│   │       └── DashboardController.php    # Lands on real-time view, links to visitor CRUD
│   │
│   ├── Models/
│   │   ├── UtilisateurModel.php           # Auth, session data, password reset tokens
│   │   ├── EmployeModel.php               # Employee profile, never returns salaire_journalier to non-admin
│   │   ├── PresenceModel.php              # Clock-in/out records, status calculation
│   │   ├── VisiteurModel.php              # Visitor records, badge number generation
│   │   ├── CongeModel.php                 # Leave requests, overlap detection
│   │   ├── SoldeCongeModel.php            # Leave balances, OHADA init logic
│   │   ├── ShiftModel.php                 # Shift model templates
│   │   ├── AffectationShiftModel.php      # Employee-to-shift assignments with date ranges
│   │   ├── NotificationModel.php          # In-app notifications, read/unread state
│   │   ├── DocumentRHModel.php            # HR document metadata (file stored in storage/)
│   │   ├── JourFerieModel.php             # Cameroonian public holidays
│   │   ├── ConfigSystemeModel.php         # Key-value system configuration
│   │   └── AuditLogModel.php              # Append-only audit log — no update/delete methods
│   │
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── main.php            # Authenticated layout: sidebar + topbar + main + footer + chatbot
│   │   │   ├── kiosque.php         # Full-screen kiosk layout (#1A365D bg, no nav)
│   │   │   └── auth.php            # Centered card layout (red gradient, no nav)
│   │   │
│   │   ├── components/
│   │   │   ├── sidebar_admin.php   # Admin left navigation
│   │   │   ├── sidebar_employe.php # Employee left navigation
│   │   │   ├── sidebar_agent.php   # Agent left navigation
│   │   │   ├── topbar.php          # Top bar: logo, user menu, notification bell
│   │   │   ├── chatbot.php         # Floating chat button + slide-in panel
│   │   │   ├── kpi_card.php        # Reusable KPI card partial (icon, value, label, trend)
│   │   │   └── pagination.php      # Bootstrap 5 pagination partial
│   │   │
│   │   ├── auth/                   # login.php, forgot.php, reset.php
│   │   ├── kiosque/                # index.php
│   │   ├── shared/                 # realtime.php, notifications.php, profil.php
│   │   │
│   │   ├── admin/
│   │   │   ├── dashboard/          # index.php
│   │   │   ├── employes/           # index.php, create.php (step1/2/3), edit.php, show.php
│   │   │   ├── presences/          # index.php (daily), historique.php, correction.php
│   │   │   ├── conges/             # index.php, show.php (with approval form)
│   │   │   ├── visiteurs/          # index.php, show.php, historique.php
│   │   │   ├── planning/           # index.php (calendar), shifts.php (CRUD modals)
│   │   │   ├── documents/          # index.php
│   │   │   ├── rapports/           # index.php
│   │   │   ├── finance/            # index.php
│   │   │   ├── audit/              # index.php
│   │   │   └── config/             # index.php, jours_feries.php
│   │   │
│   │   ├── employe/
│   │   │   ├── dashboard/          # index.php
│   │   │   ├── conges/             # index.php (history + request form), demande.php
│   │   │   ├── presences/          # index.php (personal history)
│   │   │   ├── planning/           # index.php (read-only, 2 weeks)
│   │   │   └── documents/          # index.php (read-only)
│   │   │
│   │   ├── agent/                  # dashboard.php (= realtime view entry)
│   │   └── errors/                 # 403.php, 404.php, 500.php
│   │
│   ├── Filters/
│   │   ├── AuthFilter.php          # Checks session + account active status on every request
│   │   ├── RoleFilter.php          # Checks session role matches required role param
│   │   └── KiosqueIPFilter.php     # Checks request IP against config_systeme whitelist
│   │
│   ├── Libraries/
│   │   ├── PresenceCalculator.php  # calculateStatus(), calculateDuration(), calculateOvertime()
│   │   ├── WorkingDaysCalculator.php # calculate($start, $end) — the ONLY place for this logic
│   │   ├── NotificationService.php # All notification creation methods (never use model directly)
│   │   ├── AnthropicClient.php     # call($systemPrompt, $messages, $maxTokens) — server-side only
│   │   └── RateLimiter.php         # check($userId, $action, $limit, $windowSec), increment()
│   │
│   ├── Helpers/
│   │   └── ocanada_helper.php      # format_date_fr(), format_heure(), format_xaf(), badge_number()
│   │
│   ├── Language/
│   │   └── fr/                     # French translations for all validation messages
│   │
│   ├── Commands/                   # CI4 CLI commands for cron jobs
│   │   ├── MarkAbsences.php        # ocanada:mark-absences   — daily at 23:59
│   │   ├── CloseVisits.php         # ocanada:close-visits    — daily at 23:59
│   │   ├── CheckContracts.php      # ocanada:check-contracts — weekly Monday 08:00
│   │   └── PendingLeaves.php       # ocanada:pending-leaves  — daily at 09:00
│   │
│   └── Database/
│       ├── Migrations/             # Numbered 001→008 in dependency order
│       └── Seeds/
│           └── InitialDataSeeder.php  # Default admin account, default shift, default config
│
├── public/                         # *** WEB ROOT — Apache/Nginx DocumentRoot points here ***
│   ├── index.php                   # CI4 front controller — do not modify
│   ├── .htaccess                   # URL rewriting for CI4
│   └── assets/
│       ├── css/
│       │   └── ocanada.css         # Bootstrap overrides + custom variables + components
│       ├── js/
│       │   ├── app.js              # Global: CSRF AJAX setup, notification polling, showToast()
│       │   ├── kiosque.js          # Real-time clock, form reset logic, confirmation timer
│       │   ├── chatbot.js          # Panel toggle, message history, fetch to /ia/chatbot
│       │   └── qrcode.min.js       # qrcode.js — LOCAL copy, never reference CDN version
│       └── img/
│           └── logo.svg            # Ô Canada brand logo
│
├── storage/                        # *** OUTSIDE WEB ROOT — Never accessible via HTTP ***
│   ├── uploads/
│   │   ├── employees/              # Profile photos (UUID-named, JPEG/PNG only)
│   │   ├── documents/              # HR documents (UUID-named, PDF/JPEG/PNG)
│   │   └── visitors/               # Visitor photos (UUID-named, optional capture)
│   └── logs/                       # CI4 application logs (PHP errors, debug)
│
├── vendor/                         # Composer — never edit manually
├── .env                            # Credentials — NEVER commit to git
├── .env.example                    # Safe template — commit this
├── composer.json
├── composer.lock
└── .gitignore                      # Must include: .env, storage/uploads/, vendor/, *.log
```

---

## 4. CODEIGNITER 4 IMPLEMENTATION RULES

### 4.1 Controllers

Every controller must extend `App\Controllers\BaseController`. Always declare `strict_types=1`. Always type-hint parameters and return types.

```php
<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeModel;

class EmployeesController extends BaseController
{
    protected EmployeModel $employeModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->employeModel = model(EmployeModel::class);
    }

    public function index(): string
    {
        return $this->renderView('admin/employes/index', [
            'title'    => 'Gestion des employés',
            'employes' => $this->employeModel->getActiveList(),
        ]);
    }
}
```

**BaseController must provide:**

| Property / Method | Type | Description |
|---|---|---|
| `$this->currentUser` | `array` | `['user_id', 'role', 'nom_complet', 'employe_id', 'photo_profil']` from session |
| `$this->db` | `Database` | CI4 database connection |
| `$this->session` | `Session` | CI4 session service |
| `renderView(string $view, array $data)` | `string` | Loads the correct layout (based on role), injects shared data |
| `jsonResponse(mixed $data, int $code)` | `ResponseInterface` | JSON response with correct headers |

`renderView()` automatically injects into every view:
- `$currentUser` — the authenticated user array
- `$unreadCount` — number of unread notifications for the bell badge
- `$csrfField` — pre-rendered CSRF field
- `$title` — page title (for `<title>` tag and `<h1>`)

### 4.2 Models

Every model must declare all these protected properties:

```php
<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PresenceModel extends Model
{
    protected $table         = 'presences';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'employe_id', 'date_presence', 'heure_arrivee', 'heure_depart',
        'duree_travaillee_minutes', 'statut_arrivee', 'minutes_retard',
        'source', 'commentaire_admin', 'shift_modele_id',
    ];
    protected $validationRules = [
        'employe_id'    => 'required|is_natural_no_zero',
        'date_presence' => 'required|valid_date',
        'source'        => 'required|in_list[kiosque,correction_admin]',
    ];
}
```

Model naming conventions for custom methods:

| Pattern | Method example | Returns |
|---|---|---|
| Single record by ID | `findById(int $id)` | `array|null` |
| Filtered list | `getByDate(string $date)` | `array` |
| Scoped to employee | `getForEmployee(int $employeId, ...)` | `array` |
| Count | `countActiveToday()` | `int` |
| Existence check | `existsForDate(int $empId, string $date)` | `bool` |
| Status update | `approveRequest(int $id, int $adminId, string $comment)` | `bool` |

### 4.3 Routing Rules

All routes are defined in `app/Config/Routes.php`. Declare all filters inline on the route group.

```php
<?php
// Public routes (no auth required)
$routes->get('login',              'AuthController::login');
$routes->post('login',             'AuthController::loginProcess');
$routes->get('logout',             'AuthController::logout');
$routes->get('mot-de-passe-oublie', 'AuthController::forgotPassword');
$routes->post('mot-de-passe-oublie', 'AuthController::forgotPasswordProcess');
$routes->get('reinitialiser/(:segment)', 'AuthController::resetPassword/$1');
$routes->post('reinitialiser/(:segment)', 'AuthController::resetPasswordProcess/$1');

// Kiosk — IP filter only (no session needed)
$routes->group('kiosque', ['filter' => 'kiosque'], function ($routes) {
    $routes->get('/',                'KiosqueController::index');
    $routes->post('arrivee',         'KiosqueController::pointArrivee');
    $routes->post('depart',          'KiosqueController::pointDepart');
});

// Admin routes
$routes->group('admin', ['filter' => 'auth,role:admin'], function ($routes) {
    $routes->get('/',                           'Admin\DashboardController::index');
    $routes->get('employes',                    'Admin\EmployeesController::index');
    $routes->get('employes/creer',              'Admin\EmployeesController::create');
    $routes->post('employes/creer',             'Admin\EmployeesController::store');
    $routes->get('employes/(:num)',             'Admin\EmployeesController::show/$1');
    $routes->get('employes/(:num)/modifier',    'Admin\EmployeesController::edit/$1');
    $routes->post('employes/(:num)/modifier',   'Admin\EmployeesController::update/$1');
    $routes->post('employes/(:num)/desactiver', 'Admin\EmployeesController::deactivate/$1');
    $routes->get('presences',                   'Admin\PresencesController::index');
    $routes->post('presences/(:num)/corriger',  'Admin\PresencesController::correct/$1');
    $routes->get('conges',                      'Admin\LeaveController::index');
    $routes->post('conges/(:num)/approuver',    'Admin\LeaveController::approve/$1');
    $routes->post('conges/(:num)/refuser',      'Admin\LeaveController::reject/$1');
    $routes->get('rapports',                    'Admin\ReportsController::index');
    $routes->post('rapports/generer',           'Admin\ReportsController::generate');
    $routes->get('audit',                       'Admin\AuditController::index');
    $routes->get('finance',                     'Admin\FinanceController::index');
    $routes->get('configuration',               'Admin\ConfigController::index');
    $routes->post('configuration',              'Admin\ConfigController::update');
    $routes->get('documents',                   'Admin\DocumentsController::index');
    $routes->post('documents/upload',           'Admin\DocumentsController::upload');
    $routes->get('documents/(:num)/telecharger','Admin\DocumentsController::download/$1');
    $routes->post('documents/(:num)/supprimer', 'Admin\DocumentsController::delete/$1');
    $routes->get('planning',                    'Admin\PlanningController::index');
});

// Employee routes
$routes->group('employe', ['filter' => 'auth,role:employe'], function ($routes) {
    $routes->get('/',                           'Employee\DashboardController::index');
    $routes->get('conges',                      'Employee\LeaveController::index');
    $routes->post('conges/demander',            'Employee\LeaveController::submit');
    $routes->get('presences',                   'Employee\PresencesController::index');
    $routes->get('planning',                    'Employee\PlanningController::index');
    $routes->get('documents',                   'Employee\DocumentsController::index');
    $routes->get('documents/(:num)/telecharger','Employee\DocumentsController::download/$1');
});

// Agent routes
$routes->group('accueil', ['filter' => 'auth,role:agent'], function ($routes) {
    $routes->get('/', 'Agent\DashboardController::index');
});

// Shared authenticated routes (all roles)
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('temps-reel',                       'RealtimeController::index');
    $routes->get('notifications',                    'NotificationsController::index');
    $routes->post('notifications/lire/(:num)',        'NotificationsController::markRead/$1');
    $routes->post('notifications/tout-lire',         'NotificationsController::markAllRead');
    $routes->get('profil',                           'ProfileController::index');
    $routes->post('profil',                          'ProfileController::update');
    $routes->post('profil/pin',                      'ProfileController::updatePin');
    $routes->post('ia/assistant-conge',              'AIController::assistantConge');
    $routes->post('ia/chatbot',                      'AIController::chatbot');
    $routes->get('visiteurs/enregistrer',            'VisitorController::create');
    $routes->post('visiteurs/enregistrer',           'VisitorController::store');
    $routes->post('visiteurs/(:num)/sortie',         'VisitorController::checkout/$1');
    $routes->get('visiteurs/historique',             'VisitorController::history');
});
```

### 4.4 Filters Implementation

**AuthFilter** — checks on every protected route:
1. Session exists and is active
2. `user_id` and `role` are in session
3. Re-queries DB to verify `utilisateurs.statut = 'actif'` (handles real-time account deactivation)
4. On failure: redirect to `/login` (not authenticated) or 403 page (authenticated but blocked)

**RoleFilter** — receives role as argument (e.g., `role:admin`):
1. Reads `role` param from filter configuration
2. Compares against `$session->get('role')`
3. On mismatch: logs to `audit_log` with type `ACCES_NON_AUTORISE`, returns 403 view

**KiosqueIPFilter** — IP whitelist check:
1. Gets `$_SERVER['REMOTE_ADDR']` (with reverse proxy handling)
2. Reads `ip_kiosque_autorisees` from `config_systeme` (CSV of allowed IPs)
3. On mismatch: returns a standalone response (no layout) with message "Terminal non habilité au pointage"
4. No audit log for every attempt — only log if suspicious (>5 failed attempts from unknown IP)

### 4.5 View Rendering Contract

Views are plain PHP. The layout system works as follows:

```php
// In BaseController::renderView()
public function renderView(string $view, array $data = []): string
{
    // Inject shared data
    $data['currentUser']  = $this->currentUser;
    $data['unreadCount']  = model(NotificationModel::class)
                               ->countUnread($this->currentUser['user_id']);
    $data['currentRoute'] = service('router')->getMatchedRoute()[0] ?? '';

    // Determine layout from role
    $layout = match($this->currentUser['role']) {
        'admin'   => 'layouts/main',
        'employe' => 'layouts/main',
        'agent'   => 'layouts/main',
        default   => 'layouts/main',
    };

    $data['content'] = view($view, $data);
    return view($layout, $data);
}
```

**In every view file**, the first line is a comment declaring the data it expects:

```php
<?php
// View: admin/employes/index.php
// Expects: $employes (array), $title (string)
?>
```

**esc() usage in views** — always escape user-supplied output:

```php
// String content
<?= esc($employe['nom']) ?>

// HTML attributes
<input value="<?= esc($employe['email'], 'attr') ?>">

// URL parameters
<a href="/admin/employes/<?= esc($employe['id'], 'url') ?>">

// JavaScript context (for inline JS variables only)
<script>const employeId = <?= json_encode((int) $employe['id']) ?>;</script>

// NEVER do these:
<?= $employe['nom'] ?>
<?php echo $data['commentaire']; ?>
```

---

## 5. DATABASE RULES

### 5.1 Schema Conventions

```sql
-- All tables: InnoDB + utf8mb4
CREATE TABLE `presences` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    -- ... columns ...
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Column naming rules:

| Column type | Convention | Example |
|---|---|---|
| Primary key | `id INT UNSIGNED AUTO_INCREMENT` | `id` |
| Foreign key | `{singular_table}_id INT UNSIGNED` | `employe_id`, `utilisateur_id` |
| Timestamps | `DATETIME NOT NULL` | `date_creation`, `date_evenement` |
| Dates | `DATE NOT NULL` | `date_presence`, `date_embauche` |
| Times | `TIME` | `heure_arrivee`, `heure_depart` |
| Status/type | `ENUM(...)` lowercase | `statut ENUM('actif','inactif')` |
| Boolean-like | `TINYINT(1) DEFAULT 0` | `actif`, `lue` |
| Money | `DECIMAL(10,2)` | `salaire_journalier` |
| Long text | `TEXT` | `commentaire_admin`, `description` |
| Short string | `VARCHAR(N)` with sensible N | `nom VARCHAR(100)` |
| JSON data | `JSON NULL` | `donnees_avant`, `donnees_apres` |

### 5.2 Required Indexes and Constraints

```sql
-- presences — enforces one record per employee per day
UNIQUE KEY `uk_employe_date` (`employe_id`, `date_presence`)

-- utilisateurs — login identifier
UNIQUE KEY `uk_email` (`email`)

-- employes — kiosk identification
UNIQUE KEY `uk_matricule` (`matricule`)

-- soldes_conges — one balance row per employee per year
UNIQUE KEY `uk_employe_annee` (`employe_id`, `annee`)

-- visiteurs — badge uniqueness across all history
UNIQUE KEY `uk_badge` (`numero_badge`)

-- jours_feries — no duplicate holiday dates
UNIQUE KEY `uk_date_ferie` (`date_ferie`)

-- Performance indexes (required, not optional)
INDEX `idx_presences_date` (`date_presence`)
INDEX `idx_presences_statut` (`statut_arrivee`)
INDEX `idx_visiteurs_statut_date` (`statut`, `heure_arrivee`)
INDEX `idx_notifications_destinataire_lue` (`destinataire_id`, `lue`)
INDEX `idx_demandes_conge_statut` (`statut`)
INDEX `idx_audit_log_type_date` (`type_evenement`, `date_evenement`)
```

### 5.3 Transactions — Mandatory Contexts

These operations **must** be wrapped in a database transaction. A partial write is worse than a failed operation.

```php
// Pattern for all transactional operations
$this->db->transStart();
try {
    // ... multiple insert/update operations ...
    $this->db->transComplete();
    if ($this->db->transStatus() === false) {
        throw new \RuntimeException('Transaction failed');
    }
} catch (\Throwable $e) {
    $this->db->transRollback();
    log_message('error', 'Transaction failed: ' . $e->getMessage());
    return $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de l\'opération'], 500);
}
```

Operations requiring transactions:

| Operation | Tables written | Why |
|---|---|---|
| Employee creation | `employes` + `utilisateurs` + `soldes_conges` | All three or none |
| Leave approval | `demandes_conge` + `soldes_conges` + `notifications` | Balance must stay consistent |
| Leave cancellation | `demandes_conge` + `soldes_conges` + `notifications` | Days must be restored atomically |
| Manual presence correction | `presences` + `audit_log` | Correction and its trace must be atomic |
| Shift assignment | `affectations_shifts` (deactivate old + create new) | Avoid overlap |

### 5.4 Query Builder Patterns

```php
// Simple where + get
$result = $this->db->table('employes')
    ->where('statut', 'actif')
    ->orderBy('nom', 'ASC')
    ->get()
    ->getResultArray();

// Join query
$result = $this->db->table('presences p')
    ->select('p.*, e.nom, e.prenom, e.poste, e.photo')
    ->join('employes e', 'e.id = p.employe_id')
    ->where('p.date_presence', $date)
    ->where('p.statut_arrivee !=', 'absent')
    ->get()
    ->getResultArray();

// Complex WHERE with date range
$result = $this->db->table('demandes_conge')
    ->where('employe_id', $employeId)
    ->where('statut', 'approuvee')
    ->where('date_debut <=', $dateFin)
    ->where('date_fin >=', $dateDebut)
    ->get()
    ->getResultArray();

// Count
$count = $this->db->table('presences')
    ->where('date_presence', date('Y-m-d'))
    ->whereIn('statut_arrivee', ['present', 'retard'])
    ->countAllResults();

// Raw query when Query Builder cannot express it (prepared statement only)
$query = $this->db->query(
    'SELECT e.id, COUNT(p.id) as jours_absents
     FROM employes e
     LEFT JOIN presences p ON p.employe_id = e.id
         AND p.date_presence BETWEEN ? AND ?
         AND p.statut_arrivee = ?
     WHERE e.statut = ?
     GROUP BY e.id',
    [$dateDebut, $dateFin, 'absent', 'actif']
);
```

### 5.5 The Salary Column — Special Rule

`employes.salaire_journalier` is salary data. **Rules:**

- Never appear in queries used by Employee or Agent controllers
- Never appear in JSON API responses consumed by the frontend
- Never appear in CSV exports (except `Rapport 4 — Absentéisme` which is admin-only)
- In `EmployeModel`, create a separate method `getForAdmin(int $id)` that returns salary, and `getProfile(int $id)` that does not

```php
// In EmployeModel:
public function getProfile(int $id): ?array
{
    // Used by employee self-view — NO salary
    return $this->select('id, matricule, nom, prenom, date_naissance, genre,
                          nationalite, adresse, telephone_1, telephone_2,
                          email_pro, photo, poste, departement, type_contrat,
                          date_embauche, heure_debut_travail, heure_fin_travail')
                ->find($id);
}

public function getForAdmin(int $id): ?array
{
    // Admin only — includes salary
    return $this->find($id);  // returns all fields
}
```

---

## 6. SECURITY — NON-NEGOTIABLE RULES

Every item in this section is a **critical security requirement**. Violations block deployment.

### 6.1 SQL Injection Prevention

```php
// CORRECT — Query Builder (PDO-escaped automatically)
$this->db->where('email', $email)->get('utilisateurs');
$this->db->like('nom', $search, 'both');  // auto-escaped %search%

// CORRECT — Raw query with prepared parameters
$this->db->query('SELECT * FROM employes WHERE matricule = ?', [$matricule]);

// ❌ FORBIDDEN — Direct string concatenation
$this->db->query("SELECT * FROM employes WHERE nom = '" . $nom . "'");
$this->db->query("SELECT * FROM employes WHERE nom LIKE '%" . $search . "%'");
"WHERE id = " . $_GET['id']  // never
```

Input validation before any database operation:

```php
// IDs from URL params — always cast and validate
$id = (int) $this->request->getPost('employe_id');
if ($id <= 0) {
    return $this->jsonResponse(['success' => false, 'message' => 'ID invalide'], 422);
}

// Dates
$date = $this->request->getPost('date_debut');
if (!$this->validation->check($date, 'valid_date[Y-m-d]')) {
    // reject
}
```

### 6.2 XSS Prevention

```php
// In Views — esc() on EVERY output that comes from user input or database
<?= esc($employe['nom']) ?>                        // HTML context (default)
<?= esc($employe['commentaire'], 'html') ?>        // Explicit HTML
<input value="<?= esc($val, 'attr') ?>">          // Attribute context
<a href="/path/<?= esc($id, 'url') ?>">           // URL context

// In JavaScript — NEVER use innerHTML with external content
element.textContent = apiResponse.message;        // ✅ Safe
element.innerHTML   = apiResponse.message;        // ❌ FORBIDDEN

// When building HTML in JavaScript, use DOM methods
const li = document.createElement('li');
li.textContent = response.nom;
list.appendChild(li);
```

### 6.3 CSRF Protection

```php
// In every HTML form (POST)
<form method="POST" action="/admin/employes/creer">
    <?= csrf_field() ?>
    <!-- ... -->
</form>

// In layout <head> for AJAX
<meta name="csrf-token" content="<?= csrf_hash() ?>">
```

```javascript
// AJAX POST wrapper — always include CSRF
async function securePost(url, data) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify(data),
    });
    return response.json();
}
```

CSRF filter must be registered in `app/Config/Filters.php` as a **global** filter on POST methods:

```php
// app/Config/Filters.php
public array $globals = [
    'before' => [
        'csrf',  // applies to all POST requests
    ],
];
```

### 6.4 Authentication and Session Security

```php
// Login — on success
session()->regenerate(true);  // destroy old session, create new one (prevents session fixation)
session()->set([
    'user_id'     => $user['id'],
    'role'        => $user['role'],
    'nom_complet' => $user['nom_complet'],
    'employe_id'  => $user['employe_id'] ?? null,
    'photo_profil'=> $user['photo_profil'] ?? null,
]);

// Never store in session: passwords, PINs, salary data, full user object

// Login error — always generic, never specific
// ❌ WRONG: "Ce mot de passe est incorrect"
// ❌ WRONG: "Aucun compte trouvé avec cet email"
// ✅ CORRECT:
$error = "Email ou mot de passe incorrect.";

// Brute force — after 5 failed attempts in 15 minutes
// Store attempt counter in CI4 cache keyed by email+IP
// Lock message: "Compte temporairement bloqué. Réessayez dans X minutes."
// Always log ECHEC_CONNEXION in audit_log
```

Password hashing:

```php
// ALWAYS bcrypt with cost 12
$hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);

// ALWAYS use password_verify — never re-hash and compare
$isValid = password_verify($input, $storedHash);

// PIN kiosque — same algorithm, separate column
$pinHash = password_hash($pin, PASSWORD_BCRYPT, ['cost' => 12]);

// Never log either value — not in CI4 logs, not in audit_log
```

### 6.5 File Upload Security

```php
public function handleUpload(\CodeIgniter\HTTP\Files\UploadedFile $file, string $type): ?string
{
    // 1. Check real MIME type (not extension, not declared Content-Type)
    $finfo    = new \finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file->getTempName());

    $allowedMimes = $type === 'photo'
        ? ['image/jpeg', 'image/png']
        : ['application/pdf', 'image/jpeg', 'image/png'];

    if (!in_array($realMime, $allowedMimes, true)) {
        return null;  // reject — caller shows error
    }

    // 2. Check file size
    $maxBytes = $type === 'photo' ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
    if ($file->getSize() > $maxBytes) {
        return null;
    }

    // 3. Generate UUID filename — never use original name
    $extension = $type === 'photo' ? 'jpg' : $file->getClientExtension();
    $newName   = bin2hex(random_bytes(16)) . '.' . strtolower($extension);

    // 4. Store OUTSIDE web root
    $destDir = ROOTPATH . 'storage/uploads/' . ($type === 'photo' ? 'employees/' : 'documents/');
    $file->move($destDir, $newName);

    return $newName;  // only the filename, caller stores full path reference
}
```

File serving (downloads) — always through a controller, never via direct URL:

```php
public function download(int $documentId): ResponseInterface
{
    $doc = model(DocumentRHModel::class)->find($documentId);

    if (!$doc) {
        return $this->response->setStatusCode(404);
    }

    // Ownership check — employee can only access their own documents
    if ($this->currentUser['role'] === 'employe'
        && $doc['employe_id'] !== $this->currentUser['employe_id']) {
        // Log unauthorized access attempt
        model(AuditLogModel::class)->log('ACCES_NON_AUTORISE', $this->currentUser['user_id'],
            "Tentative d'accès au document #$documentId");
        return $this->response->setStatusCode(403);
    }

    $path = ROOTPATH . 'storage/uploads/documents/' . $doc['nom_fichier'];

    if (!file_exists($path)) {
        return $this->response->setStatusCode(404);
    }

    return $this->response
        ->setHeader('Content-Type', mime_content_type($path))
        ->setHeader('Content-Disposition', 'attachment; filename="' . $doc['nom_original'] . '"')
        ->setBody(file_get_contents($path));
}
```

### 6.6 Data-Level Security (Row-Level Authorization)

For employee-facing queries, **always** filter by the authenticated employee's ID from session:

```php
// ✅ CORRECT — session-derived ID
$myEmployeId = $this->currentUser['employe_id'];
$presences = model(PresenceModel::class)
    ->where('employe_id', $myEmployeId)
    ->where('date_presence >=', $dateDebut)
    ->findAll();

// ❌ FORBIDDEN — ID from request (attacker controls this)
$employeId = $this->request->getGet('employe_id');
$employeId = $this->request->getPost('employe_id');

// ❌ FORBIDDEN — trusting route parameter for personal data
// Route: /employe/presences/(:num) → never use $1 as employee ID for personal data
```

Exception: Admin routes receive employee IDs as route parameters to view any employee's data — this is correct because the admin filter has already verified the `admin` role.

### 6.7 Anthropic API Key Security

```php
// ✅ CORRECT — fetch from DB, use server-side only
class AnthropicClient
{
    private string $apiKey;

    public function __construct()
    {
        // Read from database — never from $_ENV, never hardcoded
        $this->apiKey = model(ConfigSystemeModel::class)->get('anthropic_api_key');
    }

    public function call(string $systemPrompt, array $messages, int $maxTokens = 500): ?string
    {
        // $this->apiKey is ONLY used in this class, in HTTP headers
        // It NEVER leaves this class
    }
}

// ❌ FORBIDDEN — any of these
$data['apiKey'] = $this->apiKey;          // never pass to view
echo $this->apiKey;                        // never output
log_message('debug', $this->apiKey);      // never log
$response = $this->jsonResponse(['key' => $this->apiKey]);  // NEVER
```

---

## 7. MODULE IMPLEMENTATION RULES

### 7.1 Kiosk Terminal (`/kiosque`)

**Layout:** `kiosque.php` only. Never render using `main.php`.

**IP enforcement** in `KiosqueIPFilter`:
- Compare `$request->getIPAddress()` (handles X-Forwarded-For via CI4 config) against CSV from `config_systeme.ip_kiosque_autorisees`
- On failure: render a self-contained HTML response (no layout, no nav) with only the "Terminal non habilité" message
- Log nothing for normal mismatches — only log if the same unknown IP attempts more than 5 times in an hour (potential probe)

**Time window enforcement** — always server-side in `KiosqueController`:
```php
$now = new \DateTime('now', new \DateTimeZone('Africa/Douala'));
$heure = (int) $now->format('H') * 60 + (int) $now->format('i'); // minutes since midnight

$debutArrivee = $this->configModel->getAsMinutes('heure_debut_pointage_arrivee'); // 06:00 = 360
$finArrivee   = $this->configModel->getAsMinutes('heure_fin_pointage_arrivee');   // 10:30 = 630
$debutDepart  = $this->configModel->getAsMinutes('heure_debut_pointage_depart');  // 15:00 = 900
$finDepart    = $this->configModel->getAsMinutes('heure_fin_pointage_depart');    // 21:00 = 1260

// Clock-in window check
if ($heure < $debutArrivee || $heure > $finArrivee) {
    return $this->jsonResponse([
        'success' => false,
        'message' => 'Pointage non autorisé à cette heure. Contactez votre responsable.',
    ]);
}
```

**PIN brute-force protection:**
```php
$cacheKey = 'kiosque_pin_fails_' . $employe['id'];
$fails = cache($cacheKey) ?? 0;

if ($fails >= 3) {
    return $this->jsonResponse([
        'success' => false,
        'message' => 'Compte temporairement bloqué (10 min). Contactez votre responsable.',
    ]);
}

if (!password_verify($pinSaisi, $employe['pin_kiosque'])) {
    cache()->save($cacheKey, $fails + 1, 600); // 10 minutes
    model(AuditLogModel::class)->log('ECHEC_PIN_KIOSQUE', null, [
        'employe_id' => $employe['id'],
        'ip'         => $this->request->getIPAddress(),
    ]);
    return $this->jsonResponse(['success' => false, 'message' => 'PIN incorrect.']);
}

cache()->delete($cacheKey); // Clear on success
```

**Confirmation display** (4 seconds, then form reset) — in `kiosque.js`:
```javascript
function showConfirmation(data) {
    const box = document.getElementById('confirmation-box');
    document.getElementById('confirm-photo').src   = data.photo_url;
    document.getElementById('confirm-nom').textContent   = data.nom_complet;
    document.getElementById('confirm-heure').textContent = data.heure_enregistree;
    document.getElementById('confirm-statut').textContent = data.statut;
    box.classList.remove('d-none');

    setTimeout(() => {
        box.classList.add('d-none');
        document.getElementById('kiosque-form').reset();
        document.getElementById('champ-matricule').focus();
    }, 4000);
}
```

### 7.2 Working Days Calculator

`WorkingDaysCalculator` is the **single source of truth** for all working day calculations. It must never be duplicated or bypassed.

```php
<?php
declare(strict_types=1);

namespace App\Libraries;

use App\Models\JourFerieModel;
use App\Models\ConfigSystemeModel;

class WorkingDaysCalculator
{
    private array $publicHolidays;
    private bool  $samediOuvrable;

    public function __construct()
    {
        $year = (int) date('Y');
        $this->publicHolidays = array_column(
            model(JourFerieModel::class)->getForYear($year),
            'date_ferie'
        );
        $this->samediOuvrable = (bool) model(ConfigSystemeModel::class)->get('samedi_ouvrable');
    }

    /**
     * Count working days between two dates (inclusive).
     * Excludes: weekends, public holidays, and Saturday if not configured as working.
     */
    public function calculate(string $dateStart, string $dateEnd): int
    {
        $start  = new \DateTime($dateStart);
        $end    = new \DateTime($dateEnd);
        $count  = 0;
        $cursor = clone $start;

        while ($cursor <= $end) {
            $dow  = (int) $cursor->format('N'); // 1=Mon ... 7=Sun
            $date = $cursor->format('Y-m-d');

            $isWeekend = ($dow === 7) || ($dow === 6 && !$this->samediOuvrable);
            $isHoliday = in_array($date, $this->publicHolidays, true);

            if (!$isWeekend && !$isHoliday) {
                $count++;
            }

            $cursor->modify('+1 day');
        }

        return $count;
    }

    /**
     * Check if a given date is a working day.
     */
    public function isWorkingDay(string $date): bool
    {
        return $this->calculate($date, $date) === 1;
    }
}
```

This class is injected (via `new WorkingDaysCalculator()` or CI4 service) in:
- `Employee\LeaveController::submit()` — count requested days in real-time
- `Admin\LeaveController::approve()` — verify count before deducting balance
- `Commands\MarkAbsences` — determine which days require an absence record
- `Admin\ReportsController::generate()` — calculate working days in the period

### 7.3 OHADA Leave Balance Initialization

`SoldeCongeModel::initForEmployee()` — exact OHADA Article 89 implementation:

```php
public function initForEmployee(int $employeId, string $dateEmbauche, int $annee): bool
{
    $today     = new \DateTime();
    $embauche  = new \DateTime($dateEmbauche);
    $debutAnnee = new \DateTime($annee . '-01-01');

    // Months worked (since hire or since start of the requested year, whichever is later)
    $refDate      = max($embauche, $debutAnnee);
    $interval     = $refDate->diff($today);
    $moisTravailles = $interval->y * 12 + $interval->m;
    $moisTravailles = min($moisTravailles, 12); // cap at 12 months

    // Base: 1.5 days per worked month
    $joursBase = 1.5 * $moisTravailles;

    // Seniority bonus (calculated from hire date to today)
    $ancienneteAns = ($today->getTimestamp() - $embauche->getTimestamp()) / (365.25 * 86400);
    $bonusSeniority = match(true) {
        $ancienneteAns >= 20 => 4,
        $ancienneteAns >= 15 => 3,
        $ancienneteAns >= 10 => 2,
        $ancienneteAns >= 5  => 1,
        default              => 0,
    };

    $joursTotal = $joursBase + $bonusSeniority;

    return $this->insert([
        'employe_id'      => $employeId,
        'annee'           => $annee,
        'jours_total'     => $joursTotal,
        'jours_pris'      => 0,
        'jours_restants'  => $joursTotal,
        'date_mise_a_jour' => date('Y-m-d H:i:s'),
    ]);
}
```

Leave type `maternite_paternite` — **never** deduct from `soldes_conges`. In `Admin\LeaveController::approve()`:

```php
if ($demande['type_conge'] !== 'maternite_paternite') {
    // Deduct from balance
    model(SoldeCongeModel::class)->deduct($employeId, $demande['jours_ouvrables']);
}
// Always update demande status regardless
```

### 7.4 Notification Service

**Never** call `model(NotificationModel::class)->insert()` directly in controllers. Always use `NotificationService`:

```php
<?php
declare(strict_types=1);

namespace App\Libraries;

use App\Models\NotificationModel;
use App\Models\UtilisateurModel;

class NotificationService
{
    private NotificationModel $notifModel;

    public function __construct()
    {
        $this->notifModel = model(NotificationModel::class);
    }

    public function notifyCongeSubmitted(array $demande): void
    {
        // Find all admin user IDs
        $admins = model(UtilisateurModel::class)->getAdminIds();
        foreach ($admins as $adminId) {
            $this->notifModel->insert([
                'destinataire_id' => $adminId,
                'type'            => 'NOTIF_CONGE_SOUMIS',
                'message'         => "{$demande['prenom']} {$demande['nom']} a soumis une demande de congé du {$demande['date_debut_fr']} au {$demande['date_fin_fr']}",
                'lien'            => "/admin/conges/{$demande['id']}",
                'date_creation'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function notifyCongeDecision(array $demande): void
    {
        $statut  = $demande['statut'] === 'approuvee' ? 'approuvé' : 'refusé';
        $message = "Votre congé du {$demande['date_debut_fr']} au {$demande['date_fin_fr']} a été {$statut}.";
        if ($demande['statut'] === 'refusee' && !empty($demande['commentaire_admin'])) {
            $message .= " Commentaire : {$demande['commentaire_admin']}";
        }

        $this->notifModel->insert([
            'destinataire_id' => $demande['utilisateur_id'],
            'type'            => $demande['statut'] === 'approuvee' ? 'NOTIF_CONGE_APPROUVE' : 'NOTIF_CONGE_REFUSE',
            'message'         => $message,
            'lien'            => "/employe/conges",
            'date_creation'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function notifyAbsence(array $employe, array $adminIds): void { /* ... */ }
    public function notifyVisiteurLong(array $visiteur, array $adminAndAgentIds): void { /* ... */ }
    public function notifyContratExpiration(array $employe, array $adminIds): void { /* ... */ }
    public function notifyDepartManquant(array $employe, array $adminIds): void { /* ... */ }
    public function notifyPendingLeave(array $demande, array $adminIds): void { /* ... */ }
}
```

### 7.5 Visitor Badge and QR Code

Badge number generation — guaranteed unique, daily sequential:

```php
// In VisitorController::store() or VisitorModel::generateBadgeNumber()
public function generateBadgeNumber(): string
{
    $today  = date('Ymd');
    $prefix = 'V' . $today . '-';

    // Get the highest sequence number for today
    $last = $this->db->table('visiteurs')
        ->select('numero_badge')
        ->like('numero_badge', $prefix, 'after')
        ->orderBy('numero_badge', 'DESC')
        ->limit(1)
        ->get()
        ->getRowArray();

    if (!$last) {
        return $prefix . '001';
    }

    $lastSeq = (int) substr($last['numero_badge'], -3);
    return $prefix . str_pad((string) ($lastSeq + 1), 3, '0', STR_PAD_LEFT);
}
```

QR code content string (passed to `qrcode.js` via `data-qr` attribute):

```php
// In the badge view, set data-qr attribute — escaping is mandatory
$qrData = "BADGE:{$visiteur['numero_badge']}|NOM:{$visiteur['nom_complet']}|ARRIVEE:{$visiteur['heure_arrivee_fr']}|POUR:{$visiteur['personne_a_voir']}";
```

```html
<canvas id="qr-badge" data-qr="<?= esc($qrData, 'attr') ?>"></canvas>
```

```javascript
// In badge view inline script
document.querySelectorAll('[data-qr]').forEach(canvas => {
    QRCode.toCanvas(canvas, canvas.dataset.qr, { width: 120, margin: 1 });
});
```

Print trigger — CSS only, no JavaScript needed:

```html
<button onclick="window.print()" class="btn btn-secondary btn-sm">
    <i class="bi bi-printer"></i> Imprimer le badge
</button>
```

```css
@media print {
    body > * { display: none !important; }
    .badge-visiteur-print { display: block !important; }
}
```

### 7.6 AI Modules

**AIController** handles both AI endpoints with identical security structure:

```php
// POST /ia/assistant-conge
public function assistantConge(): ResponseInterface
{
    // 1. Rate limit: 3 calls/hour/user
    if (!$this->rateLimiter->check($this->currentUser['user_id'], 'assistant_conge', 3, 3600)) {
        return $this->jsonResponse([
            'success' => false,
            'message' => "Limite d'utilisation atteinte. Réessayez dans une heure.",
        ], 429);
    }

    // 2. Validate input
    $texteInformel = trim($this->request->getPost('texte') ?? '');
    $typeConge     = $this->request->getPost('type_conge') ?? '';
    $nbJours       = (int) ($this->request->getPost('nb_jours') ?? 0);

    if (mb_strlen($texteInformel) < 5 || mb_strlen($texteInformel) > 200) {
        return $this->jsonResponse(['success' => false, 'message' => 'Texte invalide.'], 422);
    }

    // 3. Build system prompt (server-side, never trust client prompts)
    $systemPrompt = "Tu es un assistant de rédaction RH pour une entreprise camerounaise. "
        . "Rédige uniquement un motif formel de demande de congé en français, professionnel, "
        . "courtois, de 2 à 4 phrases maximum. "
        . "Type de congé : {$typeConge}. Durée : {$nbJours} jours ouvrables. "
        . "Ne génère rien d'autre que le motif. Pas de salutation, pas de signature.";

    // 4. Call API via AnthropicClient (never expose API key)
    $response = $this->anthropicClient->call($systemPrompt, [
        ['role' => 'user', 'content' => $texteInformel],
    ], 200);

    if ($response === null) {
        return $this->jsonResponse([
            'success' => false,
            'message' => "L'assistant est temporairement indisponible. Rédigez votre motif manuellement.",
        ], 503);
    }

    // 5. Increment rate limit counter
    $this->rateLimiter->increment($this->currentUser['user_id'], 'assistant_conge', 3600);

    return $this->jsonResponse(['success' => true, 'motif' => $response]);
}
```

```php
// POST /ia/chatbot
public function chatbot(): ResponseInterface
{
    // 1. Rate limit: 20 messages/hour/user
    if (!$this->rateLimiter->check($this->currentUser['user_id'], 'chatbot', 20, 3600)) {
        return $this->jsonResponse([
            'success' => false,
            'message' => "Vous avez atteint la limite de messages. Réessayez dans une heure.",
        ], 429);
    }

    // 2. Validate and get conversation history (from client, max 10 exchanges)
    $userMessage = trim($this->request->getPost('message') ?? '');
    $history     = $this->request->getPost('history') ?? [];
    $history     = array_slice((array) $history, -20); // max 10 exchanges = 20 messages

    if (empty($userMessage)) {
        return $this->jsonResponse(['success' => false, 'message' => 'Message vide.'], 422);
    }

    // 3. Fetch contextual data from DB (role-dependent)
    $contextData = $this->buildChatbotContext();

    // 4. Build system prompt with DB-sourced context
    $systemPrompt = $this->buildChatbotSystemPrompt($contextData);

    // 5. Append user message to history
    $history[] = ['role' => 'user', 'content' => $userMessage];

    // 6. Call API
    $response = $this->anthropicClient->call($systemPrompt, $history, 500);

    if ($response === null) {
        return $this->jsonResponse([
            'success' => false,
            'message' => "Je suis temporairement indisponible. Consultez directement votre espace.",
        ], 503);
    }

    $this->rateLimiter->increment($this->currentUser['user_id'], 'chatbot', 3600);

    return $this->jsonResponse(['success' => true, 'response' => $response]);
}

private function buildChatbotContext(): array
{
    $userId    = $this->currentUser['user_id'];
    $role      = $this->currentUser['role'];
    $employeId = $this->currentUser['employe_id'];

    $context = [
        'user_role'  => $role,
        'user_name'  => $this->currentUser['nom_complet'],
    ];

    if ($role === 'employe' && $employeId) {
        $annee = (int) date('Y');
        $mois  = date('Y-m');

        $context['solde_conges'] = model(SoldeCongeModel::class)
            ->where('employe_id', $employeId)
            ->where('annee', $annee)
            ->first();

        $context['presences_mois'] = model(PresenceModel::class)
            ->where('employe_id', $employeId)
            ->where("DATE_FORMAT(date_presence, '%Y-%m')", $mois)
            ->findAll();

        $context['demandes_en_cours'] = model(CongeModel::class)
            ->where('employe_id', $employeId)
            ->whereIn('statut', ['en_attente', 'approuvee'])
            ->findAll();
    }

    if ($role === 'admin') {
        $context['total_employes_actifs'] = model(EmployeModel::class)
            ->where('statut', 'actif')->countAllResults();
        $context['presents_aujourd_hui'] = model(PresenceModel::class)
            ->where('date_presence', date('Y-m-d'))
            ->whereIn('statut_arrivee', ['present', 'retard'])
            ->countAllResults();
        $context['conges_en_attente'] = model(CongeModel::class)
            ->where('statut', 'en_attente')->countAllResults();
    }

    return $context;
}
```

Chatbot system prompt must include:
- Assistant identity (RH assistant for Ô Canada)
- User profile (name, role, position)
- DB context data (in French, formatted)
- Key HR rules (OHADA leave calculation, public holidays list)
- Hard instruction: respond only in French, concise, conversational tone
- Hard instruction: refuse non-HR questions politely
- Hard instruction: never reveal salary data, never provide data about other employees (for `employe` role)

### 7.7 Report Generation (DOMPDF)

```php
// In Admin\ReportsController::generate()
public function generate(): ResponseInterface
{
    $type   = $this->request->getPost('type');
    $params = $this->request->getPost();

    // Fetch data
    $data = match($type) {
        'presences_mensuel'  => $this->buildPresencesData($params),
        'conges_annuels'     => $this->buildCongesData($params),
        'visiteurs'          => $this->buildVisiteursData($params),
        'absenteisme'        => $this->buildAbsenteismeData($params),
        default              => null,
    };

    if (!$data) {
        return redirect()->back()->with('error', 'Type de rapport invalide.');
    }

    // Render HTML for DOMPDF
    $html = view('admin/rapports/pdf/' . $type, $data);

    // Generate PDF with DOMPDF
    $dompdf = new \Dompdf\Dompdf(['defaultFont' => 'DejaVu Sans']);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Log report generation
    model(AuditLogModel::class)->log('GENERATION_RAPPORT',
        $this->currentUser['user_id'],
        "Rapport: $type — Paramètres: " . json_encode($params)
    );

    // Stream as download
    $filename = "rapport_{$type}_" . date('Ymd') . ".pdf";
    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', "attachment; filename=\"$filename\"")
        ->setBody($dompdf->output());
}
```

---

## 8. JAVASCRIPT IMPLEMENTATION RULES

### 8.1 Code Standards

```javascript
// ✅ Always: const/let, arrow functions, async/await, template literals
const loadPresences = async (date) => {
    try {
        const data = await securePost('/admin/presences/data', { date });
        if (!data?.success) throw new Error(data?.message ?? 'Erreur inconnue');
        renderPresencesTable(data.presences);
    } catch (err) {
        showToast(err.message, 'danger');
    }
};

// ❌ Forbidden: var, function declarations in callbacks, jQuery, innerHTML with data
var x = 1;                              // forbidden
element.innerHTML = serverData.html;    // forbidden
$('#modal').show();                     // forbidden (no jQuery)
```

### 8.2 Global Utilities in `app.js`

`app.js` exposes these globals used across all pages:

```javascript
// Toast notification — type: 'success' | 'danger' | 'warning' | 'info'
window.showToast = (message, type = 'info', duration = 4000) => {
    const toastContainer = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 show`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
        </div>`;
    toastContainer.appendChild(toast);
    setTimeout(() => toast.remove(), duration);
};

// Secure AJAX POST with automatic CSRF token injection
window.securePost = async (url, body = {}) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const res   = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
};

// Confirm dialog (Bootstrap modal-based — not window.confirm)
window.confirmAction = (message, onConfirm) => {
    document.getElementById('confirm-modal-body').textContent = message;
    document.getElementById('confirm-modal-btn').onclick = onConfirm;
    new bootstrap.Modal(document.getElementById('confirm-modal')).show();
};
```

### 8.3 Notification Polling

In `app.js` — polls every 60 seconds on authenticated pages:

```javascript
const pollNotifications = async () => {
    try {
        const data = await fetch('/notifications/count').then(r => r.json());
        const badge = document.getElementById('notif-badge');
        if (data.count > 0) {
            badge.textContent = data.count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    } catch { /* silent fail */ }
};
setInterval(pollNotifications, 60_000);
```

---

## 9. BOOTSTRAP 5 AND DESIGN SYSTEM

### 9.1 Color Variables

`ocanada.css` must define and use these CSS custom properties:

```css
:root {
    /* Brand */
    --oc-primary:        #C41230;
    --oc-primary-dark:   #8B0D21;
    --oc-secondary:      #2D3748;
    --oc-secondary-light:#4A5568;
    --oc-accent:         #D97706;

    /* Semantic (override Bootstrap) */
    --bs-primary:        #C41230;
    --bs-primary-rgb:    196, 18, 48;

    /* Surface */
    --oc-bg:             #F7FAFC;
    --oc-card-bg:        #FFFFFF;
    --oc-border:         #E2E8F0;
    --oc-text-main:      #1A202C;
    --oc-text-muted:     #718096;

    /* Kiosk */
    --oc-kiosk-bg:       #1A365D;
    --oc-kiosk-accent:   #3182CE;
}
```

### 9.2 Layout Structure (Authenticated Pages)

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($title) ?> — Ô Canada RH</title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS (after Bootstrap) -->
    <link href="/assets/css/ocanada.css" rel="stylesheet">
</head>
<body class="d-flex" style="background-color: var(--oc-bg); font-family: 'Inter', sans-serif;">

    <!-- Sidebar (role-specific partial) -->
    <?= view('components/sidebar_' . $currentUser['role']) ?>

    <!-- Main wrapper -->
    <div class="main-wrapper flex-grow-1 d-flex flex-column" style="min-width: 0;">

        <!-- Topbar -->
        <?= view('components/topbar') ?>

        <!-- Page content -->
        <main class="content-area flex-grow-1 p-4">
            <?= $content ?>
        </main>

        <!-- Footer -->
        <footer class="app-footer text-muted small py-2 px-4 border-top">
            &copy; <?= date('Y') ?> Ô Canada — Tous droits réservés
        </footer>
    </div>

    <!-- Chatbot (all pages except kiosk) -->
    <?= view('components/chatbot') ?>

    <!-- Toast container (top-right) -->
    <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    <!-- Global confirm modal -->
    <!-- ... Bootstrap modal markup ... -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js (only on pages that need it) -->
    <?php if (!empty($needsCharts)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    <!-- App global JS -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
```

### 9.3 Component Patterns

**KPI Card:**
```html
<div class="col">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-3 p-3" style="background-color: rgba(196,18,48,0.1);">
                <i class="bi bi-people-fill fs-3" style="color: var(--oc-primary);"></i>
            </div>
            <div>
                <div class="fw-bold fs-2 lh-1">14</div>
                <div class="text-muted small">Présents aujourd'hui</div>
            </div>
        </div>
    </div>
</div>
```

**Data table:**
```html
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-table me-2"></i>Titre du tableau</h5>
        <div class="d-flex gap-2"><!-- filters, search, export buttons --></div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Colonne</th>
                        <!-- ... -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr class="<?= $row['statut'] === 'absent' ? 'table-danger' : '' ?>">
                        <td><?= esc($row['nom']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pagination -->
    <?= view('components/pagination', ['pager' => $pager]) ?>
</div>
```

### 9.4 Form Pattern

```php
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-plus-circle me-2"></i>Titre du formulaire</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/employes/creer">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nom de famille <span class="text-danger">*</span></label>
                    <input type="text"
                           name="nom"
                           class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                           value="<?= esc(old('nom')) ?>"
                           required>
                    <?php if (isset($errors['nom'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['nom']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="/admin/employes" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Enregistrer
                </button>
            </div>
        </form>
        <small class="text-muted">* Champ obligatoire</small>
    </div>
</div>
```

---

## 10. AUDIT LOG — MANDATORY COVERAGE

All 21 event types must be implemented. Audit calls happen in the relevant controller immediately after the DB operation succeeds.

```php
// AuditLogModel::log() signature
public function log(string $type, ?int $userId, $description = '', $before = null, $after = null): void
{
    $this->insert([
        'utilisateur_id' => $userId,
        'type_evenement' => $type,
        'description'    => is_string($description) ? $description : json_encode($description),
        'donnees_avant'  => $before ? json_encode($before) : null,
        'donnees_apres'  => $after  ? json_encode($after)  : null,
        'ip_adresse'     => service('request')->getIPAddress(),
        'date_evenement' => date('Y-m-d H:i:s'),
    ]);
}
```

Required log calls:

| Controller method | Event type | Includes before/after |
|---|---|---|
| `AuthController::loginProcess()` | `CONNEXION` or `ECHEC_CONNEXION` | No |
| `AuthController::logout()` | `DECONNEXION` | No |
| `EmployeesController::store()` | `CREATION_EMPLOYE` | After only |
| `EmployeesController::update()` | `MODIFICATION_EMPLOYE` | Yes (changed fields only) |
| `EmployeesController::deactivate()` | `DESACTIVATION_EMPLOYE` | Before status |
| `KiosqueController::pointArrivee/Depart()` | `POINTAGE` | No |
| `KiosqueController` (PIN fail) | `ECHEC_PIN_KIOSQUE` | No |
| `PresencesController::correct()` | `CORRECTION_PRESENCE` | Yes (full row) |
| `Employee\LeaveController::submit()` | `SOUMISSION_CONGE` | No |
| `Admin\LeaveController::approve/reject()` | `TRAITEMENT_CONGE` | Before status |
| `Admin\LeaveController::cancel()` | `ANNULATION_CONGE` | Before status |
| `Admin\LeaveController::updateBalance()` | `MODIF_SOLDE_CONGE` | Yes (jours fields) |
| `VisitorController::store()` | `ENREGISTREMENT_VISITEUR` | No |
| `VisitorController::checkout()` | `SORTIE_VISITEUR` | No |
| `DocumentsController::upload()` | `UPLOAD_DOCUMENT` | No |
| `DocumentsController::delete()` | `SUPPRESSION_DOCUMENT` | Before (filename, type) |
| `ReportsController::generate()` | `GENERATION_RAPPORT` | No |
| `ConfigController::update()` | `MODIF_CONFIG` | Yes (changed keys) |
| `ConfigController::updateHolidays()` | `MODIF_JOURS_FERIES` | No |
| `AuthFilter` / `RoleFilter` (denied) | `ACCES_NON_AUTORISE` | No |

`AuditLogModel` exposes **no** `update()`, `delete()`, `save()`, or `truncate()` methods.

---

## 11. ENVIRONMENT AND DEPLOYMENT

### 11.1 `.env` File Structure

```ini
# Runtime
CI_ENVIRONMENT = development  # MUST be 'production' on server

# Application
app.baseURL       = 'http://localhost:8080/'
app.encryptionKey = 'a-cryptographically-random-32-char-string'
app.sessionDriver = 'database'   # or 'files' — database recommended for production
app.sessionExpiration = 7200
app.cookieHTTPOnly    = true
app.cookieSecure      = false    # set to true in production (HTTPS)
app.cookieSameSite    = 'Lax'

# Database
database.default.hostname = localhost
database.default.database = ocanada_db
database.default.username = ocanada_user
database.default.password = change_this_in_production
database.default.DBDriver = MySQLi
database.default.charset  = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci
database.default.strictOn = true    # enables MySQL strict mode

# Logging
logger.threshold = 4   # production: only warnings and above
```

> **The Anthropic API key is NOT in `.env`.**  
> It is stored in the `config_systeme` DB table (`anthropic_api_key` key) and retrieved by `AnthropicClient` via `ConfigSystemeModel::get()`.

### 11.2 Apache Virtual Host

```apache
<VirtualHost *:80>
    ServerName ocanada.local
    DocumentRoot /var/www/ocanada/public

    <Directory /var/www/ocanada/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Deny direct access to everything outside public/
    <Directory /var/www/ocanada>
        Require all denied
    </Directory>

    # Required security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

### 11.3 Crontab Configuration

```bash
# On the production server, run as www-data
# Daily at 23:59 — mark absences + close unclosed visits
59 23 * * * www-data /usr/bin/php /var/www/ocanada/spark ocanada:mark-absences >> /var/log/ocanada-cron.log 2>&1
59 23 * * * www-data /usr/bin/php /var/www/ocanada/spark ocanada:close-visits  >> /var/log/ocanada-cron.log 2>&1

# Daily at 09:00 — alert on pending leaves > 48h
00  9 * * * www-data /usr/bin/php /var/www/ocanada/spark ocanada:pending-leaves >> /var/log/ocanada-cron.log 2>&1

# Weekly Monday 08:00 — alert on CDD contracts expiring in 30 days
00  8 * * 1 www-data /usr/bin/php /var/www/ocanada/spark ocanada:check-contracts >> /var/log/ocanada-cron.log 2>&1
```

### 11.4 Git Rules

```gitignore
# .gitignore — required entries
.env
vendor/
storage/uploads/
storage/logs/
*.sql
*.log
*.bak
.DS_Store
Thumbs.db
```

**Branches:**

| Branch | Purpose |
|---|---|
| `main` | Production-ready, deployable code only |
| `develop` | Integration branch (all phases merge here first) |
| `feature/phase-N-description` | One branch per development phase |

**Commit message format:**
```
[Phase N] Imperative verb + what changed

[Phase 0] Initialize CI4 project, configure database and run all 8 migrations
[Phase 1] Implement bcrypt login with 5-attempt lockout and session regeneration
[Phase 2] Add kiosk IP filter, PIN verification and time window enforcement
[Phase 3] Implement OHADA-compliant leave balance init and working day calculator
[Phase 5] Add in-app notification service with all 8 notification types
[Phase 7] Integrate Anthropic client with rate limiting for assistant and chatbot
```

---

## 12. NAMING CONVENTIONS — COMPLETE REFERENCE

| Element | Convention | Example |
|---|---|---|
| PHP controller classes | `PascalCase` + `Controller` suffix | `EmployeesController`, `KiosqueController` |
| PHP model classes | `PascalCase` + `Model` suffix | `PresenceModel`, `SoldeCongeModel` |
| PHP library classes | `PascalCase` (no suffix) | `WorkingDaysCalculator`, `AnthropicClient` |
| PHP method names | `camelCase` | `getActiveList()`, `initForEmployee()` |
| PHP variable names | `snake_case` | `$date_debut`, `$employe_id` |
| PHP constants | `UPPER_SNAKE_CASE` | `MAX_PIN_ATTEMPTS`, `DEFAULT_WORK_START` |
| PHP namespaces | `App\Controllers\Admin\` etc. | Follows directory structure exactly |
| View files | `snake_case.php` | `create_step1.php`, `historique.php` |
| View partials (components) | `snake_case.php` | `sidebar_admin.php`, `kpi_card.php` |
| JavaScript variables | `camelCase` | `dateDebut`, `csrfToken`, `employeId` |
| JavaScript functions | `camelCase` verb + noun | `updateClock()`, `loadPresences()`, `renderTable()` |
| CSS custom classes | `kebab-case` | `kpi-card`, `kiosque-clock`, `badge-visiteur-print` |
| Database tables | `snake_case`, plural | `demandes_conge`, `soldes_conges`, `jours_feries` |
| Database columns | `snake_case` | `heure_arrivee`, `minutes_retard`, `date_embauche` |
| Database ENUM values | `lowercase_underscored` | `'en_attente'`, `'correction_admin'`, `'actif'` |
| CI4 route URLs | `kebab-case` segments | `/admin/employes/creer`, `/temps-reel` |
| Employee matricule | `EMP-XXXX` (zero-padded) | `EMP-0001`, `EMP-0042` |
| Visitor badge number | `V[YYYYMMDD]-[NNN]` | `V20260309-001`, `V20260309-042` |
| File UUID names | 32 hex chars + extension | `a3f8b2c1d4e5f6a7b8c9d0e1f2a3b4c5.pdf` |
| Audit event codes | `UPPER_SNAKE_CASE` | `CONNEXION`, `CORRECTION_PRESENCE` |
| Notification codes | `NOTIF_` prefix + `UPPER_SNAKE` | `NOTIF_CONGE_SOUMIS`, `NOTIF_ABSENCE` |
| CRON command names | `ocanada:kebab-case` | `ocanada:mark-absences`, `ocanada:close-visits` |

---

## 13. QUICK REFERENCE — SYSTEM CONFIGURATION KEYS

All these keys must exist in `config_systeme` table after the initial seed:

| Key | Default | Type | Description |
|---|---|---|---|
| `ip_kiosque_autorisees` | `192.168.1.100` | CSV string | Authorized kiosk terminal IPs |
| `heure_debut_pointage_arrivee` | `06:00` | `HH:MM` | Clock-in window opens |
| `heure_fin_pointage_arrivee` | `10:30` | `HH:MM` | Clock-in window closes |
| `heure_debut_pointage_depart` | `15:00` | `HH:MM` | Clock-out window opens |
| `heure_fin_pointage_depart` | `21:00` | `HH:MM` | Clock-out window closes |
| `seuil_alerte_visiteur_heures` | `3` | Integer | Hours until visitor long-stay alert |
| `samedi_ouvrable` | `0` | `0` or `1` | Whether Saturday counts as working day |
| `shift_defaut_id` | `1` | Integer | FK to shifts_modeles used when no assignment exists |
| `departements_liste` | `Direction,Commercial,...` | CSV | Selectable departments for employee creation |
| `anthropic_api_key` | *(set by admin post-install)* | String | Anthropic API key — server-side only |

---

## 14. THE 15 RULES YOU MUST NEVER BREAK

Memorize these. Every one of them has caused real security incidents elsewhere.

1. **Never concatenate user input into SQL** — Query Builder or prepared statements, always.
2. **Never output user data without `esc()`** in PHP views.
3. **Never use `innerHTML`** to insert server-provided or AI-generated content in JavaScript.
4. **Never omit `<?= csrf_field() ?>`** from a POST form. No exceptions.
5. **Never serve `storage/` files via direct HTTP** — always through a PHP controller with auth check.
6. **Never return `salaire_journalier`** in queries or responses destined for non-admin contexts.
7. **Never send the Anthropic API key to the frontend** — it lives in the DB and stays in PHP.
8. **Never call the Anthropic API from JavaScript** — always via the PHP backend endpoint.
9. **Never write UPDATE or DELETE operations on `audit_log`** — it is append-only by design.
10. **Never use a client-supplied ID as the owner filter for personal data queries** — always use `$this->currentUser` from session.
11. **Never let the kiosk page load from an unauthorized IP** — the IP check is the only anti-fraud mechanism.
12. **Never duplicate working-day calculation logic** — `WorkingDaysCalculator` is the single source of truth.
13. **Never commit `.env`** or any file containing real credentials to git. Ever.
14. **Never disable the CSRF filter** on POST routes, even temporarily for debugging.
15. **Never use `eval()`, `Function()`, or `document.write()`** anywhere in the application.
