# ⏱️ ChronoTrack

ChronoTrack is a calm command center for your projects. Capture every idea, turn it into action, and understand where the time really goes—without needing to be a developer to follow along.

---

## 🌟 What You Can Do Today

- **Sign in once, stay secure.** Self-serve register/login endpoints hand back Sanctum tokens, while `me` and `logout` keep the session tidy.
- **Create projects that stay yours.** Ownership is enforced per user; projects can be completed or archived, and `GET /projects/{project}/summary` keeps the big picture close.
- **Plan tasks the way you work.** Add, update, filter, and delete tasks with due dates and descriptions, always returning timer-aware snapshots.
- **Connect tasks to goals.** Goals keep projects pointed in the right direction, and attaching/detaching active tasks keeps scope intentional.
- **Track time with intent.** Start, pause, resume, or stop timers safely—ChronoTrack prevents overlapping sessions and honours idempotent stop requests.
- **Review clean summaries.** Task, goal, and project summary payloads surface elapsed time in seconds and humanised strings for dashboards.

---

## 🔄 Everyday Flow

1. **Authenticate** – `POST /register` (once) then `POST /login` for a Sanctum token; hit `GET /me` to confirm the session.
2. **Create a project** – `POST /projects`
3. **Add tasks** – `POST /projects/{project}/tasks`
4. **Group tasks under goals** – `POST /projects/{project}/goals/{goal}/tasks/{task}` (attach) with only active tasks; remove when needed
5. **Start working** – `POST /tasks/{task}/timers/start`
6. **Pause or finish** – `POST /tasks/{task}/timers/pause` to pause, `POST /tasks/{task}/timers/stop` (or `POST /timers/stop` to stop whatever is running) then patch the task status to `done`
7. **Stay informed**
   - Task details: `GET /projects/{project}/tasks/{task}`
   - Goal progress: `GET /projects/{project}/goals/{goal}/progress`
   - Project summary: `GET /projects/{project}/summary`
8. **Wrap up the project** – `POST /projects/{project}/complete` when everything is finished or `DELETE /projects/{project}` to archive it.

Everything is authenticated with Laravel Sanctum, so the same token secures the entire loop.

---

## 🧱 Project → Goal → Task → Timer Hierarchy

- **Projects** are the top-level container. Every goal, task, and timer is scoped to a single project owner.
- **Goals** live under projects and represent the outcome you’re chasing. They only care about the tasks attached to them.
- **Tasks** always belong to a project and may optionally belong to a goal. A task can be attached or detached from a goal, but:
  - You can’t attach tasks to goals that are already complete.
  - Completed tasks can’t be attached to any goal—tasks must remain active to be linked.
- **Timers** belong to tasks. A task can have many timers (historical sessions plus the current run), but the service stops any active timer before marking the task `done` and prevents a user from running overlapping sessions on the same project or goal.

### Completion Rules

- Marking a task `done` automatically stops its active timer.
- When the last remaining task attached to a goal is marked `done`, the goal is automatically marked `complete`. You don’t have to call the goal completion endpoint manually—the domain service keeps goals and tasks in sync.
- Project status and progress metrics are recalculated after every task update, so projects immediately reflect the latest active/completed state of their goals and tasks.

Put simply: **project → goals → tasks → timers** form a strict cascade. Completing work at the task level ripples up to goals and projects, while timers ensure time tracking stays accurate at the leaf node.

---

## ⏲ Timer Safeguards

- **Single active session per user.** Starting a timer pauses any running timer on another project and blocks overlapping work on the same project or goal.
- **Database-backed locking.** Repositories issue `FOR UPDATE` queries and unique partial indexes to guard against race conditions when timers start, pause, or stop.
- **Idempotent stops.** `POST /tasks/{task}/timers/stop` and `POST /timers/stop` accept an `Idempotency-Key` header, caching the response so clients can safely retry without double-counting.

---

## 📊 Project Summary Endpoint

- **All-in-one snapshot.** `GET /projects/{project}/summary` returns project metadata, task and goal breakdowns, running timer counts, and time totals (seconds + humanised strings).
- **Timer-aware metrics.** Behind the scenes, `ProjectSummaryService` pulls `TaskSnapshot` and `GoalSnapshot` data so elapsed time reflects both active timers and stored historical totals.
- **Dashboard ready.** The response is designed for drop-in widgets: running timers, percent complete, active since, and total tracked time arrive precomputed.

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
| Auth      | Self-serve register/login, fetch the current user, and revoke tokens | `POST /register`, `POST /login`, `GET /me`, `POST /logout` |
| Projects  | Create, filter, show, summarise, complete, or archive projects scoped per user | `POST /projects`, `GET /projects`, `GET /projects/{project}`, `GET /projects/{project}/summary`, `POST /projects/{project}/complete`, `DELETE /projects/{project}` |
| Tasks     | Create, list, view, update, delete; responses always include timer-aware snapshots | `POST /projects/{project}/tasks`, `GET /projects/{project}/tasks`, `GET /projects/{project}/tasks/{task}`, `PATCH /projects/{project}/tasks/{task}`, `DELETE /projects/{project}/tasks/{task}` |
| Goals     | List, create, attach/detach active tasks, view progress, mark complete | `GET /projects/{project}/goals`, `POST /projects/{project}/goals`, `GET /projects/{project}/goals/{goal}`, `GET /projects/{project}/goals/{goal}/progress`, `POST /projects/{project}/goals/{goal}/tasks/{task}`, `DELETE /projects/{project}/goals/{goal}/tasks/{task}`, `POST /projects/{project}/goals/{goal}/complete` |
| Timers    | Start/pause/stop per task, list timers, or stop whichever timer is running with idempotency support | `POST /tasks/{task}/timers/start`, `POST /tasks/{task}/timers/pause`, `POST /tasks/{task}/timers/stop`, `GET /tasks/{task}/timers`, `POST /timers/stop` |

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
