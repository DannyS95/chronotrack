<script setup>
import GoalPanel from '@/Components/ChronoTrack/GoalPanel.vue';
import TaskList from '@/Components/ChronoTrack/TaskList.vue';
import TaskTimer from '@/Components/ChronoTrack/TaskTimer.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useStore } from 'vuex';

const store = useStore();

const goalSearch = ref('');
const taskSearch = ref('');
const clockTick = ref(Date.now());
let clockHandle = null;

const goals = computed(() => store.state.goals.goals);
const selectedGoalId = computed(() => store.state.goals.selectedGoalId);
const selectedGoal = computed(() => store.getters['goals/selectedGoal']);
const goalsLoading = computed(() => store.state.goals.loading);
const goalsCreating = computed(() => store.state.goals.creating);

const allGoalTasks = computed(() => {
    if (!selectedGoalId.value) {
        return [];
    }

    return store.getters['tasks/tasksForGoal'](selectedGoalId.value);
});

const tasksLoading = computed(() => {
    if (!selectedGoalId.value) {
        return false;
    }

    return Boolean(store.state.tasks.loadingByGoalId[selectedGoalId.value]);
});

const tasksCreating = computed(() => {
    if (!selectedGoalId.value) {
        return false;
    }

    return Boolean(store.state.tasks.creatingByGoalId[selectedGoalId.value]);
});

const activeTimer = computed(() => store.state.timers.activeTimer);
const activeTaskId = computed(() => store.getters['timers/activeTaskId']);
const timerBusy = computed(() => store.state.timers.busy);

const activeTask = computed(() => {
    if (!activeTaskId.value) {
        return null;
    }

    return store.getters['tasks/taskById'](activeTaskId.value);
});

const runtimeTaskMap = computed(() => {
    const now = clockTick.value;
    const active = activeTimer.value;
    const map = {};

    allGoalTasks.value.forEach((task) => {
        let elapsed = Number(task.accumulated_seconds || 0);

        if (active && active.task_id === task.id && !active.is_paused) {
            const startedAt = Date.parse(active.started_at);
            if (!Number.isNaN(startedAt)) {
                const pausedTotal = Number(active.paused_total || 0);
                const delta = Math.max(0, Math.floor((now - startedAt) / 1000) - pausedTotal);
                elapsed += delta;
            }
        }

        const target = Number(task.target_duration_seconds || 0);
        const percent = target > 0 ? Math.min(100, Math.floor((elapsed / target) * 100)) : Number(task.progress_percent || 0);

        map[task.id] = {
            ...task,
            live_elapsed_seconds: elapsed,
            live_progress_percent: percent,
        };
    });

    return map;
});

const tasks = computed(() =>
    allGoalTasks.value.map((task) => runtimeTaskMap.value[task.id] || task)
);

const goalsWithProgress = computed(() =>
    goals.value.map((goal) => {
        const progress = store.getters['goals/goalProgress'](goal.id);
        return {
            ...goal,
            percent_complete: progress?.percent_complete ?? goal.percent_complete ?? 0,
        };
    })
);

const formatClock = (seconds) => {
    const total = Math.max(0, Math.floor(seconds));
    const hrs = Math.floor(total / 3600);
    const mins = Math.floor((total % 3600) / 60);
    const secs = total % 60;

    if (hrs > 0) {
        return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
};

const activeElapsedSeconds = computed(() => {
    if (!activeTimer.value || !activeTask.value) {
        return 0;
    }

    const fromTask = runtimeTaskMap.value[activeTask.value.id]?.live_elapsed_seconds;
    if (typeof fromTask === 'number') {
        return fromTask;
    }

    return Number(activeTask.value.accumulated_seconds || 0);
});

const activeRemainingSeconds = computed(() => {
    if (!activeTask.value?.target_duration_seconds) {
        return null;
    }

    return Math.max(0, Number(activeTask.value.target_duration_seconds) - activeElapsedSeconds.value);
});

const activeProgressPercent = computed(() => {
    if (!activeTask.value?.target_duration_seconds) {
        return 0;
    }

    return Math.min(100, Math.floor((activeElapsedSeconds.value / Number(activeTask.value.target_duration_seconds)) * 100));
});

const canStartTask = (taskId) => {
    if (!activeTimer.value) {
        return true;
    }

    return activeTimer.value.task_id === taskId;
};

const refreshGoalTasks = async ({ silent = false } = {}) => {
    if (!selectedGoalId.value) {
        return;
    }

    await store.dispatch('tasks/fetchTasksForGoal', {
        goalId: selectedGoalId.value,
        silent,
    });
    await store.dispatch('goals/fetchGoalProgress', selectedGoalId.value);
    await store.dispatch('timers/fetchActiveTimer');
};

const handleCreateGoal = async (payload) => {
    await store.dispatch('goals/createGoal', payload);
    await refreshGoalTasks();
};

const handleCreateTask = async ({ goalId, payload }) => {
    await store.dispatch('tasks/createTask', {
        goalId,
        payload,
    });
    await store.dispatch('goals/fetchGoalProgress', goalId);
};

const handleToggleTaskStatus = async (task) => {
    const nextStatus = task.status === 'done' ? 'active' : 'done';

    if (nextStatus === 'done' && activeTaskId.value === task.id) {
        await store.dispatch('timers/stopTaskTimer', task.id);
    }

    await store.dispatch('tasks/updateTask', {
        goalId: selectedGoalId.value,
        taskId: task.id,
        payload: {
            status: nextStatus,
        },
    });
    await refreshGoalTasks();
};

const handleDeleteTask = async (task) => {
    if (activeTaskId.value === task.id) {
        await store.dispatch('timers/stopTaskTimer', task.id);
    }

    await store.dispatch('tasks/deleteTask', {
        goalId: selectedGoalId.value,
        taskId: task.id,
    });
    await refreshGoalTasks();
};

const handleStartTimer = async (taskId) => {
    if (!canStartTask(taskId)) {
        return;
    }

    await store.dispatch('timers/startTaskTimer', taskId);
    await refreshGoalTasks({ silent: true });
};

const handlePauseTimer = async (taskId) => {
    await store.dispatch('timers/pauseTaskTimer', taskId);
    await refreshGoalTasks({ silent: true });
};

const handleStopTimer = async (taskId) => {
    await store.dispatch('timers/stopTaskTimer', taskId);
    await refreshGoalTasks({ silent: true });
};

const handleCompleteGoal = async (goalId) => {
    await store.dispatch('goals/completeGoal', goalId);
    await refreshGoalTasks();
};

watch(
    selectedGoalId,
    async (goalId) => {
        if (!goalId) {
            return;
        }

        await store.dispatch('tasks/fetchTasksForGoal', goalId);
        await store.dispatch('goals/fetchGoalProgress', goalId);
    },
    { immediate: true }
);

watch(
    activeTaskId,
    async (taskId) => {
        if (!taskId) {
            return;
        }

        const knownTask = store.getters['tasks/taskById'](taskId);
        if (!knownTask) {
            await store.dispatch('tasks/fetchTaskById', taskId);
        }
    },
    { immediate: true }
);

onMounted(async () => {
    await store.dispatch('goals/fetchGoals');
    await store.dispatch('timers/fetchActiveTimer');

    clockHandle = window.setInterval(() => {
        clockTick.value = Date.now();
    }, 1000);
});

onBeforeUnmount(() => {
    if (clockHandle) {
        window.clearInterval(clockHandle);
    }
});
</script>

<template>
    <Head title="ChronoTrack Dashboard" />

    <div class="chronotrack-page">
        <div class="chronotrack-shell">
            <aside class="chronotrack-sidebar">
                <GoalPanel
                    :goals="goalsWithProgress"
                    :selected-goal-id="selectedGoalId"
                    :loading="goalsLoading"
                    :creating="goalsCreating"
                    :search="goalSearch"
                    @select-goal="store.dispatch('goals/selectGoal', $event)"
                    @create-goal="handleCreateGoal"
                    @update:search="goalSearch = $event"
                />
            </aside>

            <main class="chronotrack-main">
                <header class="main-topbar glass">
                    <label class="main-topbar__search">
                        <input
                            type="search"
                            :value="taskSearch"
                            placeholder="Search tasks..."
                            @input="taskSearch = $event.target.value"
                        />
                    </label>

                    <div class="main-topbar__user">
                        <p>Hello, {{ $page.props.auth.user.name }}</p>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="logout"
                        >
                            Logout
                        </Link>
                    </div>
                </header>

                <div class="main-grid">
                    <TaskList
                        :goal="selectedGoal"
                        :tasks="tasks"
                        :loading="tasksLoading"
                        :creating="tasksCreating"
                        :timer-busy="timerBusy"
                        :active-task-id="activeTaskId"
                        :can-start-task="canStartTask"
                        :search="taskSearch"
                        @create-task="handleCreateTask"
                        @toggle-task-status="handleToggleTaskStatus"
                        @delete-task="handleDeleteTask"
                        @start-task-timer="handleStartTimer"
                        @pause-task-timer="handlePauseTimer"
                        @stop-task-timer="handleStopTimer"
                        @complete-goal="handleCompleteGoal"
                        @update:search="taskSearch = $event"
                    />

                    <TaskTimer
                        :active-task="activeTask"
                        :active-timer="activeTimer"
                        :elapsed-label="formatClock(activeElapsedSeconds)"
                        :remaining-label="activeRemainingSeconds === null ? '--' : formatClock(activeRemainingSeconds)"
                        :progress-percent="activeProgressPercent"
                        :busy="timerBusy"
                        @pause="handlePauseTimer"
                        @resume="handleStartTimer"
                        @stop="handleStopTimer"
                    />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Sora:wght@500;700&display=swap');

.chronotrack-page {
    height: 100dvh;
    min-height: 100dvh;
    box-sizing: border-box;
    background:
        radial-gradient(65rem 26rem at 85% 100%, rgba(119, 195, 255, 0.22), transparent 65%),
        radial-gradient(50rem 22rem at -5% 95%, rgba(101, 176, 240, 0.22), transparent 62%),
        linear-gradient(165deg, #071a2c 0%, #0a233a 48%, #061425 100%);
    padding: clamp(0.8rem, 1.6vw, 1.3rem);
    position: relative;
    overflow: hidden;
    font-family: 'Manrope', 'Sora', 'Segoe UI', sans-serif;
}

.chronotrack-page::before,
.chronotrack-page::after {
    content: '';
    position: absolute;
    border: 1px solid rgba(139, 185, 219, 0.18);
    transform: rotate(45deg);
    pointer-events: none;
}

.chronotrack-page::before {
    width: 1rem;
    height: 1rem;
    left: 22%;
    top: 18%;
}

.chronotrack-page::after {
    width: 0.75rem;
    height: 0.75rem;
    right: 14%;
    bottom: 22%;
}

.chronotrack-shell {
    width: min(1300px, 100%);
    margin: 0 auto;
    height: 100%;
    min-height: 0;
    border-radius: 1.25rem;
    border: 1px solid rgba(146, 188, 220, 0.2);
    background: rgba(5, 22, 37, 0.4);
    display: grid;
    grid-template-columns: minmax(290px, 330px) minmax(0, 1fr);
    box-shadow: 0 20px 80px rgba(2, 12, 22, 0.45);
    backdrop-filter: blur(9px);
    overflow: hidden;
}

.chronotrack-sidebar {
    border-right: 1px solid rgba(146, 188, 220, 0.16);
    background: linear-gradient(180deg, rgba(8, 29, 48, 0.92), rgba(8, 25, 41, 0.78));
    min-height: 0;
    overflow: hidden;
}

.chronotrack-main {
    padding: 1rem;
    display: grid;
    grid-template-rows: auto minmax(0, 1fr);
    gap: 1rem;
    min-height: 0;
    overflow: hidden;
}

.main-topbar {
    border-radius: 1rem;
    padding: 0.8rem 0.9rem;
    border: 1px solid rgba(145, 188, 219, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.8rem;
}

.main-topbar__search {
    width: min(620px, 100%);
}

.main-topbar__search input {
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid rgba(149, 193, 226, 0.2);
    background: rgba(10, 33, 54, 0.6);
    color: #d9efff;
    padding: 0.68rem 0.82rem;
    outline: none;
}

.main-topbar__user {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    color: #d8eeff;
    font-weight: 600;
    white-space: nowrap;
}

.logout {
    border: 1px solid rgba(148, 191, 225, 0.25);
    background: rgba(13, 44, 70, 0.76);
    color: #dff1ff;
    border-radius: 0.72rem;
    padding: 0.52rem 0.78rem;
    font-weight: 700;
    cursor: pointer;
}

.main-grid {
    height: 100%;
    min-height: 0;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(320px, 360px);
    gap: 0.95rem;
    overflow: hidden;
}

.main-grid > * {
    min-height: 0;
}

.glass {
    background:
        linear-gradient(150deg, rgba(17, 49, 74, 0.5), rgba(10, 29, 47, 0.66));
    backdrop-filter: blur(10px);
}

@media (max-width: 1120px) {
    .chronotrack-shell {
        grid-template-columns: 1fr;
        height: 100%;
    }

    .chronotrack-sidebar {
        border-right: 0;
        border-bottom: 1px solid rgba(146, 188, 220, 0.16);
    }

    .main-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 760px) {
    .chronotrack-page {
        padding: 0.4rem;
    }

    .chronotrack-shell {
        border-radius: 0.9rem;
    }

    .chronotrack-main {
        padding: 0.7rem;
    }

    .main-topbar {
        flex-direction: column;
        align-items: stretch;
    }

    .main-topbar__search {
        width: 100%;
    }

    .main-topbar__user {
        justify-content: space-between;
    }
}
</style>
