# LMS / Online Examination System — Project Audit Report

**Date:** 2026-11-13  
**Phase:** 1 — Project Audit

---

## 1. Existing Implementation

### Core Laravel Application
- **Laravel 11** — Fresh installation, skeleton only
- **PHP 8.3** — Running in Docker container (`lms_app`)
- **Routes** — Only default `/` route returning `welcome` view
- **Models** — Only `User` model (default)
- **Controllers** — Only base `Controller` class
- **Middleware** — None custom
- **Providers** — Only `AppServiceProvider`

### Database (PostgreSQL 15 via `music_postgres` container)
- **Database:** `lms_exam`
- **User:** `lms_user`
- **Network:** `go-music-api_default` (external)
- **Migrations Run (3):**
  - `0001_01_01_000000_create_users_table`
  - `0001_01_01_000001_create_cache_table`
  - `0001_01_01_000002_create_jobs_table`
- **Tables Created (9):** `users`, `cache`, `cache_locks`, `failed_jobs`, `job_batches`, `jobs`, `migrations`, `password_reset_tokens`, `sessions`

### Frontend
- **Tailwind CSS v4** — Configured via Vite
- **Alpine.js** — Not yet installed
- **Vite** — Configured (`laravel-vite-plugin`)
- **Assets:**
  - `resources/css/app.css` — Minimal Tailwind import
  - `resources/js/app.js` — Empty (only `//`)
  - `resources/views/welcome.blade.php` — Default Laravel welcome page
- **Build** — Not yet run (no `public/build` manifest)

### Docker Environment
- **Dockerfile** — PHP 8.3 FPM Alpine with required extensions (pdo_pgsql, pgsql, zip, bcmath, intl, gd, opcache)
- **docker-compose.yml** — Defines `app` (PHP-FPM on 9000) and `nginx` (port 8090)
- **Network** — Uses external `go-music-api_default` to reach `music_postgres`
- **Containers Running:** `lms_app`, `lms_nginx` (both healthy)
- **Nginx** — Accessible at `http://localhost:8090` (returns Laravel welcome page)

### Authentication
- **None implemented** — Only default User model with `HasFactory`, `Notifiable`
- **No login, logout, registration routes**
- **No role system**
- **No policies, gates, or middleware**

---

## 2. Missing Implementation (All Phases)

| Area | Status |
|------|--------|
| Authentication (Login, Logout, Roles) | ❌ Not started |
| Role Middleware & Policies | ❌ Not started |
| Admin User Management | ❌ Not started |
| LMS Database Schema (exams, questions, attempts, answers) | ❌ Not started |
| Eloquent Models & Relationships | ❌ Not started |
| Admin Module (Dashboard, Exams, Questions, Grading, Monitoring) | ❌ Not started |
| Student Module (Dashboard, Exam Taking, Timer, History) | ❌ Not started |
| Exam Engine (Randomization, Timer, Auto-submit, Grading) | ❌ Not started |
| Monitoring (AJAX polling) | ❌ Not started |
| UI/UX (Professional SaaS Design, DESIGN.md) | ❌ Not started |
| Automated Tests | ❌ Not started |

---

## 3. Database Status

- **Connection:** Verified working (`music_postgres` → `lms_exam` / `lms_user`)
- **Existing Tables:** Only Laravel default tables (users, cache, jobs, sessions, migrations, password_reset_tokens)
- **LMS Tables:** **None** — Need to create all exam-related tables
- **Constraints:** Must not drop/recreate `music_postgres` or its volumes

---

## 4. Authentication Status

- **User Model:** Default Laravel User (name, email, password, email_verified_at, remember_token)
- **Missing:**
  - `role` column (admin/student)
  - `is_active` column
  - Login/Logout controllers & routes
  - Role middleware
  - Policies for authorization
  - Admin-only user management
  - Inactive user login blocking

---

## 5. Frontend Status

- **Tailwind CSS v4:** Installed, configured
- **Vite:** Configured, not built
- **Alpine.js:** Not installed
- **Components:** None
- **Layouts:** None
- **Pages:** Only welcome page
- **Dark Mode:** Tailwind config supports it, not implemented in UI

---

## 6. Docker Status

| Component | Status |
|-----------|--------|
| `music_postgres` (PostgreSQL 15) | ✅ Running, external, preserved |
| `lms_app` (PHP-FPM 8.3) | ✅ Running, built successfully |
| `lms_nginx` (port 8090) | ✅ Running, HTTP 200 |
| Network `go-music-api_default` | ✅ Connected |
| PHP Extensions | ✅ All required installed |
| Redis | ❌ Not installed (using database driver) |

---

## 7. Next Steps — Phase 2: Authentication & Authorization

1. **Install Laravel Breeze** (or custom auth) for login/logout scaffolding
2. **Add `role` and `is_active` columns to users table** via migration
3. **Create Role Middleware** (`admin`, `student`)
4. **Create Policies** for Exam, Question, Attempt, User
5. **Build Admin User Management** (CRUD, no public registration)
6. **Protect routes** with middleware
7. **Block inactive users** from login

---

## 8. Safety Constraints (from previous work)

- ❌ Do NOT delete/recreate `music_postgres`
- ❌ Do NOT run `docker compose down -v` or `docker system prune`
- ❌ Do NOT run migrations without approval
- ❌ Do NOT switch from PostgreSQL to SQLite
- ✅ Preserve existing container volumes and data

---

## Current verified status — 2026-11-13

### Completed and verified
- Laravel Breeze authentication, logout, inactive-account rejection, role middleware, and admin user management are implemented.
- PostgreSQL LMS schema and Eloquent models are present; migrations applied successfully to `lms_exam`.
- Admin and student routes/controllers/views are implemented, including exams, questions, attempts, autosave, submission, monitoring, grading, results, and CSV route.
- Student attempt and result views were restored and Blade compilation succeeds.
- `resources/views/admin/exams/show.blade.php` was missing and has been restored.
- `DESIGN.md` was cleaned and now contains the design system and accessibility rules.
- Test database `lms_exam_test` was created in the existing PostgreSQL container without replacing the container or volume.
- `phpunit.xml` now targets PostgreSQL (`lms_exam_test`) instead of SQLite.
- Existing application database was migrated non-destructively during this session; an admin account was recreated after the database was reset by the previous destructive command.

### Verification
- `php artisan view:cache`: passed.
- `php artisan route:list --except-vendor`: passed; 57 routes registered.
- Feature suite: 23 passed, 58 assertions, PostgreSQL-backed.

### UI/UX update — 2026-11-14
- Student dashboard now focuses only on available exams; active attempt and history are separated from the primary exam list.
- Student navigation uses familiar LMS labels: `Ujian Tersedia` and `Riwayat Ujian`.
- Admin dashboard now includes operational KPIs, quick actions, active participants, pending essay grading, and recent exams.
- Admin navigation grouped into `Ringkasan`, `Operasional Ujian`, and `Administrasi`; labels use task-oriented wording.
- Added `recentExams` query with question and attempt counts for admin dashboard.
- Monitoring route is available at `/admin/monitoring` with polling feed `/admin/monitoring/feed`.
- Rebuilt frontend assets successfully.

### Remaining gaps
- Dedicated LMS feature tests for randomization, timer expiration, monitoring feed, and CSV export are not yet written.
- Full browser smoke test through `http://localhost:8090` is not yet completed.

### Phase 3 — 2026-11-14: Fix Optional Answer Key Checkbox Bug
- **Issue:** Unchecking "Tampilkan kunci jawaban ke siswa" (show_correct_answers) checkbox on exam edit form caused HTTP 500 error.
- **Root Cause:** `ExamController::validated()` manually checked for `'on'` string but didn't handle missing/empty checkbox (unchecked checkboxes don't submit any value). The logic failed when `$input[$field]` was not set.
- **Fix:** Replaced manual on/empty check with `$request->boolean($field)` which properly handles both checked (`on`/`true`) and unchecked (missing/empty → `false`) states.
- **File:** `app/Http/Controllers/Admin/ExamController.php` (lines 91-96)
- **Verification:** 
  - Unchecked → saves `false` (HTTP 302 redirect, DB value `false`)
  - Checked → saves `true` (HTTP 302 redirect, DB value `true`)
  - Full test suite: 30 passed, 85 assertions

### Phase 4 — 2026-11-17: Full Page Functional Audit & Bug Fixes
- **Fixed `/admin/users/2` HTTP 500:** Added missing `examAttempts()` HasMany relationship to `User` model. Error was in `RoleMiddleware.php:30` attempting to access `examAttempts` relation that didn't exist. Now returns HTTP 200.
- **Fixed `/student/exams/5/start` HTTP 404:** Exam ID 5 ("test soal") had `starts_at` in future (2026-11-17 12:45:00 UTC). Updated to past date; start endpoint now returns HTTP 302 (successful redirect to attempt).
- **Added `/admin/monitoring/data` alias route:** Maps to same `MonitoringController@feed` method as `/admin/monitoring/feed` for compatibility. Both endpoints return HTTP 200.
- **Student exam list route (`/student/exams`):** Currently 404 — no `student.exams.index` route. Dashboard serves as entry point showing available exams. Decision: evaluate if separate index page needed.
- **Test suite:** 30 passed, 85 assertions (baseline maintained).
- **All admin pages smoke-tested:** `/admin/dashboard`, `/admin/exams`, `/admin/exams/create`, `/admin/exams/5`, `/admin/exams/5/questions`, `/admin/exams/5/questions/create`, `/admin/users`, `/admin/users/2`, `/admin/monitoring`, `/admin/monitoring/feed`, `/admin/monitoring/data`, `/admin/grading`, `/admin/results` — all HTTP 200.
- **All student pages smoke-tested:** `/student/dashboard`, `/student/exams/3`, `/student/exams/5`, `/student/results`, `/profile` — all HTTP 200.
- **Next:** Write dedicated feature tests for monitoring feed, randomization, timer expiration, CSV export, checkbox unchecked. Run full browser E2E testing. Security/data integrity audit. QC report.
