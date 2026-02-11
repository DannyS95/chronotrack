<script setup>
const props = defineProps({
    activeTask: {
        type: Object,
        default: null,
    },
    activeTimer: {
        type: Object,
        default: null,
    },
    elapsedLabel: {
        type: String,
        default: '00:00',
    },
    remainingLabel: {
        type: String,
        default: '--',
    },
    progressPercent: {
        type: Number,
        default: 0,
    },
    busy: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['pause', 'resume', 'stop']);
</script>

<template>
    <section class="timer-card glass">
        <div class="timer-card__header">
            <h3>Task Timer</h3>
            <span v-if="activeTimer" class="timer-card__pill" :class="{ 'timer-card__pill--paused': activeTimer.is_paused }">
                {{ activeTimer.is_paused ? 'Paused' : 'Running' }}
            </span>
        </div>

        <div v-if="activeTask && activeTimer" class="timer-card__body">
            <p class="timer-card__task">{{ activeTask.title }}</p>
            <p class="timer-card__meta">
                {{ activeTask.timer_type }} • Target {{ activeTask.target_duration_human || 'not set' }}
            </p>
            <div class="timer-card__numbers">
                <div>
                    <span class="timer-card__label">Elapsed</span>
                    <strong>{{ elapsedLabel }}</strong>
                </div>
                <div>
                    <span class="timer-card__label">Remaining</span>
                    <strong>{{ remainingLabel }}</strong>
                </div>
            </div>

            <div class="timer-card__progress">
                <span :style="{ width: `${Math.max(0, Math.min(100, progressPercent))}%` }" />
            </div>

            <div class="timer-card__actions">
                <button
                    v-if="!activeTimer.is_paused"
                    type="button"
                    class="btn btn--secondary"
                    :disabled="busy"
                    @click="$emit('pause', activeTask.id)"
                >
                    Pause
                </button>
                <button
                    v-else
                    type="button"
                    class="btn btn--primary"
                    :disabled="busy"
                    @click="$emit('resume', activeTask.id)"
                >
                    Resume
                </button>
                <button
                    type="button"
                    class="btn btn--danger"
                    :disabled="busy"
                    @click="$emit('stop', activeTask.id)"
                >
                    Stop
                </button>
            </div>
        </div>

        <p v-else class="timer-card__empty">
            No active timer. Start one from your task list.
        </p>
    </section>
</template>

<style scoped>
.timer-card {
    padding: 1rem;
    border-radius: 1rem;
    border: 1px solid rgba(143, 186, 219, 0.2);
    height: fit-content;
    max-height: 100%;
    overflow-y: auto;
}

.timer-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.timer-card__header h3 {
    margin: 0;
    color: #dff1ff;
    font-size: 1rem;
}

.timer-card__pill {
    font-size: 0.77rem;
    font-weight: 700;
    color: #042035;
    background: #79d2ff;
    border-radius: 999px;
    padding: 0.2rem 0.6rem;
}

.timer-card__pill--paused {
    background: #f4d58f;
}

.timer-card__body {
    margin-top: 0.8rem;
    display: grid;
    gap: 0.72rem;
}

.timer-card__task {
    margin: 0;
    color: #f1f8ff;
    font-weight: 700;
}

.timer-card__meta {
    margin: 0;
    color: #8fb0cd;
    font-size: 0.85rem;
}

.timer-card__numbers {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.6rem;
}

.timer-card__numbers > div {
    border: 1px solid rgba(123, 167, 205, 0.22);
    background: rgba(8, 31, 51, 0.6);
    border-radius: 0.8rem;
    padding: 0.6rem;
    display: grid;
    gap: 0.2rem;
}

.timer-card__label {
    color: #8eafc9;
    font-size: 0.77rem;
}

.timer-card__numbers strong {
    color: #e8f6ff;
    font-size: 1.12rem;
}

.timer-card__progress {
    width: 100%;
    height: 0.44rem;
    border-radius: 999px;
    background: rgba(79, 121, 155, 0.35);
    overflow: hidden;
}

.timer-card__progress span {
    display: block;
    height: 100%;
    background: linear-gradient(90deg, #6ec4ff, #2f8ad0);
}

.timer-card__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
}

.btn {
    border: 0;
    border-radius: 0.72rem;
    padding: 0.55rem 0.8rem;
    font-weight: 700;
    cursor: pointer;
    color: #edf8ff;
}

.btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.btn--primary {
    background: linear-gradient(140deg, #3489cf, #4ab0ff);
}

.btn--secondary {
    background: linear-gradient(140deg, #3b6e94, #2f5574);
}

.btn--danger {
    background: linear-gradient(140deg, #a45356, #81363b);
}

.timer-card__empty {
    margin-top: 0.9rem;
    color: #8fa9c1;
    font-size: 0.9rem;
}
</style>
