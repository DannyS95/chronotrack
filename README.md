# ChronoTrack

ChronoTrack is now a focused daily planning tool:
- Create a **daily goal** (a one-line summary of your day).
- Add **tasks** under that goal.
- Run timers on tasks with explicit profiles:
  - `pomodoro` (fixed 25 minutes)
  - `custom` (any duration in minutes)
  - `hourglass` (preset only: 60, 90, 120, 240 minutes)

The project still uses Clean Architecture layering and keeps the existing timer safety behavior (single running timer per user, pause/resume/stop, idempotent stop support).

## API Overview

### Auth
- `POST /api/register`
- `POST /api/login`
- `GET /api/me`
- `POST /api/logout`

### Daily goals
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

## Daily Goal Payloads

### Create daily goal
`POST /api/daily-goals`

```json
{
  "summary": "Deep focus on onboarding flow",
  "goal_date": "2026-02-08",
  "description": "Ship first-pass onboarding UI"
}
```

### Create task for a daily goal
`POST /api/daily-goals/{goal}/tasks`

```json
{
  "title": "Build registration form",
  "timer_type": "hourglass",
  "target_minutes": 90,
  "description": "Add validation + happy-path submit"
}
```

## Task Timer Profiles

- `pomodoro`
  - Uses a fixed target of **25 minutes**.
  - `target_minutes` is optional and must be `25` if provided.
- `custom`
  - Requires `target_minutes` (`1..720`).
- `hourglass`
  - Requires `target_minutes` and it must be one of: `60`, `90`, `120`, `240`.

Tasks expose:
- `timer_type`
- `target_duration_seconds`
- `target_duration_human`
- `progress_percent` (tracked vs target)

## Local setup

```bash
git clone https://github.com/DannyS95/chronotrack.git
cd chronotrack
make up
make install
make dev
```

## Architecture

```
/app
  Domain
  Application
  Infrastructure
  Interface
```

The timer implementation is reused from the existing architecture and now powers the simpler daily-goal workflow.
