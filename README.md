# HelpDesk (Mini Ticketing) – Laravel Layered / N-tier

A compact, production-minded HelpDesk/Ticketing MVP to practice a strict layered architecture in Laravel:

**Controller → UseCase/Service → Repository → DB**

> Goal: ship a “presentable” repo with clear structure, migrations/ERD, defined endpoints, tests (Feature + Unit), CI, and docs. :contentReference[oaicite:1]{index=1}

---

## Badges

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12%2B-FF2D20?logo=laravel&logoColor=white)
![REST API](https://img.shields.io/badge/API-REST-2F855A)
![Architecture](https://img.shields.io/badge/Architecture-Layered%20%2F%20N--tier-4A5568)

![Tests](https://img.shields.io/badge/tests-passing-brightgreen)
![CI](https://img.shields.io/github/actions/workflow/status/EzeArcich/HelpDesk_Tiny/ci.yml?label=CI&logo=github)
![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)

> 🔧 Reemplazá `<OWNER>/<REPO>` y el nombre del workflow `ci.yml` por el real.

---

## Features (MVP)

- **Tickets**
  - Create ticket (`subject`, `description`, `priority`)
  - View ticket details
  - List tickets with filters: `status`, `assignee`, `tag`, `q` (search)
  - Change status: `open → in_progress → closed`
- **Comments**
  - Add comment to ticket
  - `visibility`: `public | internal`
- **Assignment**
  - Assign a ticket to an agent (user)
- **Tags** (optional)
  - Many-to-many ticket ↔ tags
- **Audit trail**
  - Activity log for events like `status_changed`, `assigned`, `commented`, `tagged` :contentReference[oaicite:2]{index=2}

Out of scope: real-time, SLA, multi-tenant, full-text search, attachments (stretch goals later). :contentReference[oaicite:3]{index=3}

---

## Architecture (Layered / N-tier)

**Rules**
- Controllers: HTTP only (no business logic)
- Application: UseCases orchestrate flows + transactions
- Domain: entities/enums/contracts/rules (framework-agnostic)
- Infrastructure: Eloquent models + repository implementations
- **Never leak Eloquent Models upward**. If needed, use DTOs. :contentReference[oaicite:4]{index=4}

:contentReference[oaicite:5]{index=5}

---

## Database schema (tables)

- `users` (role: `customer|agent|admin`)
- `tickets` (requester_id, assignee_id, status, priority, …)
- `comments` (ticket_id, author_id, visibility)
- `tags`
- `ticket_tag` (pivot)
- `ticket_activities` (audit log + `meta` json) :contentReference[oaicite:6]{index=6}

---

## API

| Method | Path | Description |
|---|---|---|
| GET | `/api/tickets` | List + filters (`status`, `assignee`, `tag`, `q`) |
| POST | `/api/tickets` | Create ticket |
| GET | `/api/tickets/{id}` | Ticket details |
| POST | `/api/tickets/{id}/assign` | Assign ticket |
| POST | `/api/tickets/{id}/status` | Change status |
| POST | `/api/tickets/{id}/comments` | Add comment |
| POST | `/api/tickets/{id}/tags` | Tag ticket (optional) | :contentReference[oaicite:7]{index=7}

---

## Local setup

### Requirements
- PHP 8+
- Composer
- A DB (SQLite/MySQL/Postgres; configurable via `.env`)

### Install

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

(Optional) seed data if the project includes seeders:
php artisan db:seed

Run all tests:
php artisan test

