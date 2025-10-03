# ⏱️ ChronoTrack

ChronoTrack is a calm command center for your projects. Capture every idea, turn it into action, and understand where the time really goes—without needing to be a developer to follow along.

---

## 🌟 What You Can Do Today

- **Create projects that stay yours.** Everything is scoped to the signed-in user so personal and client work never mix.
- **Plan tasks the way you work.** Add tasks, give them due dates and descriptions, and see their latest activity at a glance.
- **Connect tasks to goals.** Goals keep projects pointed in the right direction, and progress snapshots show how close you are to done.
- **Track time with intent.** Start and stop timers directly on tasks; ChronoTrack keeps the math straight and guards against stray overlaps.
- **Review clean summaries.** Task and goal APIs return friendly payloads with elapsed time (both in seconds and humanised for dashboards).

---

## 🔄 Everyday Flow

1. **Create a project** – `POST /projects`
2. **Add tasks** – `POST /projects/{project}/tasks`
3. **Group tasks under goals** – `POST /projects/{project}/goals/{goal}/tasks/{task}` (attach) and remove when needed
4. **Start working** – `POST /tasks/{task}/timers/start`
5. **Pause or finish** – `POST /tasks/{task}/timers/stop` then patch the task status to `done`
6. **Stay informed**
   - Task details: `GET /projects/{project}/tasks/{task}`
   - Goal progress: `GET /projects/{project}/goals/{goal}/progress`

Everything is authenticated with Laravel Sanctum, so the same token secures the entire loop.

---

## 🧭 Architecture in Plain Language

ChronoTrack follows Clean Architecture to keep business rules clear and testable:

```
/app
  Domain         → language of the product (contracts, exceptions, value objects)
  Application    → use-case services powered by DTOs, snapshots, and view models
  Infrastructure → Eloquent models, repositories, transaction runner
  Interface      → HTTP controllers & requests
```

- **Domain first:** ownership rules, timer math, and goal progress calculations live beside expressive value objects.
- **Application services:** orchestrate one vertical slice at a time—create a task, stop a timer, fetch goal progress—while packaging responses through DTOs, snapshots (task, goal) and view models that keep API output friendly and consistent.
- **Infrastructure adapters:** Eloquent models, repositories, and a transaction runner that wraps multi-step writes in safe database transactions with locking.
- **Interface layer:** Thin controllers that translate HTTP requests/responses while the heavy lifting stays in the core.

This separation means you can swap interfaces (CLI, web, mobile) without rewriting the rules that keep data consistent.

Common building blocks you will spot:
- `TaskSnapshot`, `GoalSnapshot`, and friends for immutable read models.
- View models such as `GoalProgressViewModel` that convert snapshots into API payloads.
- The shared `TimerDurations` utility that normalises timer calculations across services.
- `LaravelTransactionRunner`, our transaction runner wrapper, used by services to keep multi-step changes atomic.

---

## 🧩 Feature Details & Endpoints

| Area      | Highlights | Key Endpoints |
|-----------|------------|---------------|
| Projects  | Create and list projects scoped per user | `POST /projects`, `GET /projects` |
| Tasks     | Create, list, view, update, delete; return timer-aware snapshots | `POST /projects/{project}/tasks`, `GET /projects/{project}/tasks`, `GET /projects/{project}/tasks/{task}`, `PATCH /projects/{project}/tasks/{task}`, `DELETE ...` |
| Goals     | List, create, attach/detach tasks, view progress | `GET /projects/{project}/goals`, `POST ...`, `POST /projects/{project}/goals/{goal}/tasks/{task}`, `GET .../progress` |
| Timers    | Start/stop per task, list user-active timers with project context | `POST /tasks/{task}/timers/start`, `POST /tasks/{task}/timers/stop`, `GET /tasks/{task}/timers`, `GET /timers/active` |

Under the hood, timers are recalculated safely thanks to shared helper utilities (`TimerDurations`) that aggregate historical durations plus live running time.

---

## 🛠️ Tech Stack

- **Laravel 11** with Sanctum for auth and Pest for testing
- **Vue 3 + Inertia.js + Tailwind CSS** for the interactive front-end (coming online as the API firms up)
- **Docker** for reproducible local setups (`make up`, `make dev`)

---

## 🚀 Getting Started

```bash
git clone https://github.com/DannyS95/chronotrack.git
cd chronotrack
make up          # boot the Docker stack
make install     # composer install + npm install + artisan key generation
make dev         # start the dev stack (API on http://localhost:8000)
```

If the server isn’t reachable:
- Restart Docker Desktop (WSL2 often needs it)
- Or change the port mapping in `docker-compose.yml` (e.g. `8080:8000`) and visit `http://localhost:8080`

### Try It Instantly

```
Email:    daniel@chronotrack.com
Password: password123
```

---

## 🧭 Product Principles

- **Clarity over clutter.** Interfaces and payloads should make sense to non-engineers without hiding detail from developers.
- **Ownership everywhere.** Every repository double-checks that a user actually owns what they are touching.
- **Transactions by default.** Multi-step writes (start/stop timers, mark tasks complete) live inside database transactions with row locks.
- **Composable slices.** Each feature adds a vertical slice from HTTP → domain → persistence so future work builds on solid paths.

---

## 🗺️ Roadmap

### Short Term
- Goal creation and listing in the UI
- Real-time timer heads-up (WebSockets or polling)
- Project-level reports showing total logged time, late tasks, and goal health

### Medium Term
- Shared projects with role-based permissions
- Automatic reminders (e.g. “timer still running?”, “goal deadline tomorrow”)
- Export-ready reports (CSV/PDF) for billing and retrospectives

### Long Term
- Goal dependencies (unlock, depends on, subgoal)
- Advanced analytics (burn-up charts, team load balancing)
- Integrations with calendar, Slack, and invoicing tools

---

ChronoTrack evolves with each iteration. Whether you’re here to log your personal projects or to extend the platform, the codebase is ready to welcome you—one clean slice at a time.
