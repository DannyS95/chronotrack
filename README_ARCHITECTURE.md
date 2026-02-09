# ChronoTrack

ChronoTrack is a daily planning and time-tracking API built with Laravel using a Clean Architecture style.

Current product flow:
- Create a **daily goal** (summary of the day).
- Add **tasks** under that goal.
- Track time per task using timer profiles:
  - `pomodoro` (fixed 25 min)
  - `custom` (user-defined minutes)
  - `hourglass` (preset: 60, 90, 120, 240)

---

## API Shape (Current)

### Auth
- `POST /api/register`
- `POST /api/login`
- `GET /api/me`
- `POST /api/logout`

### Daily Goals
- `GET /api/daily-goals`
- `POST /api/daily-goals`
- `GET /api/daily-goals/{goal}`
- `GET /api/daily-goals/{goal}/progress`
- `POST /api/daily-goals/{goal}/complete`

### Tasks
- `GET /api/daily-goals/{goal}/tasks`
- `POST /api/daily-goals/{goal}/tasks`
- `GET /api/tasks/{task}`
- `PATCH /api/tasks/{task}`
- `DELETE /api/tasks/{task}`

### Timers
- `POST /api/tasks/{task}/timers/start`
- `POST /api/tasks/{task}/timers/pause`
- `POST /api/tasks/{task}/timers/stop`
- `GET /api/tasks/{task}/timers`
- `POST /api/timers/stop`
- `GET /api/timers/active`

---

## Architecture

```text
/app
  Domain
  Application
  Infrastructure
  Interface
```

### 1. Domain Layer
Holds product language and contracts:
- Repository interfaces (`*RepositoryInterface`)
- Domain exceptions (`ApiException`, timer conflicts, etc.)
- Value objects / snapshots (`TaskSnapshot`, `GoalSnapshot`)
- Shared timer utilities (`TimerDurations`)

### 2. Application Layer
Orchestrates use cases:
- Services (`CreateTaskService`, `CompleteGoalService`, `TimerService`, etc.)
- DTOs for input boundaries
- View models for output boundaries

Application services depend on domain contracts, not Eloquent models.

### 3. Infrastructure Layer
Implements persistence details:
- Eloquent models (`Project`, `Goal`, `Task`, `Timer`)
- Repository implementations
- Transaction adapter (`LaravelTransactionRunner`)

### 4. Interface Layer
HTTP adapter:
- Route definitions (`routes/api.php`)
- Controllers for request/response orchestration
- Form requests for validation
- Policies for authorization

---

## Repository Pattern + Eloquent Wiring

### Contracts
Defined in `app/Domain/*/Contracts`.

Examples:
- `TaskRepositoryInterface`
- `GoalRepositoryInterface`
- `TimerRepositoryInterface`
- `ProjectRepositoryInterface`

### Implementations
Bound in service providers:
- `app/Providers/TasksServiceProvider.php`
- `app/Providers/GoalsServiceProvider.php`
- `app/Providers/TimersServiceProvider.php`
- `app/Providers/ProjectsServiceProvider.php`
- `app/Providers/CommonServiceProvider.php` (transaction runner)

This gives constructor-injected interfaces in services while still using Eloquent underneath.

---

## Models and Relationships

Core models live under `app/Infrastructure/*/Eloquent/Models`.

- `Project` has many `Goal` and `Task`
- `Goal` belongs to `Project`, has many `Task`
- `Task` belongs to `Project`, optionally belongs to `Goal`, has many `Timer`
- `Timer` belongs to `Task`

Even though the public API is daily-goal-centric, a hidden per-user workspace project is resolved by:
- `app/Application/Projects/Services/WorkspaceProjectResolver.php`

That lets the code reuse existing project-scoped persistence cleanly while keeping UX simple.

---

## Filter System (Model-Driven)

Filtering is model-driven and centralized:

1. Each model defines a static `filters()` map.
2. `BaseModel::applyFilters()` translates query params into operators.
3. Repositories call `Model::applyFilters(...)`.

Relevant files:
- `app/Infrastructure/Shared/Persistence/Eloquent/Models/BaseModel.php`
- `app/Infrastructure/Tasks/Eloquent/Models/Task.php`
- `app/Infrastructure/Goals/Eloquent/Models/Goal.php`
- `app/Infrastructure/Timers/Eloquent/Models/Timer.php`

Supported operators include:
- `equals`
- `like`
- `after`
- `before`
- `date`
- `isnull`

This keeps query behavior consistent across resources.

---

## DTOs, Mappers, Responses

### DTOs (input boundaries)
Controllers convert validated request payloads into DTOs.

Examples:
- `CreateTaskDTO`
- `TaskFilterDTO`
- `CreateGoalDTO`
- `GoalFilterDTO`
- `TimerFilterDTO`

### Snapshots (domain read models)
Repositories and services map Eloquent models to immutable value objects.

Examples:
- `TaskSnapshot`
- `GoalSnapshot`

### View Models (response mappers)
View models convert snapshots/models to API response shape.

Examples:
- `TaskViewModel`
- `TaskCollectionViewModel`
- `GoalProgressViewModel`
- `TimerViewModel`

This separation keeps controllers thin and avoids leaking Eloquent internals into API contracts.

---

## Transactions and Consistency

### Transaction abstraction
- Contract: `app/Domain/Common/Contracts/TransactionRunner.php`
- Laravel adapter: `app/Infrastructure/Support/LaravelTransactionRunner.php`

Services wrap multi-step writes in transaction boundaries.

Examples:
- `TimerService` (start/pause/stop idempotent behavior)
- `UpdateTaskService` (status changes + timer stop + goal completion logic)
- `CompleteGoalService` (cascade task completion/timer stop)

### Concurrency protections
- Repository-level row locking (`lockForUpdate`)
- Active-timer uniqueness constraints in DB migrations
- Idempotent stop keys for timer stop endpoints

---

## Timer Profiles

Helper:
- `app/Domain/Tasks/Support/TaskTimerProfile.php`

Rules:
- `pomodoro`: fixed `25` minutes
- `custom`: requires `target_minutes` in `1..720`
- `hourglass`: requires one of `60, 90, 120, 240`

Stored on tasks:
- `timer_type`
- `target_duration_seconds`

Migration:
- `database/migrations/2026_02_08_000001_add_timer_profile_to_tasks_table.php`

---

## Request Lifecycle (Example)

`POST /api/daily-goals/{goal}/tasks`

1. Route resolves controller action.
2. Form request validates payload.
3. Controller resolves workspace + authorization.
4. Controller maps input to DTO.
5. Application service executes use case.
6. Repository persists via Eloquent.
7. Service returns snapshot/view model.
8. Controller returns normalized JSON response.

---

## Development

```bash
make up
make install
make dev
```

If running tests locally, ensure required PHP extensions and DB driver are installed (including XML/mbstring and sqlite/mysql driver depending on setup).
