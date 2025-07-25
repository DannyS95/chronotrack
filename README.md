# ⏱️ ChronoTrack

ChronoTrack is a **task scheduler + time tracker** built using Laravel, Inertia.js (Vue), and Clean Architecture principles.

## 📦 Tech Stack

- **Laravel** — Backend framework (Clean Architecture)
- **Inertia.js** — UI glue between Laravel and Vue
- **Vue 3** — Reactive frontend components
- **Tailwind CSS** — Modern utility-first styling
- **Pest** — Developer-friendly testing framework

---

## 🧩 Architecture

Organized with a Clean Architecture structure:
```
/app
    /Domain
    /Application
    /Infrastructure
    /UI
```


Each layer is responsible for a specific concern, promoting separation of concerns, testability, and flexibility.

---

## ✅ Core Features (Planned & In Progress)

### 🗂️ Projects
- [x] Create project  
- [ ] View all projects  
- [ ] Archive/delete project  

### ✅ Tasks
- [x] Create task (linked to project)  
- [ ] View tasks per project  
- [ ] Edit/delete task  
- [ ] Task status (To Do, In Progress, Done)  

### ⏱️ Timers
- [x] Start timer on task  
- [x] Stop timer  
- [ ] Prevent multiple timers per user  
- [ ] View time logs per task  

### 🎯 Goals
- [ ] Create goal (e.g., "Master symbolic manipulation")  
- [ ] Attach goal to project  
- [ ] Mark goal as achieved  
- [ ] View goals progress per project  

### 📊 Reports
- [ ] View time spent per project  
- [ ] View time per day/week/month  
- [ ] Export CSV or JSON summary  

### 👤 User System
- [ ] Authentication  
- [ ] View own tasks and timers only  

---

## 🚧 Status

Currently under development — building out vertical slices by use case, starting with the **StartTimer** flow.

---

## 🧠 Philosophy

Built to explore:
- Clean architecture in Laravel
- DDD patterns (Entities, Use Cases, Interfaces)
- Inertia as a full-stack bridge
- Vue 3 composition API in practice

---


Each layer is responsible for a specific concern, promoting separation of concerns, testability, and flexibility.

---

## ✅ Core Features (Planned & In Progress)

### 🗂️ Projects
- [x] Create project
- [x] View all projects
- [ ] Archive/delete project

### ✅ Tasks
- [x] Create task (linked to project)
- [ ] View tasks per project
- [ ] Edit/delete task
- [ ] Task status (To Do, In Progress, Done)

### ⏱️ Timers
- [x] Start timer on task
- [x] Stop timer
- [ ] Prevent multiple timers per user
- [ ] View time logs per task

### 📊 Reports
- [ ] View time spent per project
- [ ] View time per day/week/month
- [ ] Export CSV or JSON summary

### 👤 User System
- [ ] Authentication
- [ ] View own tasks and timers only

---

## 🚧 Status

Currently under development — building out vertical slices by use case, starting with the **StartTimer** flow.

---

## 🧠 Philosophy

Built to explore:
- Clean architecture in Laravel
- DDD patterns (Entities, Use Cases, Interfaces)
- Inertia as a full-stack bridge
- Vue 3 composition API in practice

---

## 🔧 Setup

```bash
git clone https://github.com/DannyS95/chronotrack.git
cd chronotrack
make up
make install
make dev
