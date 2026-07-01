# 🌿 GreenStep

> A cross-platform carbon footprint tracker that helps users log, monitor, and reduce their daily carbon emissions.

Built as part of the **Bachelor in Software Engineering — Cross-Platform Application Development** programme at **Universiti Teknologi Malaysia (UTM)**.

---

## 👥 Team Members

| Name |
|---|
| Amirul Hakim bin Azahar |
| Muhammad Azri Arif bin Azhar |
| Tuan Muhammad Hasif bin Tuan Zaki |
| Ahmad Fikri Nabil bin Zamri |

---

## 🛠️ Tech Stack

### Frontend
| Technology | Purpose |
|---|---|
| Vue 3 | Frontend framework |
| Vite | Build tool and dev server |
| Pinia | State management |
| Axios | HTTP client for API calls |
| Capacitor | Cross-platform mobile support (iOS/Android) |
| Vue Router | Client-side routing |

### Backend
| Technology | Purpose |
|---|---|
| PHP Slim 4 | REST API framework |
| PDO | Database connection and queries |
| MySQL | Relational database |
| Firebase JWT | JSON Web Token authentication |
| Railway | Cloud deployment (API + Database) |

---

## ✨ Features

- 🔐 **Authentication** — Register, login and secure JWT-based auth
- 📋 **Activity Logging** — Log daily carbon-emitting activities across 4 categories
  - 🚗 Transport (car, bus, train, EV)
  - 🍽️ Meal (beef, poultry, fish, plant-based)
  - ⚡ Energy (AC, laptop, lighting, washing machine)
  - ♻️ Recycle (plastic, paper, glass, metal, e-waste)
- 🧮 **Server-side CO₂ Calculation** — CO₂ emissions calculated automatically on the backend based on activity type and amount
- 📊 **Daily CO₂ Summary** — View total CO₂ emitted today at a glance
- 📁 **One-Tap Templates** — Save frequently logged activities as reusable templates
- ✏️ **Edit & Delete Logs** — Full CRUD support for activity logs and templates
- 📱 **Mobile-First Design** — Responsive UI with bottom navigation for mobile devices
- 🔒 **Role-Based Access** — Admin-only endpoints for managing activity types
- 🛡️ **Security Middleware** — CORS, rate limiting, security headers, JSON body parsing

---

## 🗂️ Project Structure

```
GreenStep/
│
├── 📄 index.html                   # Vite HTML entry point
├── 📄 vite.config.js               # Vite configuration
├── 📄 jsconfig.json                # JS path aliases
├── 📄 package.json                 # Node dependencies & scripts
├── 📄 package-lock.json
├── 📄 composer.json                # PHP dependencies
├── 📄 composer.lock
├── 📄 Dockerfile                   # Docker build config for Railway
├── 📄 eslint.config.js             # ESLint rules
├── 📄 .prettierrc.json             # Prettier formatting rules
├── 📄 .oxlintrc.json               # OXLint config
├── 📄 .editorconfig                # Editor formatting standards
├── 📄 .env                         # Environment variables (local)
├── 📄 .env.development             # Environment variables (dev)
├── 📄 .env.example                 # Environment variable template
│
├── 📁 public/                      # PHP public entry point
│   ├── 📄 index.php                # Slim 4 app entry point
│   ├── 📄 .htaccess                # Apache URL rewrite rules
│   └── 📄 favicon.ico
│
├── 📁 mysql/                       # Database schema SQL files
│   ├── 📄 user.sql
│   ├── 📄 activityLog.sql
│   ├── 📄 activityType.sql
│   ├── 📄 badges.sql
│   ├── 📄 challenges.sql
│   ├── 📄 userChallenges.sql
│   └── 📄 ecoTips.sql
│
├── 📁 src/                         # Application source code
│   │
│   ├── 📄 main.js                  # Vue app bootstrap
│   ├── 📄 App.vue                  # Root Vue component
│   ├── 📄 style.css                # Global styles & CSS variables
│   │
│   ├── 📁 api/
│   │   └── 📄 client.js            # Axios API client (base URL, interceptors)
│   │
│   ├── 📁 router/
│   │   └── 📄 index.js             # Vue Router route definitions
│   │
│   ├── 📁 stores/
│   │   └── 📄 auth.js              # Pinia auth store (JWT state)
│   │
│   ├── 📁 assets/                  # Static images & SVG icons
│   │   ├── 📄 activity-icon-black.png
│   │   ├── 📄 activity-icon-white.png
│   │   ├── 📄 badges-icon-black.png
│   │   ├── 📄 badges-icon-white.png
│   │   ├── 📄 challenges-icon-black.png
│   │   ├── 📄 challenges-icon-white.png
│   │   ├── 📄 dashboard-icon-black.png
│   │   ├── 📄 dashboard-icon-white.png
│   │   ├── 📄 profile-icon-black.png
│   │   ├── 📄 profile-icon-white.png
│   │   ├── 📄 award.svg
│   │   ├── 📄 trophy.svg
│   │   ├── 📄 layout-dashboard.svg
│   │   ├── 📄 notebook-pen.svg
│   │   ├── 📄 user-round.svg
│   │   ├── 📄 user-check.svg
│   │   └── 📄 user-check.png
│   │
│   ├── 📁 components/              # Reusable Vue components
│   │   ├── 📄 TopBar.vue           # Top navigation bar
│   │   ├── 📄 SideBar.vue          # Side navigation menu
│   │   └── 📁 modals/              # Modal dialog components
│   │       ├── 📄 EventLoggerModal.vue     # Activity category picker
│   │       ├── 📄 TransportModal.vue       # Log transport activity
│   │       ├── 📄 MealModal.vue            # Log meal activity
│   │       ├── 📄 EnergyModal.vue          # Log energy activity
│   │       ├── 📄 RecycleModal.vue         # Log recycling activity
│   │       ├── 📄 CreatePresetModal.vue    # Create/edit activity preset
│   │       ├── 📄 CreateChallengeModal.vue # Admin: create challenge
│   │       └── 📄 LeaderboardModal.vue     # Challenge leaderboard
│   │
│   ├── 📁 Auth/                    # PHP authentication
│   │   └── 📄 JwtService.php       # JWT encode/decode/verify
│   │
│   ├── 📁 Controllers/             # PHP Slim 4 route controllers
│   │   ├── 📄 AuthController.php   # Register, login, logout
│   │   ├── 📄 LogController.php    # Activity log CRUD
│   │   ├── 📄 TypeController.php   # Activity type CRUD
│   │   ├── 📄 BadgeController.php  # Badge management & awarding
│   │   ├── 📄 ChallengeController.php  # Challenge management
│   │   ├── 📄 TemplateController.php   # User preset templates
│   │   └── 📄 EcoTipController.php     # Eco tips feed
│   │
│   ├── 📁 Repositories/            # PHP data access layer (PDO)
│   │   ├── 📄 UserRepository.php
│   │   ├── 📄 LogRepository.php
│   │   ├── 📄 TypeRepository.php
│   │   ├── 📄 BadgeRepository.php
│   │   ├── 📄 ChallengeRepository.php
│   │   ├── 📄 TemplateRepository.php
│   │   └── 📄 EcoTipsRepository.php
│   │
│   ├── 📁 Middlewares/             # Slim 4 middleware stack
│   │   ├── 📄 AuthMiddleware.php   # JWT authentication guard
│   │   ├── 📄 Cors.php             # CORS headers
│   │   ├── 📄 JsonBodyParser.php   # Parse JSON request body
│   │   ├── 📄 RateLimit.php        # Request rate limiting
│   │   └── 📄 SecurityHeaders.php  # HTTP security headers
│   │
│   ├── 📁 Validation/
│   │   └── 📄 Validator.php        # Input validation helper
│   │
│   ├── 📄 Database.php             # PDO database connection singleton
│   └── 📄 Routes.php               # Slim 4 route definitions
│
└── 📁 views/                       # Vue page-level views
    ├── 📄 LoginView.vue             # Login page
    ├── 📄 RegisterView.vue          # Registration page
    ├── 📄 DashboardView.vue         # Main dashboard
    ├── 📄 ActivityView.vue          # Activity logging & history
    ├── 📄 ChallengesView.vue        # Challenges & leaderboard
    ├── 📄 ProfileView.vue           # User profile & settings
    └── 📄 AdminView.vue             # Admin configuration panel
```

---

## 🗄️ Database Schema

| Table | Description |
|---|---|
| `users` | User accounts with roles (member / admin) |
| `activity_types` | Predefined activity types with CO₂ emission factors |
| `activity_logs` | User activity logs with server-calculated CO₂ emissions |
| `user_templates` | User-saved one-tap templates for quick logging |

---

## 🚀 Getting Started

### Prerequisites
- Node.js >= 18
- PHP >= 8.1
- Composer
- MySQL

### Setup
```bash
cd GreenStep
npm install
composer install
```

### Database Setup
```bash
mysql -u root -p your_database < mysql/user.sql
mysql -u root -p your_database < mysql/activityType.sql
mysql -u root -p your_database < mysql/activityLog.sql
```

### Environment Variables
Copy `.env.example` to `.env` and fill in your credentials:

```env
# Database
DB_HOST=
DB_PORT=
DB_NAME=
DB_USER=
DB_PASS=
DB_CHARSET=

# App
APP_DEBUG=

# JWT
JWT_SECRET=
JWT_TTL=3600
JWT_ISSUER=greenstep_api

# Rate Limiting
LOGIN_RATE_LIMIT=5
LOGIN_WINDOW_SECONDS=60

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://localhost:8000
```

For the frontend, copy into `.env.development` and set:

```env
VITE_API_BASE_URL=http://localhost:8000
```

### Run Development Servers
```bash
# Backend (PHP Slim4)
php -S localhost:8000 -t public

# Frontend (Vue + Vite)
npm run dev
```

### Build for Production
```bash
npm run build
```

---

## 📱 Mobile Build (Capacitor)

```bash
npm run build
npx cap sync
npx cap open android   # or ios
```

---

## 📖 API Documentation

See [API.md](./API.md) for the full list of endpoints, request/response examples, and validation rules.

---

## 📄 License

This project is developed for academic purposes at Universiti Teknologi Malaysia.