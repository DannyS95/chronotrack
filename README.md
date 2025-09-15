# ⏱️ ChronoTrack

ChronoTrack is a **project-focused task scheduler + time tracker** built with Laravel, Inertia.js (Vue), and Clean Architecture principles.

---

## 📦 Tech Stack

- **Laravel** — Backend framework (Clean Architecture, DDD patterns)  
- **Inertia.js** — UI glue between Laravel and Vue  
- **Vue 3** — Reactive frontend components  
- **Tailwind CSS** — Utility-first styling  
- **Pest** — Developer-friendly testing framework  

---

## 🧩 Architecture

Organized by Clean Architecture principles:
```
/app
    /Domain         # contracts, entities, exceptions
    /Application    # DTOs, services, use cases
    /Infrastructure # Eloquent models, repositories
    /Interface      # HTTP controllers, requests
```

- Clear separation of concerns  
- Service layer driven by DTOs  
- Repository interfaces decouple persistence from domain logic  

---

## ✅ Core Features

### 🗂️ Projects
- [x] Create project  
- [x] View all projects  
- [ ] Archive/delete project  

### ✅ Tasks
- [x] Create task (scoped to project)  
- [x] View tasks per project  
- [ ] Edit/delete task  
- [ ] Task status (To Do, In Progress, Done)  

### ⏱️ Timers
- [x] Start timer on task  
- [x] Stop timer  
- [x] View timers per task  
- [ ] Prevent multiple active timers per user  

### 🎯 Goals
- [ ] Create goal (scoped to project)  
- [ ] List goals per project  
- [ ] Attach tasks to goals (via pivot)  
- [ ] Mark goal complete  
- [ ] Show goal progress  

### 📊 Reports
- [ ] Aggregate timers by project  
- [ ] Time per day/week/month  
- [ ] Billable vs non-billable hours  
- [ ] Export CSV/JSON  

### 👤 User System
- [x] Register user  
- [x] Login user  
- [x] Authenticated user info (`/me`)  
- [x] Logout  
- [ ] Restrict tasks/goals/timers per user (ownership rules)  


---

## 🚧 Status

- Current focus: **Goals** (project-scoped create + list).  
- Next: goal–task linkage, reporting services, and user auth.  

---

## 🧠 Philosophy

ChronoTrack is a playground for:  
- Practicing Clean Architecture in Laravel  
- Modeling domains with DDD  
- Building vertical slices (routes → requests → DTOs → services → repos → models)  
- Exploring time-tracking and reporting design  

---

## 🔧 Setup

```bash
git clone https://github.com/DannyS95/chronotrack.git
cd chronotrack
make up
make install
make dev
```

### ⚠️ Note on Docker

This app uses `artisan serve` inside Docker (`make dev`).  
Networking quirks may appear depending on Docker Desktop / WSL2 setup.  
If `http://localhost:8000` doesn’t work:  
- Try stopping Docker Desktop entirely.  
- Or change `docker-compose.yml` to map `8080:8000` and use `http://localhost:8080`.  

---

## 👤 Test User

A seeded test user is available for login:  

```
Email:    daniel@chronotrack.com  
Password: password123
```

---

## 🎯 Future Goals

- [ ] Real-time timers (WebSockets, ReactPHP)  
- [ ] Advanced reports (per user, per client, per project)  
- [ ] Notifications (reminders for deadlines/goals)  
- [ ] Role-based access (shared projects, team goals)  
