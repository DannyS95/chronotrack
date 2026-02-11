<script setup>
import { computed, reactive } from 'vue';

const props = defineProps({
    goal: {
        type: Object,
        default: null,
    },
    tasks: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    creating: {
        type: Boolean,
        default: false,
    },
    timerBusy: {
        type: Boolean,
        default: false,
    },
    activeTaskId: {
        type: String,
        default: null,
    },
    canStartTask: {
        type: Function,
        required: true,
    },
    search: {
        type: String,
        default: '',
    },
});

const emit = defineEmits([
    'create-task',
    'toggle-task-status',
    'delete-task',
    'start-task-timer',
    'pause-task-timer',
    'stop-task-timer',
    'complete-goal',
    'update:search',
]);

const taskForm = reactive({
    title: '',
    description: '',
    timer_type: 'pomodoro',
    target_minutes: 25,
});

const filteredTasks = computed(() => {
    if (!props.search) {
        return props.tasks;
    }

    const term = props.search.toLowerCase();
    return props.tasks.filter((task) =>
        `${task.title} ${task.description ?? ''}`.toLowerCase().includes(term)
    );
});

const doneCount = computed(() => props.tasks.filter((task) => task.status === 'done').length);
const goalProgress = computed(() => {
    if (props.tasks.length === 0) {
        return 0;
    }

    return Math.round((doneCount.value / props.tasks.length) * 100);
});

const canCompleteGoal = computed(() => props.tasks.length > 0 && doneCount.value === props.tasks.length);

const submit = () => {
    if (!props.goal || !taskForm.title.trim()) {
        return;
    }

    const payload = {
        title: taskForm.title.trim(),
        description: taskForm.description.trim() || null,
        timer_type: taskForm.timer_type,
    };

    if (taskForm.timer_type === 'custom' || taskForm.timer_type === 'hourglass') {
        payload.target_minutes = Number(taskForm.target_minutes);
    }

    emit('create-task', {
        goalId: props.goal.id,
        payload,
    });

    taskForm.title = '';
    taskForm.description = '';
    taskForm.timer_type = 'pomodoro';
    taskForm.target_minutes = 25;
};

const progressWidth = (task) => `${Math.max(0, Math.min(100, task.live_progress_percent ?? task.progress_percent ?? 0))}%`;

const parseDateOnly = (dateString) => {
    if (!dateString || typeof dateString !== 'string') {
        return null;
    }

    const [year, month, day] = dateString.split('-').map(Number);
    if (!year || !month || !day) {
        return null;
    }

    return new Date(year, month - 1, day);
};

const formatGoalDate = (dateString) => {
    const date = parseDateOnly(dateString);
    if (!date) {
        return 'No date';
    }

    return new Intl.DateTimeFormat(undefined, {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
};

const formatGoalAge = (dateString) => {
    const date = parseDateOnly(dateString);
    if (!date) {
        return '';
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);

    const diffDays = Math.round((date.getTime() - today.getTime()) / 86400000);

    if (diffDays === 0) {
        return 'Today';
    }

    if (diffDays === -1) {
        return '1 day ago';
    }

    if (diffDays === 1) {
        return 'In 1 day';
    }

    return diffDays < 0 ? `${Math.abs(diffDays)} days ago` : `In ${diffDays} days`;
};
</script>

<template>
    <section class="task-panel glass">
        <div class="task-panel__top">
            <div>
                <p class="task-panel__heading">Daily Goals</p>
                <h2 v-if="goal" class="task-panel__goal">{{ goal.summary }}</h2>
                <div v-if="goal" class="task-panel__goal-meta">
                    <span>{{ formatGoalDate(goal.goal_date) }}</span>
                    <span v-if="formatGoalAge(goal.goal_date)">{{ formatGoalAge(goal.goal_date) }}</span>
                </div>
                <p v-if="goal?.description" class="task-panel__goal-description">{{ goal.description }}</p>
            </div>

            <button
                v-if="goal"
                type="button"
                class="task-panel__complete"
                :disabled="!canCompleteGoal"
                @click="$emit('complete-goal', goal.id)"
            >
                Complete Goal
            </button>
        </div>

        <div class="task-panel__goal-progress">
            <span :style="{ width: `${goalProgress}%` }" />
            <small>{{ goalProgress }}%</small>
        </div>

        <label class="task-panel__search">
            <input
                :value="search"
                type="search"
                placeholder="Search tasks..."
                @input="$emit('update:search', $event.target.value)"
            />
        </label>

        <form v-if="goal" class="task-form" @submit.prevent="submit">
            <input
                v-model="taskForm.title"
                type="text"
                placeholder="Task title"
                required
            />
            <textarea
                v-model="taskForm.description"
                rows="2"
                placeholder="Task description (optional)"
            />
            <div class="task-form__row">
                <select v-model="taskForm.timer_type">
                    <option value="pomodoro">Pomodoro (25 min)</option>
                    <option value="custom">Custom</option>
                    <option value="hourglass">Hourglass</option>
                </select>

                <select
                    v-if="taskForm.timer_type === 'hourglass'"
                    v-model.number="taskForm.target_minutes"
                >
                    <option :value="60">1h</option>
                    <option :value="90">1h 30m</option>
                    <option :value="120">2h</option>
                    <option :value="240">4h</option>
                </select>

                <input
                    v-else-if="taskForm.timer_type === 'custom'"
                    v-model.number="taskForm.target_minutes"
                    type="number"
                    min="1"
                    max="720"
                    placeholder="Minutes"
                    required
                />
            </div>

            <button type="submit" :disabled="creating">
                {{ creating ? 'Adding...' : 'Add Task' }}
            </button>
        </form>

        <p v-if="loading" class="task-panel__empty">Loading tasks...</p>
        <p v-else-if="!goal" class="task-panel__empty">Pick a goal to manage tasks.</p>
        <p v-else-if="filteredTasks.length === 0" class="task-panel__empty">No tasks yet.</p>

        <div class="task-list">
            <article
                v-for="(task, index) in filteredTasks"
                :key="task.id"
                class="task-card"
                :class="{ 'task-card--active': task.id === activeTaskId, 'task-card--done': task.status === 'done' }"
                :style="{ '--delay': `${index * 55}ms` }"
            >
                <div class="task-card__header">
                    <div>
                        <p class="task-card__title">{{ task.title }}</p>
                        <p class="task-card__meta">
                            {{ task.timer_type }} • {{ task.target_duration_human || 'no target' }}
                        </p>
                    </div>
                    <span class="task-card__percent">{{ task.live_progress_percent ?? task.progress_percent ?? 0 }}%</span>
                </div>

                <p v-if="task.description" class="task-card__description">{{ task.description }}</p>

                <div class="task-card__progress">
                    <span :style="{ width: progressWidth(task) }" />
                </div>

                <div class="task-card__actions">
                    <button
                        type="button"
                        class="btn btn--primary"
                        :disabled="task.status === 'done' || !canStartTask(task.id) || timerBusy"
                        @click="$emit('start-task-timer', task.id)"
                    >
                        Start
                    </button>
                    <button
                        type="button"
                        class="btn btn--secondary"
                        :disabled="activeTaskId !== task.id || timerBusy"
                        @click="$emit('pause-task-timer', task.id)"
                    >
                        Pause
                    </button>
                    <button
                        type="button"
                        class="btn btn--danger"
                        :disabled="activeTaskId !== task.id || timerBusy"
                        @click="$emit('stop-task-timer', task.id)"
                    >
                        Stop
                    </button>
                    <button
                        type="button"
                        class="btn btn--ghost"
                        @click="$emit('toggle-task-status', task)"
                    >
                        {{ task.status === 'done' ? 'Mark Active' : 'Mark Done' }}
                    </button>
                    <button
                        type="button"
                        class="btn btn--ghost"
                        @click="$emit('delete-task', task)"
                    >
                        Delete
                    </button>
                </div>
            </article>
        </div>
    </section>
</template>

<style scoped>
.task-panel {
    padding: 1.2rem;
    border-radius: 1rem;
    border: 1px solid rgba(138, 181, 216, 0.2);
    display: grid;
    gap: 0.95rem;
    min-height: 0;
}

.task-panel__top {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
}

.task-panel__heading {
    color: #8fb2ce;
    font-size: 0.82rem;
    letter-spacing: 0.06em;
    font-weight: 700;
    text-transform: uppercase;
}

.task-panel__goal {
    margin-top: 0.1rem;
    color: #e5f5ff;
    font-size: clamp(1.15rem, 2vw, 1.8rem);
    font-weight: 800;
    line-height: 1.2;
}

.task-panel__goal-description {
    color: #91aec7;
    margin-top: 0.35rem;
}

.task-panel__goal-meta {
    margin-top: 0.4rem;
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.task-panel__goal-meta span {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.22rem 0.56rem;
    border: 1px solid rgba(143, 188, 221, 0.28);
    background: rgba(9, 34, 57, 0.58);
    color: #b6d2e8;
    font-size: 0.75rem;
    font-weight: 600;
}

.task-panel__complete {
    border: 0;
    border-radius: 0.8rem;
    background: linear-gradient(135deg, #2b74b8, #4ca9f2);
    color: #ecf8ff;
    font-weight: 700;
    padding: 0.62rem 1rem;
    cursor: pointer;
    white-space: nowrap;
}

.task-panel__complete:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.task-panel__goal-progress {
    height: 0.5rem;
    border-radius: 999px;
    background: rgba(70, 110, 146, 0.4);
    overflow: hidden;
    position: relative;
}

.task-panel__goal-progress span {
    display: block;
    height: 100%;
    background: linear-gradient(90deg, #73cdff, #2e84ca);
}

.task-panel__goal-progress small {
    position: absolute;
    right: 0.5rem;
    top: -1.1rem;
    color: #a9c8e3;
    font-size: 0.8rem;
}

.task-panel__search input,
.task-form input,
.task-form textarea,
.task-form select {
    width: 100%;
    border: 1px solid rgba(145, 195, 235, 0.16);
    background: rgba(8, 28, 47, 0.55);
    color: #d7ebff;
    border-radius: 0.8rem;
    padding: 0.72rem 0.85rem;
    outline: none;
}

.task-form {
    display: grid;
    gap: 0.6rem;
    border: 1px solid rgba(134, 174, 207, 0.2);
    border-radius: 0.9rem;
    padding: 0.75rem;
    background: rgba(8, 31, 52, 0.56);
}

.task-form__row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.55rem;
}

.task-form button {
    border: 0;
    border-radius: 0.75rem;
    background: linear-gradient(130deg, #2d7bc3, #4db2ff);
    color: #ecf8ff;
    padding: 0.62rem 0.9rem;
    font-weight: 700;
    cursor: pointer;
}

.task-form button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.task-panel__empty {
    color: #8ea9c3;
    font-size: 0.9rem;
}

.task-list {
    min-height: 0;
    max-block-size: min(58vh, 40rem);
    overflow-y: auto;
    display: grid;
    grid-auto-rows: max-content;
    align-content: start;
    gap: 0.7rem;
    padding-right: 0.2rem;
    scrollbar-gutter: stable;
}

.task-card {
    border: 1px solid rgba(134, 176, 209, 0.2);
    border-radius: 0.95rem;
    background: rgba(8, 29, 48, 0.58);
    padding: 0.85rem;
    display: grid;
    gap: 0.55rem;
    align-self: start;
    transform: translateY(8px);
    opacity: 0;
    animation: cardIn 300ms ease forwards;
    animation-delay: var(--delay, 0ms);
}

.task-card--active {
    border-color: rgba(106, 190, 247, 0.45);
}

.task-card--done {
    opacity: 0.8;
}

.task-card__header {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}

.task-card__title {
    color: #e3f3ff;
    font-size: 1rem;
    font-weight: 700;
}

.task-card__meta {
    color: #8eafc9;
    font-size: 0.83rem;
}

.task-card__percent {
    color: #8cd4ff;
    font-size: 0.9rem;
    font-weight: 700;
}

.task-card__description {
    color: #9eb6cb;
    font-size: 0.9rem;
}

.task-card__progress {
    width: 100%;
    height: 0.42rem;
    border-radius: 999px;
    background: rgba(67, 111, 146, 0.38);
    overflow: hidden;
}

.task-card__progress span {
    display: block;
    height: 100%;
    background: linear-gradient(90deg, #6ec5ff, #2e84ca);
}

.task-card__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
}

.btn {
    border: 0;
    border-radius: 0.65rem;
    padding: 0.43rem 0.68rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: #e5f6ff;
    cursor: pointer;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn--primary {
    background: linear-gradient(145deg, #2e7ac1, #46a7f0);
}

.btn--secondary {
    background: linear-gradient(145deg, #3a6688, #2f5372);
}

.btn--danger {
    background: linear-gradient(145deg, #a35258, #7c363d);
}

.btn--ghost {
    background: rgba(22, 58, 87, 0.75);
    border: 1px solid rgba(141, 183, 216, 0.2);
}

@keyframes cardIn {
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 760px) {
    .task-panel__top {
        flex-direction: column;
    }

    .task-form__row {
        grid-template-columns: 1fr;
    }

    .task-list {
        max-block-size: min(52vh, 32rem);
    }
}
</style>
