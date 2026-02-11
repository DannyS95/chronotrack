import api from '@/services/chronotrackApi';

const state = () => ({
    goals: [],
    selectedGoalId: null,
    loading: false,
    creating: false,
    progressByGoalId: {},
});

const getters = {
    selectedGoal(state) {
        return state.goals.find((goal) => goal.id === state.selectedGoalId) || null;
    },
    goalProgress:
        (state) =>
        (goalId) =>
            state.progressByGoalId[goalId] || null,
};

const mutations = {
    SET_LOADING(state, value) {
        state.loading = value;
    },
    SET_CREATING(state, value) {
        state.creating = value;
    },
    SET_GOALS(state, goals) {
        state.goals = goals;
    },
    SET_SELECTED_GOAL_ID(state, goalId) {
        state.selectedGoalId = goalId;
    },
    UPSERT_GOAL(state, goal) {
        const index = state.goals.findIndex((item) => item.id === goal.id);
        if (index === -1) {
            state.goals.unshift(goal);
            return;
        }

        state.goals.splice(index, 1, {
            ...state.goals[index],
            ...goal,
        });
    },
    SET_GOAL_PROGRESS(state, { goalId, progress }) {
        state.progressByGoalId = {
            ...state.progressByGoalId,
            [goalId]: progress,
        };
    },
};

const actions = {
    async fetchGoals({ commit, state }) {
        commit('SET_LOADING', true);

        try {
            const payload = await api.getDailyGoals();
            const goals = payload.data ?? [];
            commit('SET_GOALS', goals);

            if (goals.length > 0 && !state.selectedGoalId) {
                commit('SET_SELECTED_GOAL_ID', goals[0].id);
            }

            return goals;
        } finally {
            commit('SET_LOADING', false);
        }
    },

    async createGoal({ commit }, payload) {
        commit('SET_CREATING', true);

        try {
            const response = await api.createDailyGoal(payload);
            if (response?.data) {
                commit('UPSERT_GOAL', response.data);
                commit('SET_SELECTED_GOAL_ID', response.data.id);
            }

            return response?.data ?? null;
        } finally {
            commit('SET_CREATING', false);
        }
    },

    selectGoal({ commit }, goalId) {
        commit('SET_SELECTED_GOAL_ID', goalId);
    },

    async fetchGoalProgress({ commit }, goalId) {
        const response = await api.getDailyGoalProgress(goalId);
        const progress = response?.progress ?? null;

        if (progress) {
            commit('SET_GOAL_PROGRESS', {
                goalId,
                progress,
            });
        }

        return progress;
    },

    async completeGoal({ commit }, goalId) {
        const response = await api.completeDailyGoal(goalId);
        const updatedGoal = response?.data?.goal ?? null;

        if (updatedGoal) {
            commit('UPSERT_GOAL', updatedGoal);
        }

        return updatedGoal;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
};
