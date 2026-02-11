<script setup>
import { computed, reactive } from 'vue';

const props = defineProps({
    goals: {
        type: Array,
        required: true,
    },
    selectedGoalId: {
        type: String,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    creating: {
        type: Boolean,
        default: false,
    },
    search: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['select-goal', 'create-goal', 'update:search']);

const goalForm = reactive({
    summary: '',
    description: '',
    goal_date: new Date().toISOString().slice(0, 10),
});

const filteredGoals = computed(() => {
    if (!props.search) {
        return props.goals;
    }

    const term = props.search.toLowerCase();
    return props.goals.filter((goal) =>
        `${goal.summary} ${goal.description ?? ''}`.toLowerCase().includes(term)
    );
});

const submit = () => {
    if (!goalForm.summary.trim()) {
        return;
    }

    emit('create-goal', {
        summary: goalForm.summary.trim(),
        description: goalForm.description.trim() || null,
        goal_date: goalForm.goal_date,
    });

    goalForm.summary = '';
    goalForm.description = '';
};

const progressWidth = (goal) => `${Math.max(0, Math.min(100, goal.percent_complete ?? 0))}%`;

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
    <section class="goal-panel glass">
        <div class="goal-panel__brand">
            <div class="goal-panel__logo">
                <span>✓</span>
            </div>
            <div>
                <p class="goal-panel__title">ChronoTrack</p>
                <p class="goal-panel__subtitle">Daily focus planner</p>
            </div>
        </div>

        <label class="goal-panel__search">
            <input
                :value="search"
                type="search"
                placeholder="Search goals..."
                @input="$emit('update:search', $event.target.value)"
            />
        </label>

        <form class="goal-form" @submit.prevent="submit">
            <input
                v-model="goalForm.summary"
                type="text"
                placeholder="Today's goal summary"
                required
            />
            <input
                v-model="goalForm.goal_date"
                type="date"
                required
            />
            <textarea
                v-model="goalForm.description"
                rows="2"
                placeholder="Optional goal details"
            />
            <button type="submit" :disabled="creating">
                {{ creating ? 'Saving...' : 'Add Goal' }}
            </button>
        </form>

        <div class="goal-list">
            <p class="goal-list__heading">Daily Goals</p>
            <p v-if="loading" class="goal-list__empty">Loading goals...</p>
            <p v-else-if="filteredGoals.length === 0" class="goal-list__empty">
                No goals yet. Create one to start.
            </p>

            <button
                v-for="(goal, index) in filteredGoals"
                :key="goal.id"
                class="goal-card"
                :class="{ 'goal-card--active': goal.id === selectedGoalId }"
                :style="{ '--delay': `${index * 55}ms` }"
                @click="$emit('select-goal', goal.id)"
            >
                <div class="goal-card__top">
                    <p class="goal-card__summary">{{ goal.summary }}</p>
                    <span class="goal-card__percent">
                        {{ goal.percent_complete ?? 0 }}%
                    </span>
                </div>
                <p class="goal-card__description">{{ goal.description || 'No description' }}</p>
                <div class="goal-card__meta">
                    <span>{{ formatGoalDate(goal.goal_date) }}</span>
                    <span v-if="formatGoalAge(goal.goal_date)">{{ formatGoalAge(goal.goal_date) }}</span>
                </div>
                <div class="goal-card__progress">
                    <span :style="{ width: progressWidth(goal) }" />
                </div>
            </button>
        </div>
    </section>
</template>

<style scoped>
.goal-panel {
    padding: 1.25rem;
    display: grid;
    gap: 1rem;
    height: 100%;
}

.goal-panel__brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.goal-panel__logo {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.75rem;
    background: linear-gradient(140deg, #5eb6ff, #2f7dc4);
    display: grid;
    place-items: center;
    font-size: 1.2rem;
    color: #051625;
    font-weight: 700;
    box-shadow: 0 10px 30px rgba(70, 150, 220, 0.45);
}

.goal-panel__title {
    color: #e6f4ff;
    font-size: 1.3rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.goal-panel__subtitle {
    color: #9bb8d6;
    font-size: 0.8rem;
}

.goal-panel__search input,
.goal-form input,
.goal-form textarea {
    width: 100%;
    border: 1px solid rgba(145, 195, 235, 0.16);
    background: rgba(8, 28, 47, 0.55);
    color: #d7ebff;
    border-radius: 0.8rem;
    padding: 0.72rem 0.85rem;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    font-size: 0.9rem;
}

.goal-panel__search input:focus,
.goal-form input:focus,
.goal-form textarea:focus {
    border-color: rgba(95, 178, 238, 0.55);
    box-shadow: 0 0 0 3px rgba(95, 178, 238, 0.15);
}

.goal-form {
    display: grid;
    gap: 0.6rem;
}

.goal-form button {
    border: 0;
    border-radius: 0.85rem;
    padding: 0.75rem 0.95rem;
    color: #e5f6ff;
    background: linear-gradient(135deg, #2e7cc5, #3da2f3);
    font-weight: 700;
    cursor: pointer;
}

.goal-form button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.goal-list {
    min-height: 0;
    display: grid;
    gap: 0.55rem;
    overflow-y: auto;
    padding-right: 0.2rem;
}

.goal-list__heading {
    color: #d9efff;
    font-size: 0.92rem;
    letter-spacing: 0.03em;
    font-weight: 700;
}

.goal-list__empty {
    color: #8ba6c2;
    font-size: 0.9rem;
}

.goal-card {
    border: 1px solid rgba(138, 178, 212, 0.18);
    background: rgba(8, 27, 44, 0.55);
    color: inherit;
    border-radius: 1rem;
    text-align: left;
    padding: 0.8rem;
    display: grid;
    gap: 0.45rem;
    cursor: pointer;
    transform: translateY(8px);
    opacity: 0;
    animation: cardIn 300ms ease forwards;
    animation-delay: var(--delay, 0ms);
}

.goal-card--active {
    border-color: rgba(96, 183, 242, 0.55);
    background: rgba(12, 39, 66, 0.7);
}

.goal-card__top {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}

.goal-card__summary {
    color: #dff0ff;
    font-weight: 700;
    font-size: 0.95rem;
}

.goal-card__percent {
    color: #9ad6ff;
    font-weight: 700;
    font-size: 0.88rem;
}

.goal-card__description {
    color: #97b2cc;
    font-size: 0.83rem;
}

.goal-card__meta {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
}

.goal-card__meta span {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    border: 1px solid rgba(140, 189, 222, 0.28);
    background: rgba(11, 40, 66, 0.58);
    color: #a9c6de;
    font-size: 0.73rem;
    font-weight: 600;
}

.goal-card__progress {
    width: 100%;
    height: 0.36rem;
    border-radius: 999px;
    background: rgba(83, 126, 162, 0.34);
    overflow: hidden;
}

.goal-card__progress span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #63bfff, #2d81c8);
}

@keyframes cardIn {
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
