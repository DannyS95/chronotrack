# ⏱️ ChronoTrack

ChronoTrack tracks project work end-to-end: capture projects, break them into tasks, align tasks to goals, and log time against the plan. The scope is simple—ship a laser-focused slice that proves clean architecture can move fast without losing rigor.

---

## ⚡ Core Highlights

- **Project Ops Ready** – Create and list projects scoped to the authenticated user; ownership rules flow through every layer.
- **Task Execution** – Spin up tasks per project, view task backlogs, and lay the groundwork for richer statuses and editing.
- **Goal Alignment** – Goals stay project-scoped with a tight 1 goal → many tasks relationship so progress rolls up cleanly.
- **Timer Discipline** – Start, stop, and review timers per task to connect work logged with the plan in real time.
- **Consistency Built-In** – Transaction runner + locking strategy keep multi-write workflows safe and prevent duplicate persistence logic from creeping in.

Status overview:
- Projects: ✅ create, ✅ list, ☐ archive/delete
- Tasks: ✅ create, ✅ list, ☐ edit/delete, ☐ status workflow
- Timers: ✅ start, ✅ stop, ✅ list, ☐ guard against parallel timers
- Goals: ☐ create, ☐ list, ☐ attach tasks (1:N), ☐ mark complete, ☐ progress UI
  - Progress API now exposes goal completion % plus task list at `GET /projects/{project}/goals/{goal}/progress`
- Reports: ☐ aggregation, ☐ billing splits, ☐ exports
- User system: ✅ register/login/me/logout, ☐ full ownership enforcement

---

## 🧭 Architecture at a Glance

Clean Architecture drives the structure. Each layer owns its concerns and the flow is legible from HTTP to persistence.

```
/app
    /Domain         # contracts, entities, exceptions
    /Application    # DTOs, services, use cases
    /Infrastructure # Eloquent models, repositories, adapter layer + transaction runner
    /Interface      # HTTP controllers, requests
```

- DTO-first service layer contains the business logic.
- Infrastructure holds the adapters: querying philosophy, consistent repository structure, and the transaction runner that keeps multi-write operations safe while avoiding duplicate logic.
- Transaction locks and scoped repositories ensure ownership checks and write conflicts are handled deterministically.
- Repositories hide persistence details behind interfaces.
- Vertical slices connect route → request → service → repository → model without leaking framework shortcuts across layers.

---

## 🔬 Feature Flow: Goal ↔ Task Linking (WIP)

1. `POST /api/projects/{project}/goals` (coming soon) captures the goal definition under a project.
2. `POST /api/projects/{project}/goals/{goal}/tasks/{task}` links a task to a goal by setting `tasks.goal_id`.
3. Attach/Detach services enforce ownership through repository lookups, ensuring one user’s goal cannot claim another user’s task.
4. Read models surface goal summaries alongside tasks so future reporting can measure progress automatically.

📌 **Design note:** Tasks carry a `goal_id`. That simple 1:N keeps lineage clean today and leaves room for future goal-to-goal dependencies.

---

## 🛠 Tech Stack

- **Laravel** — Backend foundation with DDD-friendly patterns
- **Inertia.js** — Bridges Laravel responses to Vue screens
- **Vue 3** — Reactive UI components
- **Tailwind CSS** — Utility-first styling
- **Pest** — Fast, expressive testing

---

## 🧠 Build Philosophy

- Iterate with ownership: every module reflects a deliberate architectural choice.
- Keep the domain vocabulary explicit; DTOs and services mirror the language of the product.
- Deliver vertical slices that can be demoed quickly while still being testable end-to-end.
- Use ChronoTrack as the proving ground for future reporting and operations features.
- Lean on transaction-scoped services so a single failure never leaves partial data behind.

---

## 🚀 Quick Start

```bash
git clone https://github.com/DannyS95/chronotrack.git
cd chronotrack
make up
make install
make dev
```

### ⚠️ Docker Notes

`make dev` runs `php artisan serve` inside Docker. If the app isn’t reachable at `http://localhost:8000`:
- Restart Docker Desktop (common on WSL2).
- Or remap ports in `docker-compose.yml` to `8080:8000` and visit `http://localhost:8080`.

---

## 👤 Seeded Test User

```
Email:    daniel@chronotrack.com
Password: password123
```

---

## 📅 Current Focus

- **Now:** Goal creation, listing, and task linkage scoped by project + user.
- **Next:** Ownership enforcement everywhere, richer reports, and expanded auth flows.

---

## 🔭 Roadmap

### Goal Evolution
- Keep the 1:N goal→task relationship.
- Introduce goal-to-goal dependencies (depends on, subgoal, unlocks) when sequencing becomes necessary.
- Proposed schema: `goal_links(goal_id, linked_goal_id, type)`.

### Broader Backlog
- ☐ Real-time timers (WebSockets, ReactPHP)
- ☐ Advanced reports (per user, client, project)
- ☐ Notifications (deadline reminders, goal nudges)
- ☐ Role-based access for shared projects and teams


ChronoTrack keeps expanding. Every commit tightens the feedback loop between planning goals, executing tasks, and understanding where the time went.
