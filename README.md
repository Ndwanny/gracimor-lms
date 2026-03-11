# Gracimor Loans Management System

> A full-stack collateral-backed microfinance platform built for Zambian MFIs.
> Handles the complete loan lifecycle from application through disbursement,
> repayment tracking, overdue management, and reporting.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Architecture](#2-architecture)
3. [Tech Stack](#3-tech-stack)
4. [Prerequisites](#4-prerequisites)
5. [Local Installation](#5-local-installation)
6. [Environment Variables](#6-environment-variables)
7. [Database Setup & Seeding](#7-database-setup--seeding)
8. [Running the Application](#8-running-the-application)
9. [Queue Workers & Scheduler](#9-queue-workers--scheduler)
10. [SMS Provider Setup](#10-sms-provider-setup)
11. [File Structure](#11-file-structure)
12. [API Authentication](#12-api-authentication)
13. [Roles & Permissions](#13-roles--permissions)
14. [Artisan Commands Reference](#14-artisan-commands-reference)
15. [Production Deployment](#15-production-deployment)
16. [Backup & Recovery](#16-backup--recovery)
17. [Troubleshooting](#17-troubleshooting)

---

## 1. Project Overview

Gracimor LMS is a Laravel-based REST API with a companion single-page frontend. It manages the full lifecycle of collateral-backed loans:

| Domain | What it covers |
|---|---|
| **Borrowers** | Registration, KYC verification, document upload, borrower profiles |
| **Loan Products** | Configurable products with rates, LTV limits, penalty rules, early settlement |
| **Collateral** | Vehicle and land asset registration, valuation, pledging, release |
| **Loans** | Application, approval (four-eyes), disbursement, repayment scheduling |
| **Payments** | Recording, allocation (penalty → interest → principal), reversal, receipts |
| **Penalties** | Automatic daily accrual after grace period, individual and bulk waiver |
| **Overdue** | Collections queue, contact logging, escalation to legal/external collectors |
| **Reports** | Daily portfolio digest (email), borrower statements, PAR reporting |
| **SMS** | Configurable templates, pre-due reminders, overdue notices, payment receipts |
| **Audit** | Full immutable audit trail on every write operation |

### Key Business Rules Enforced

- **Four-eyes principle** — the officer who applies for a loan cannot approve it
- **LTV enforcement** — principal capped at `max_ltv_percent` × collateral value
- **KYC gate** — borrower must be KYC-verified before a loan can be applied
- **One active loan per borrower** — prevents stacking of unrepaid loans
- **PAR 90 threshold** — write-off requires minimum 90 days overdue
- **Collateral uniqueness** — a pledged asset cannot be used on a second loan
- **SMS template validation** — template editor rejects unsupported `{variables}`
- **Zambian regulatory context** — NRC format, phone E.164 normalisation, BoZ licence display

---

## 2. Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                            │
│   Browser / Mobile App  →  Single-Page Application (HTML/JS)   │
└───────────────────────────────┬─────────────────────────────────┘
                                │ HTTPS
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                          WEB LAYER                              │
│            Nginx (reverse proxy, SSL termination)               │
└───────────────────────────────┬─────────────────────────────────┘
                                │ FastCGI / PHP-FPM
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      LARAVEL APPLICATION                        │
│                                                                 │
│  routes/api.php  →  Middleware (auth:sanctum, role)             │
│        │                                                        │
│        ▼                                                        │
│  Form Request Validation  →  Controller  →  Service Layer       │
│                                               │                 │
│                                    ┌──────────┴──────────┐      │
│                                    ▼                     ▼      │
│                              Eloquent ORM          Events &     │
│                              (15 models)           Listeners    │
│                                    │                     │      │
│                                    ▼                     ▼      │
│                             MySQL Database         Queue Jobs   │
│                                                         │       │
│                                                    ┌────┴────┐  │
│                                                    ▼         ▼  │
│                                            ReminderService  Mail│
│                                            (Africa's Talking)   │
└─────────────────────────────────────────────────────────────────┘
                                │
              ┌─────────────────┼──────────────────┐
              ▼                 ▼                  ▼
        Redis (cache      MySQL (primary      File Storage
         + queues)         database)          (S3 / local)
```

### Queue Architecture

```
Queues (processed by Supervisor workers):
  high       ← payment confirmation SMS, loan disbursed email
  default    ← all other jobs (overdue checks, penalties, delivery status)
  reports    ← daily portfolio report generation (low-priority)
  notifications ← email mailables (LoanApproved, LoanClosed, etc.)
```

### Scheduled Jobs (run by Laravel Scheduler via cron)

| Time (ZST) | Job | Description |
|---|---|---|
| 06:00 daily | `UpdateOverdueStatusesJob` | Mark loans/instalments overdue, auto-close cleared loans |
| 06:15 daily | `ApplyDailyPenaltiesJob` | Calculate and persist daily penalty accruals |
| 07:00 daily | `GenerateDailyPortfolioReportJob` | Email digest to CEO + managers |
| 07:30 Mon–Fri | `SendInstalmentRemindersJob` | Pre-due (7d, 3d, 1d) and overdue SMS reminders |
| 02:00 daily | `ReconcileLoanBalancesJob` | Rebuild loan_balances from payments + penalties |
| 00:30 daily | `PruneOldAuditLogsJob` | Archive audit logs older than 2 years |

---

## 3. Tech Stack

| Layer | Technology | Version |
|---|---|---|
| **Framework** | Laravel | 11.x |
| **Language** | PHP | 8.2+ |
| **Database** | MySQL | 8.0+ |
| **Cache / Queues** | Redis | 7.x |
| **Authentication** | Laravel Sanctum | 4.x |
| **SMS Provider** | Africa's Talking API | v1 |
| **Email** | Laravel Mail (SMTP / Mailgun) | — |
| **File Storage** | Laravel Filesystem (local / S3) | — |
| **Process Manager** | Supervisor | 4.x |
| **Web Server** | Nginx | 1.24+ |
| **PHP Process Manager** | PHP-FPM | 8.2 |
| **Dependency Manager** | Composer | 2.x |
| **Testing** | PHPUnit | 11.x |
| **OS (production)** | Ubuntu | 22.04 LTS |

---

## 4. Prerequisites

### Local Development

- PHP 8.2+ with extensions: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `json`, `tokenizer`, `xml`, `curl`, `gd`, `intl`
- Composer 2.x
- MySQL 8.0+ or MariaDB 10.6+
- Redis 7.x (or use `QUEUE_CONNECTION=database` for development)
- Node.js 20+ and npm (for frontend assets, if compiling)

#### Check PHP extensions

```bash
php -m | grep -E "pdo|mbstring|openssl|bcmath|json|tokenizer|xml|curl|gd|intl"
```

### Production

All of the above, plus:

- Ubuntu 22.04 LTS (recommended)
- Nginx 1.24+
- PHP-FPM 8.2
- Supervisor 4.x
- Certbot (for SSL via Let's Encrypt)
- A registered Africa's Talking account with a sender ID approved for Zambia

---

## 5. Local Installation

### Step 1 — Clone the repository

```bash
git clone https://github.com/your-org/gracimor-lms.git
cd gracimor-lms
```

### Step 2 — Install PHP dependencies

```bash
composer install
```

### Step 3 — Copy and configure environment file

```bash
cp .env.example .env
```

Then edit `.env` — see [Section 6](#6-environment-variables) for the full variable reference.

### Step 4 — Generate application key

```bash
php artisan key:generate
```

### Step 5 — Create the database

```sql
-- In MySQL:
CREATE DATABASE gracimor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gracimor'@'localhost' IDENTIFIED BY 'your-secure-password';
GRANT ALL PRIVILEGES ON gracimor.* TO 'gracimor'@'localhost';
FLUSH PRIVILEGES;
```

### Step 6 — Run migrations and seed

```bash
# Run all migrations
php artisan migrate

# Seed with full development dataset
php artisan db:seed

# Or seed only the minimum (users + products + SMS templates)
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=LoanProductSeeder
php artisan db:seed --class=SmsTemplateSeeder
```

### Step 7 — Create storage symlink

```bash
php artisan storage:link
```

### Step 8 — Start the development server

```bash
# Laravel built-in server
php artisan serve

# Or specify host/port
php artisan serve --host=0.0.0.0 --port=8080
```

### Step 9 — Start the queue worker (separate terminal)

```bash
php artisan queue:work --queue=high,default,notifications,reports
```

### Step 10 — Verify the installation

```bash
# Health check endpoint
curl http://localhost:8000/api/health

# Expected response
# {"status":"ok","database":"connected","redis":"connected","version":"1.0.0"}
```

---

## 6. Environment Variables

Copy `.env.example` to `.env` and fill in the values below.

### Application

| Variable | Example | Description |
|---|---|---|
| `APP_NAME` | `Gracimor LMS` | Application name (shown in emails) |
| `APP_ENV` | `local` | `local` \| `staging` \| `production` |
| `APP_KEY` | *(generated)* | 32-char base64 key — run `php artisan key:generate` |
| `APP_DEBUG` | `false` | **Always `false` in production** |
| `APP_URL` | `https://app.gracimor.co.zm` | Public URL, no trailing slash |
| `APP_TIMEZONE` | `Africa/Lusaka` | Server timezone — always set to Lusaka |

### Database

| Variable | Example | Description |
|---|---|---|
| `DB_CONNECTION` | `mysql` | Database driver |
| `DB_HOST` | `127.0.0.1` | Database host |
| `DB_PORT` | `3306` | Database port |
| `DB_DATABASE` | `gracimor` | Database name |
| `DB_USERNAME` | `gracimor` | Database user |
| `DB_PASSWORD` | `your-secure-password` | Database password |

### Cache & Queues

| Variable | Example | Description |
|---|---|---|
| `CACHE_DRIVER` | `redis` | `redis` recommended; `file` for dev |
| `QUEUE_CONNECTION` | `redis` | `redis` recommended; `database` for dev/testing |
| `REDIS_HOST` | `127.0.0.1` | Redis host |
| `REDIS_PORT` | `6379` | Redis port |
| `REDIS_PASSWORD` | `null` | Redis auth (set in production) |
| `SESSION_DRIVER` | `redis` | `redis` \| `database` \| `file` |

### Email (SMTP)

| Variable | Example | Description |
|---|---|---|
| `MAIL_MAILER` | `smtp` | `smtp` \| `mailgun` \| `log` (dev) |
| `MAIL_HOST` | `mail.gracimor.co.zm` | SMTP host |
| `MAIL_PORT` | `587` | SMTP port (587 for TLS, 465 for SSL) |
| `MAIL_USERNAME` | `noreply@gracimor.co.zm` | SMTP username |
| `MAIL_PASSWORD` | `your-mail-password` | SMTP password |
| `MAIL_ENCRYPTION` | `tls` | `tls` \| `ssl` |
| `MAIL_FROM_ADDRESS` | `noreply@gracimor.co.zm` | Sender address |
| `MAIL_FROM_NAME` | `Gracimor Loans` | Sender display name |

### SMS — Africa's Talking

| Variable | Example | Description |
|---|---|---|
| `AT_USERNAME` | `gracimor_prod` | Your AT account username (`sandbox` for testing) |
| `AT_API_KEY` | `atsk_xxxxxxxxxxxx` | AT API key from the dashboard |
| `AT_SENDER_ID` | `GRACIMOR` | Registered sender ID (max 11 chars) |
| `AT_SANDBOX` | `false` | Set `true` to route SMS to the AT sandbox |

### File Storage

| Variable | Example | Description |
|---|---|---|
| `FILESYSTEM_DISK` | `local` | `local` \| `s3` |
| `AWS_ACCESS_KEY_ID` | — | S3 access key (if using S3) |
| `AWS_SECRET_ACCESS_KEY` | — | S3 secret key |
| `AWS_DEFAULT_REGION` | `af-south-1` | S3 region |
| `AWS_BUCKET` | `gracimor-docs` | S3 bucket name |

### Gracimor Business Config

| Variable | Example | Description |
|---|---|---|
| `GRACIMOR_COMPANY_NAME` | `Gracimor Microfinance Ltd` | Full legal company name (used in emails/SMS) |
| `GRACIMOR_ADDRESS` | `Plot 12345, Cairo Road, Lusaka` | Physical address for email footers |
| `GRACIMOR_OFFICE_PHONE` | `+260211000001` | Main office phone |
| `GRACIMOR_EMAIL` | `info@gracimor.co.zm` | General enquiries email |
| `GRACIMOR_BOZ_LICENCE` | `MF/2019/001` | Bank of Zambia licence number |
| `GRACIMOR_TPIN` | `1000000000` | ZRA TPIN |
| `GRACIMOR_COLLATERAL_RELEASE_DAYS` | `7` | Working days to release collateral after closure |
| `DAILY_REPORT_RECIPIENTS` | `ceo@gracimor.co.zm,manager.banda@gracimor.co.zm` | Comma-separated email list for daily report |

### Sanctum (API Auth)

| Variable | Example | Description |
|---|---|---|
| `SANCTUM_STATEFUL_DOMAINS` | `localhost,app.gracimor.co.zm` | Domains allowed for stateful auth |

---

## 7. Database Setup & Seeding

### Migrations

The project has 21 migration files covering all tables in dependency order:

```bash
# Run all migrations
php artisan migrate

# Roll back last batch
php artisan migrate:rollback

# Fresh database (drops all tables, re-migrates)
php artisan migrate:fresh

# Fresh + seed
php artisan migrate:fresh --seed
```

### Migration order

| # | Table | Notes |
|---|---|---|
| 1 | `users` | Staff accounts |
| 2 | `personal_access_tokens` | Sanctum tokens |
| 3 | `loan_products` | Product catalogue |
| 4 | `borrowers` | Registered borrowers |
| 5 | `collateral_assets` | Vehicle and land assets |
| 6 | `loans` | Loan records |
| 7 | `loan_schedules` | Repayment schedule rows |
| 8 | `loan_balances` | Denormalised balance cache |
| 9 | `payments` | Payment records |
| 10 | `payment_allocations` | Payment split (principal/interest/penalty) |
| 11 | `penalties` | Daily penalty accrual records |
| 12 | `guarantors` | Loan guarantor links |
| 13 | `loan_documents` | Uploaded document metadata |
| 14 | `loan_status_histories` | Status transition audit trail |
| 15 | `contact_logs` | Overdue contact attempts |
| 16 | `loan_escalations` | Escalation records |
| 17 | `reminders` | SMS reminder send log |
| 18 | `sms_templates` | Configurable SMS template bodies |
| 19 | `audit_logs` | System-wide audit trail |
| 20 | `failed_jobs` | Laravel queue failure log |
| 21 | `jobs` | Laravel queue job table |

### Seeding environments

```bash
# Production — minimum safe seed (users + products + SMS templates only)
APP_ENV=production php artisan db:seed

# Development — full realistic dataset (50 borrowers, 80 loans, history)
APP_ENV=local php artisan db:seed

# Testing — deterministic fixture set for PHPUnit
APP_ENV=testing php artisan db:seed

# Individual seeders
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=LoanProductSeeder
php artisan db:seed --class=DevelopmentSeeder
php artisan db:seed --class=SmsTemplateSeeder
```

### Default seeded credentials (development only)

| Email | Password | Role |
|---|---|---|
| `superadmin@gracimor.co.zm` | `SuperAdmin1` | Superadmin |
| `ceo@gracimor.co.zm` | `Password1` | CEO |
| `manager.banda@gracimor.co.zm` | `Password1` | Manager |
| `manager.phiri@gracimor.co.zm` | `Password1` | Manager |
| `officer.tembo@gracimor.co.zm` | `Password1` | Loan Officer |
| `officer.daka@gracimor.co.zm` | `Password1` | Loan Officer |
| `accounts.zulu@gracimor.co.zm` | `Password1` | Accountant |

> **Never use these credentials in production.** Change all passwords immediately after first login.

---

## 8. Running the Application

### Development

```bash
# API server
php artisan serve

# Queue worker (keep running in separate terminal)
php artisan queue:work --queue=high,default,notifications,reports --tries=3

# Run scheduler manually (simulates cron ticks)
php artisan schedule:work

# Watch queue and schedule together (Laravel 11 helper)
php artisan queue:work &
php artisan schedule:work &
```

### Useful development commands

```bash
# Clear all caches
php artisan cache:clear && php artisan config:clear && php artisan route:clear

# Tail log
tail -f storage/logs/laravel.log

# Preview an SMS template against a real loan
php artisan sms:preview overdue_7_days 42

# List all available SMS templates
php artisan sms:preview --list

# Run the overdue job manually
php artisan app:update-overdue-statuses

# Run the daily penalty job manually
php artisan app:apply-daily-penalties

# Reconcile loan balances
php artisan app:reconcile-loan-balances
```

---

## 9. Queue Workers & Scheduler

### Cron entry (add to server crontab)

```bash
crontab -e
```

Add:

```cron
* * * * * cd /var/www/gracimor && php artisan schedule:run >> /dev/null 2>&1
```

This single cron entry hands off to Laravel's scheduler, which handles all job timing internally.

### Queue worker options

```bash
# Run all queues with retry logic
php artisan queue:work redis \
  --queue=high,default,notifications,reports \
  --tries=3 \
  --backoff=60 \
  --timeout=120 \
  --sleep=3

# Process one job and exit (useful for debugging)
php artisan queue:work --once

# Retry failed jobs
php artisan queue:retry all

# List failed jobs
php artisan queue:failed

# Flush all failed jobs
php artisan queue:flush
```

### Supervisor configuration (production)

Supervisor ensures the queue worker restarts automatically if it crashes. See the full Supervisor config in [Section 15](#15-production-deployment).

---

## 10. SMS Provider Setup

Gracimor uses [Africa's Talking](https://africastalking.com) for SMS delivery in Zambia.

### Step 1 — Create an Africa's Talking account

1. Sign up at [africastalking.com](https://africastalking.com)
2. Create a new application in the dashboard
3. Copy your **Username** and **API Key**

### Step 2 — Register a Sender ID

A sender ID replaces the numeric shortcode with your brand name (e.g. `GRACIMOR`).

1. In the AT dashboard go to **SMS → Sender IDs**
2. Submit a sender ID registration request for Zambia (MTN Zambia, Airtel Zambia, ZAMTEL)
3. Approval typically takes 3–10 business days
4. While awaiting approval, use the AT sandbox with `AT_SANDBOX=true`

### Step 3 — Configure .env

```dotenv
AT_USERNAME=gracimor_prod
AT_API_KEY=atsk_your_actual_api_key_here
AT_SENDER_ID=GRACIMOR
AT_SANDBOX=false
```

### Step 4 — Test the integration

```bash
# Preview a template with demo values (no SMS sent)
php artisan sms:preview payment_confirmation

# Preview against a real loan (no SMS sent)
php artisan sms:preview overdue_7_days 1

# Actually send the SMS to the borrower's phone (use with caution)
php artisan sms:preview overdue_7_days 1 --send
```

### Step 5 — Delivery status webhooks (optional)

Africa's Talking can POST delivery receipts to your server. Configure the callback URL in the AT dashboard:

```
https://app.gracimor.co.zm/api/webhooks/sms-delivery
```

Then create a webhook route in `routes/api.php`:

```php
Route::post('webhooks/sms-delivery', [WebhookController::class, 'smsDelivery'])
    ->withoutMiddleware(['auth:sanctum']);
```

### SMS Template Management

Templates can be viewed and edited by managers and above at:

```
GET  /api/sms-templates
GET  /api/sms-templates/{id}
PUT  /api/sms-templates/{id}
POST /api/sms-templates/{id}/preview
```

All 18 supported `{variables}` are documented in the template editor. The API rejects any template body containing an unsupported `{variable}`.

---

## 11. File Structure

```
gracimor-lms/
│
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   └── SmsPreviewCommand.php
│   │   └── Kernel.php                     # Scheduler config
│   │
│   ├── Events/
│   │   ├── LoanApplied.php
│   │   ├── LoanApproved.php
│   │   ├── LoanDisbursed.php
│   │   ├── LoanClosed.php
│   │   ├── LoanEscalated.php
│   │   ├── PaymentRecorded.php
│   │   ├── PaymentReversed.php
│   │   ├── PenaltyWaived.php
│   │   └── LoanOverdue.php
│   │
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── BorrowerController.php
│   │   │   ├── CollateralController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── GuarantorController.php
│   │   │   ├── LoanController.php
│   │   │   ├── LoanProductController.php
│   │   │   ├── OverdueController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── PenaltyController.php
│   │   │   ├── ReportController.php
│   │   │   ├── SmsTemplateController.php
│   │   │   └── UserController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   └── ForceJsonResponse.php
│   │   │
│   │   └── Requests/
│   │       ├── GracimorFormRequest.php    # Abstract base
│   │       ├── Borrower/
│   │       │   ├── StoreBorrowerRequest.php
│   │       │   ├── UpdateBorrowerRequest.php
│   │       │   └── UploadDocumentRequest.php
│   │       ├── Loan/
│   │       │   ├── StoreLoanRequest.php
│   │       │   ├── ApproveLoanRequest.php
│   │       │   ├── RejectLoanRequest.php
│   │       │   ├── DisburseLoanRequest.php
│   │       │   ├── EarlySettleLoanRequest.php
│   │       │   ├── WriteOffLoanRequest.php
│   │       │   ├── LoanCalculatorRequest.php
│   │       │   └── StoreGuarantorRequest.php
│   │       ├── Payment/
│   │       │   ├── StorePaymentRequest.php
│   │       │   └── ReversePaymentRequest.php
│   │       ├── Penalty/
│   │       │   ├── WaivePenaltyRequest.php
│   │       │   └── BulkWaivePenaltiesRequest.php
│   │       ├── Collateral/
│   │       │   └── StoreCollateralRequest.php
│   │       ├── Overdue/
│   │       │   ├── LogContactRequest.php
│   │       │   └── EscalateLoanRequest.php
│   │       ├── LoanProduct/
│   │       │   ├── StoreLoanProductRequest.php
│   │       │   └── UpdateLoanProductRequest.php
│   │       └── User/
│   │           ├── StoreUserRequest.php
│   │           ├── UpdateUserRequest.php
│   │           └── UpdateProfileRequest.php
│   │
│   ├── Jobs/
│   │   ├── UpdateOverdueStatusesJob.php
│   │   ├── ApplyDailyPenaltiesJob.php
│   │   ├── GenerateDailyPortfolioReportJob.php
│   │   ├── SendInstalmentRemindersJob.php
│   │   ├── ReconcileLoanBalancesJob.php
│   │   ├── SendEscalationNotice.php
│   │   └── CheckSmsDeliveryJob.php
│   │
│   ├── Listeners/
│   │   ├── SendLoanApprovedNotification.php
│   │   ├── SendLoanDisbursedNotification.php
│   │   ├── SendPaymentConfirmationSms.php
│   │   ├── SendEscalationAlertEmail.php
│   │   ├── LogLoanStatusChange.php
│   │   └── WriteAuditLog.php
│   │
│   ├── Mail/
│   │   ├── DailyPortfolioReport.php
│   │   ├── LoanEscalationAlert.php
│   │   ├── LoanApproved.php
│   │   ├── LoanDisbursed.php
│   │   ├── LoanClosed.php
│   │   └── PaymentConfirmation.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Borrower.php
│   │   ├── LoanProduct.php
│   │   ├── CollateralAsset.php
│   │   ├── Loan.php
│   │   ├── LoanSchedule.php
│   │   ├── LoanBalance.php
│   │   ├── Payment.php
│   │   ├── PaymentAllocation.php
│   │   ├── Penalty.php
│   │   ├── Guarantor.php
│   │   ├── LoanDocument.php
│   │   ├── LoanStatusHistory.php
│   │   ├── Reminder.php
│   │   └── SmsTemplate.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── EventServiceProvider.php
│   │
│   ├── Http/Resources/
│   │   ├── GracimorResource.php           # Abstract base
│   │   ├── BorrowerResource.php
│   │   ├── BorrowerCollection.php
│   │   ├── LoanResource.php
│   │   ├── LoanCollection.php
│   │   ├── LoanBalanceResource.php
│   │   ├── LoanScheduleResource.php
│   │   ├── LoanStatusHistoryResource.php
│   │   ├── PaymentResource.php
│   │   ├── PenaltyResource.php
│   │   ├── CollateralAssetResource.php
│   │   ├── GuarantorResource.php
│   │   ├── LoanProductResource.php
│   │   ├── UserResource.php
│   │   ├── AuditLogResource.php
│   │   └── ReminderResource.php
│   │
│   └── Services/
│       ├── BorrowerService.php
│       ├── CollateralService.php
│       ├── LoanService.php
│       ├── LoanCalculatorService.php
│       ├── PaymentService.php
│       ├── PenaltyService.php
│       ├── ReminderService.php
│       └── Sms/
│           └── AfricasTalkingDriver.php
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── BorrowerFactory.php
│   │   ├── LoanProductFactory.php
│   │   ├── CollateralAssetFactory.php
│   │   ├── LoanFactory.php
│   │   ├── LoanScheduleFactory.php
│   │   ├── LoanBalanceFactory.php
│   │   ├── PaymentFactory.php
│   │   └── PenaltyFactory.php
│   │
│   ├── migrations/
│   │   └── (21 migration files — see Section 7)
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── LoanProductSeeder.php
│       ├── SmsTemplateSeeder.php
│       ├── DevelopmentSeeder.php
│       └── TestingSeeder.php
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── email.blade.php
│       ├── components/email/
│       │   ├── action-button.blade.php
│       │   └── kpi-card.blade.php
│       └── emails/
│           ├── daily-portfolio-report.blade.php
│           ├── loan-escalation-alert.blade.php
│           ├── payment-confirmation.blade.php
│           ├── loan-disbursed.blade.php
│           ├── loan-approved.blade.php
│           └── loan-closed.blade.php
│
├── routes/
│   ├── api.php                            # All API routes
│   └── console.php
│
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── Borrowers/
│   │   ├── Loans/
│   │   ├── Payments/
│   │   ├── Penalties/
│   │   └── Reports/
│   └── Unit/
│       ├── LoanCalculatorTest.php
│       └── SmsTemplateTest.php
│
├── .env.example
├── .gitignore
├── artisan
├── composer.json
└── README.md
```

---

## 12. API Authentication

Gracimor uses Laravel Sanctum for token-based authentication.

### Login

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "officer.tembo@gracimor.co.zm",
  "password": "Password1"
}
```

**Response:**

```json
{
  "token": "1|abc123...",
  "user": {
    "id": 5,
    "name": "Kaputo Tembo",
    "role": "officer",
    "email": "officer.tembo@gracimor.co.zm"
  }
}
```

### Using the token

Include the token in the `Authorization` header on all subsequent requests:

```http
GET /api/loans
Authorization: Bearer 1|abc123...
Accept: application/json
```

### Logout

```http
POST /api/auth/logout
Authorization: Bearer 1|abc123...
```

### Token expiry

Tokens do not expire by default. You can configure expiry in `config/sanctum.php`:

```php
'expiration' => 60 * 24 * 7, // 7 days in minutes
```

### Error responses

| Code | Meaning |
|---|---|
| `401` | Unauthenticated — missing or invalid token |
| `403` | Forbidden — authenticated but insufficient role |
| `422` | Validation failed — see `errors` object in response |
| `404` | Resource not found |
| `500` | Server error — check `storage/logs/laravel.log` |

---

## 13. Roles & Permissions

Five roles control access throughout the system.

| Role | Code | Description |
|---|---|---|
| **Super Admin** | `superadmin` | Full system access including user management |
| **CEO** | `ceo` | All operational actions + write-off + bulk waiver |
| **Manager** | `manager` | Approvals, rejections, reversals, individual waiver |
| **Loan Officer** | `officer` | Data entry — borrowers, applications, payments, contact logs |
| **Accountant** | `accountant` | Read-only access to financials and reports |

### Permission matrix

| Action | Officer | Accountant | Manager | CEO | Superadmin |
|---|---|---|---|---|---|
| View borrowers | ✓ | ✓ | ✓ | ✓ | ✓ |
| Register borrower | ✓ | — | ✓ | ✓ | ✓ |
| KYC verification | — | — | ✓ | ✓ | ✓ |
| View loans | ✓ | ✓ | ✓ | ✓ | ✓ |
| Apply for loan | ✓ | — | ✓ | ✓ | ✓ |
| Approve loan | — | — | ✓ | ✓ | ✓ |
| Disburse loan | — | — | — | ✓ | ✓ |
| Record payment | ✓ | — | ✓ | ✓ | ✓ |
| Reverse payment | — | — | ✓ | ✓ | ✓ |
| Waive penalty (single) | — | — | ✓ | ✓ | ✓ |
| Waive penalty (bulk) | — | — | — | ✓ | ✓ |
| Write off loan | — | — | — | ✓ | ✓ |
| Escalate loan | — | — | ✓ | ✓ | ✓ |
| Log contact attempt | ✓ | — | ✓ | ✓ | ✓ |
| Manage loan products | — | — | — | ✓ | ✓ |
| Edit SMS templates | — | — | ✓ | ✓ | ✓ |
| View audit log | — | — | ✓ | ✓ | ✓ |
| Create users | — | — | ✓ | ✓ | ✓ |
| Manage user roles | — | — | — | — | ✓ |
| View reports | — | ✓ | ✓ | ✓ | ✓ |
| Export reports | — | ✓ | ✓ | ✓ | ✓ |

### Four-eyes principle

The officer who submits a loan application (`applied_by`) **cannot** be the same user who approves it (`approved_by`). This is enforced at the `ApproveLoanRequest` layer and cannot be bypassed via the API.

---

## 14. Artisan Commands Reference

### Built-in Laravel commands (commonly used)

```bash
# Migrations
php artisan migrate                        # Run pending migrations
php artisan migrate:fresh --seed           # Drop all, re-migrate, seed
php artisan migrate:rollback               # Roll back last batch
php artisan migrate:status                 # Show migration status

# Cache
php artisan cache:clear                    # Clear application cache
php artisan config:clear                   # Clear config cache
php artisan config:cache                   # Cache config for production
php artisan route:cache                    # Cache routes for production
php artisan view:cache                     # Cache Blade views

# Queues
php artisan queue:work                     # Start queue worker
php artisan queue:work --once              # Process one job
php artisan queue:failed                   # List failed jobs
php artisan queue:retry all               # Retry all failed jobs
php artisan queue:flush                    # Delete all failed jobs

# Scheduler
php artisan schedule:run                   # Run due scheduled jobs once
php artisan schedule:work                  # Run scheduler every minute (dev)
php artisan schedule:list                  # List all scheduled jobs

# Tinker (REPL)
php artisan tinker                         # Interactive PHP REPL
```

### Gracimor custom commands

```bash
# SMS template preview
php artisan sms:preview --list
# Lists all SMS templates in a table

php artisan sms:preview {trigger_key}
# Shows template with demo values — no SMS sent

php artisan sms:preview {trigger_key} {loan_id}
# Renders template against a real loan from the database

php artisan sms:preview {trigger_key} {loan_id} --send
# Actually sends the SMS to the borrower's phone (production-safe with confirmation prompt)
```

### Manual job execution (useful for maintenance)

```bash
# Mark overdue loans and instalments
php artisan app:update-overdue-statuses

# Apply daily penalties to all overdue loans
php artisan app:apply-daily-penalties

# Send instalment reminder SMS (normally runs via scheduler)
php artisan app:send-instalment-reminders

# Rebuild all loan_balances from payment + penalty records
php artisan app:reconcile-loan-balances

# Generate and email the daily portfolio report now
php artisan app:generate-portfolio-report
```

---

## 15. Production Deployment

This guide targets a fresh **Ubuntu 22.04 LTS** server.

### 15.1 — Server preparation

```bash
# Update packages
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server redis-server supervisor \
    software-properties-common curl git unzip

# Add PHP 8.2 PPA
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 and required extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-gd php8.2-intl php8.2-bcmath \
    php8.2-redis php8.2-zip php8.2-tokenizer

# Verify PHP
php -v

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 15.2 — MySQL setup

```bash
sudo mysql_secure_installation

sudo mysql -u root -p << 'EOF'
CREATE DATABASE gracimor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gracimor'@'localhost' IDENTIFIED BY 'CHANGE_THIS_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON gracimor.* TO 'gracimor'@'localhost';
FLUSH PRIVILEGES;
EOF
```

### 15.3 — Redis configuration

```bash
# Edit Redis config to require auth
sudo nano /etc/redis/redis.conf
# Find: # requirepass foobared
# Change to: requirepass YOUR_REDIS_PASSWORD

sudo systemctl restart redis
sudo systemctl enable redis
```

### 15.4 — Application deployment

```bash
# Create web root
sudo mkdir -p /var/www/gracimor
sudo chown $USER:www-data /var/www/gracimor

# Clone repository
git clone https://github.com/your-org/gracimor-lms.git /var/www/gracimor

cd /var/www/gracimor

# Install dependencies (no dev packages in production)
composer install --optimize-autoloader --no-dev

# Set permissions
sudo chown -R www-data:www-data /var/www/gracimor/storage
sudo chown -R www-data:www-data /var/www/gracimor/bootstrap/cache
sudo chmod -R 775 /var/www/gracimor/storage
sudo chmod -R 775 /var/www/gracimor/bootstrap/cache

# Configure environment
cp .env.example .env
nano .env
# Fill in all production values — see Section 6

# Generate application key
php artisan key:generate

# Cache configuration for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Seed production data (users + products + SMS templates only)
APP_ENV=production php artisan db:seed --force

# Create storage symlink
php artisan storage:link
```

### 15.5 — Nginx configuration

```bash
sudo nano /etc/nginx/sites-available/gracimor
```

```nginx
server {
    listen 80;
    server_name app.gracimor.co.zm;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name app.gracimor.co.zm;

    root /var/www/gracimor/public;
    index index.php;

    # SSL — configured by Certbot (see 15.6)
    ssl_certificate     /etc/letsencrypt/live/app.gracimor.co.zm/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/app.gracimor.co.zm/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options           "SAMEORIGIN"   always;
    add_header X-Content-Type-Options    "nosniff"      always;
    add_header X-XSS-Protection          "1; mode=block" always;
    add_header Referrer-Policy           "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Request size limit (for document uploads — 10MB)
    client_max_body_size 12M;

    # API routes
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass   unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include        fastcgi_params;
        fastcgi_read_timeout 120;
    }

    # Block sensitive files
    location ~ /\.(env|git|htaccess) {
        deny all;
        return 404;
    }

    # Catch-all
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/gracimor /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 15.6 — SSL with Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx

sudo certbot --nginx -d app.gracimor.co.zm

# Verify auto-renewal
sudo certbot renew --dry-run
```

### 15.7 — PHP-FPM tuning

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Adjust for your server's available RAM:

```ini
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 8
pm.max_requests = 500
```

```bash
sudo systemctl restart php8.2-fpm
```

### 15.8 — Supervisor (queue workers)

```bash
sudo nano /etc/supervisor/conf.d/gracimor-worker.conf
```

```ini
[program:gracimor-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gracimor/artisan queue:work redis
    --queue=high,default,notifications,reports
    --sleep=3
    --tries=3
    --backoff=60
    --timeout=120
    --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/gracimor/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=300
```

```bash
# Create log directory
sudo mkdir -p /var/log/gracimor
sudo chown www-data:www-data /var/log/gracimor

# Load and start
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start gracimor-worker:*

# Check status
sudo supervisorctl status
```

### 15.9 — Cron (scheduler)

```bash
sudo -u www-data crontab -e
```

Add:

```cron
* * * * * cd /var/www/gracimor && php artisan schedule:run >> /dev/null 2>&1
```

### 15.10 — Firewall

```bash
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw deny 3306    # Block direct MySQL access
sudo ufw deny 6379    # Block direct Redis access
sudo ufw enable
sudo ufw status
```

### 15.11 — Deployment script (for subsequent releases)

Save as `/var/www/gracimor/deploy.sh`:

```bash
#!/bin/bash
set -e

echo "→ Pulling latest code..."
git -C /var/www/gracimor pull origin main

echo "→ Installing dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

echo "→ Putting site into maintenance mode..."
php artisan down --retry=60

echo "→ Running migrations..."
php artisan migrate --force

echo "→ Clearing and rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "→ Restarting queue workers..."
php artisan queue:restart

echo "→ Bringing site back online..."
php artisan up

echo "✓ Deployment complete."
```

```bash
chmod +x /var/www/gracimor/deploy.sh
```

Run subsequent deployments with:

```bash
sudo -u www-data bash /var/www/gracimor/deploy.sh
```

---

## 16. Backup & Recovery

### Database backup

```bash
# Manual backup
mysqldump -u gracimor -p gracimor \
    --single-transaction \
    --routines \
    --triggers \
    | gzip > /backups/gracimor_$(date +%Y%m%d_%H%M%S).sql.gz

# Restore from backup
gunzip -c /backups/gracimor_20260226_070000.sql.gz \
    | mysql -u gracimor -p gracimor
```

### Automated daily backup (cron)

```bash
sudo crontab -e
```

```cron
0 2 * * * mysqldump -u gracimor -p'DB_PASSWORD' gracimor \
    --single-transaction | gzip \
    > /backups/gracimor_$(date +\%Y\%m\%d).sql.gz \
    && find /backups -name "*.sql.gz" -mtime +30 -delete
```

### Uploaded files backup

```bash
# If using local storage, sync uploads to offsite location
rsync -avz /var/www/gracimor/storage/app/public/ \
    backup-server:/backups/gracimor-uploads/
```

### Redis backup

Redis is used for cache and queues — its data is ephemeral and does not require backup. If the Redis instance is lost, queue jobs will need to be re-dispatched (non-critical; the scheduler will re-run jobs on the next scheduled tick).

---

## 17. Troubleshooting

### Application won't start

```bash
# Check Laravel log
tail -100 /var/www/gracimor/storage/logs/laravel.log

# Check PHP-FPM error log
sudo tail -50 /var/log/php8.2-fpm.log

# Check Nginx error log
sudo tail -50 /var/log/nginx/error.log

# Verify storage is writable
ls -la /var/www/gracimor/storage/
```

### 500 errors on all routes

Most likely a missing `.env`, uncached config, or wrong file permissions.

```bash
# Check .env exists and has APP_KEY set
head -5 /var/www/gracimor/.env

# Clear config cache (if config was cached with wrong values)
php artisan config:clear

# Fix permissions
sudo chown -R www-data:www-data /var/www/gracimor/storage
sudo chmod -R 775 /var/www/gracimor/storage
```

### 403 Forbidden on API calls

The user's role does not have access to the requested endpoint. Check the [Roles & Permissions matrix](#13-roles--permissions). Each controller method that restricts access will return:

```json
{
  "message": "This action is unauthorized."
}
```

### SMS not being sent

```bash
# 1. Check AT credentials are set
grep AT_ /var/www/gracimor/.env

# 2. Check if sandbox mode is accidentally on
grep AT_SANDBOX /var/www/gracimor/.env

# 3. Preview a template to verify AT connectivity
php artisan sms:preview payment_confirmation 1 --send

# 4. Check queue worker is running (SMS sends are queued)
sudo supervisorctl status

# 5. Check for failed jobs
php artisan queue:failed
```

### Emails not sending

```bash
# Test mail config
php artisan tinker
Mail::raw('Test email', fn($m) => $m->to('test@example.com')->subject('Test'));

# Check mail log (if MAIL_MAILER=log)
tail -50 /var/www/gracimor/storage/logs/laravel.log | grep -i mail
```

### Scheduled jobs not running

```bash
# Verify crontab is set
sudo -u www-data crontab -l

# Run the scheduler manually to see output
php artisan schedule:run --verbose

# List all scheduled jobs and next run times
php artisan schedule:list
```

### Queue jobs piling up / not processing

```bash
# Check if worker is running
sudo supervisorctl status gracimor-worker:*

# If stopped, restart
sudo supervisorctl start gracimor-worker:*

# Check queue depth
php artisan tinker
Queue::size('default');
Queue::size('high');

# Check for failed jobs
php artisan queue:failed
php artisan queue:retry all
```

### Loan balance looks incorrect

The `loan_balances` table is a denormalised cache. If a balance looks wrong after manual DB edits or a failed job:

```bash
# Rebuild all balances from source records
php artisan app:reconcile-loan-balances

# Or rebuild for a single loan via tinker
php artisan tinker
app(App\Jobs\ReconcileLoanBalancesJob::class)->reconcileSingle(Loan::find(42));
```

### Slow API responses

```bash
# Check slow query log
sudo tail -50 /var/log/mysql/slow-query.log

# Check missing indexes via Laravel Debugbar (dev only)
# Or profile a query in tinker:
php artisan tinker
DB::enableQueryLog();
// ... run your query ...
DB::getQueryLog();

# Ensure config/route/view caches are built
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Redis connection refused

```bash
# Check Redis is running
sudo systemctl status redis

# Test connection
redis-cli -a YOUR_REDIS_PASSWORD ping
# Expected: PONG

# Restart if needed
sudo systemctl restart redis
```

---

## Appendix — Component Summary

The table below lists every PHP file written for Gracimor LMS, the session it was built in, and its deliverable filename.

| Component Group | Classes / Files | Deliverable |
|---|---|---|
| Database Migrations | 21 migration files | `gracimor_migrations.php` |
| Eloquent Models | 15 model classes | `gracimor_models.php` |
| Service Classes | 7 service classes | `gracimor_services.php` |
| API Controllers | 12 controller classes | `gracimor_controllers.php` |
| API Routes | Full `api.php` | `gracimor_routes.php` |
| Scheduled Jobs | 5 job classes | `gracimor_jobs_events.php` |
| Events & Listeners | 9 events, 6 listeners | `gracimor_jobs_events.php` |
| Form Request Validation | 24 request classes | `gracimor_form_requests.php` |
| API Resource Classes | 13 resources, 2 collections | `gracimor_resources.php` |
| Email Blade Templates | 6 templates, 1 layout, 3 components, 4 Mailables | `gracimor_email_templates.php` |
| SMS Templates & Service | `ReminderService`, `AfricasTalkingDriver`, `SmsTemplate` model, seeder, controller, Artisan command | `gracimor_sms.php` |
| Factories & Seeders | 9 factories, 6 seeders | `gracimor_seeders_and_factories.php` |
| **This document** | README & deployment guide | `GRACIMOR_README.md` |

---

*Gracimor Loans Management System — built for Zambian microfinance.*
*Bank of Zambia compliant · Africa's Talking SMS integration · Laravel 11*
