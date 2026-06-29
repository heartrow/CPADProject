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
CPADProject/
└── GreenStep/
    ├── public/                         # Slim4 entry point
    │   ├── index.php                   # API entry point
    │   └── .htaccess
    │
    ├── src/                            # Shared source (Vue + PHP)
    │   ├── api/
    │   │   └── client.js               # Axios instance
    │   │
    │   ├── assets/                     # Icons and images
    │   │
    │   ├── components/                 # Reusable Vue components
    │   │   ├── modals/
    │   │   │   ├── CreatePresetModal.vue
    │   │   │   ├── EnergyModal.vue
    │   │   │   ├── EventLoggerModal.vue
    │   │   │   ├── LeaderboardModal.vue
    │   │   │   ├── MealModal.vue
    │   │   │   ├── RecycleModal.vue
    │   │   │   └── TransportModal.vue
    │   │   ├── SideBar.vue
    │   │   └── TopBar.vue
    │   │
    │   ├── Auth/
    │   │   └── JwtService.php          # JWT issue & verify
    │   │
    │   ├── Controllers/                # Slim4 request handlers
    │   │   ├── AuthController.php
    │   │   ├── LogController.php
    │   │   ├── TemplateController.php
    │   │   └── TypeController.php
    │   │
    │   ├── Middlewares/                # Slim4 middleware
    │   │   ├── AuthMiddleware.php
    │   │   ├── Cors.php
    │   │   ├── JsonBodyParser.php
    │   │   ├── RateLimit.php
    │   │   └── SecurityHeaders.php
    │   │
    │   ├── Repositories/              # PDO database queries
    │   │   ├── LogRepository.php
    │   │   ├── TemplateRepository.php
    │   │   ├── TypeRepository.php
    │   │   └── UserRepository.php
    │   │
    │   ├── Validation/
    │   │   └── Validator.php          # Input validation
    │   │
    │   ├── router/
    │   │   └── index.js               # Vue Router
    │   │
    │   ├── stores/
    │   │   └── auth.js                # Pinia auth store
    │   │
    │   ├── Database.php               # PDO connection
    │   ├── Routes.php                 # Slim4 route definitions
    │   ├── App.vue                    # Vue root component
    │   ├── main.js                    # Vue entry point
    │   └── style.css                  # Global styles
    │
    ├── views/                         # Vue page views
    │   ├── ActivityView.vue
    │   ├── ChallengesView.vue
    │   ├── DashboardView.vue
    │   ├── LoginView.vue
    │   ├── ProfileView.vue
    │   └── RegisterView.vue
    │
    ├── mysql/                         # Database schema files
    │   ├── activityLog.sql
    │   ├── activityType.sql
    │   └── user.sql
    │
    ├── composer.json                  # PHP dependencies
    ├── package.json                   # Node dependencies
    ├── vite.config.js                 # Vite configuration
    └── index.html                     # Vue app entry HTML
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
Copy `.env.development` and fill in your credentials:
```
DB_HOST=
DB_PORT=
DB_NAME=
DB_USER=
DB_PASS=

JWT_SECRET=
JWT_TTL=3600
JWT_ISSUER=greenstep_api
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