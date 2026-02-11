import api from '@/services/chronotrackApi';

const state = () => ({
    activeTimer: null,
    busy: false,
});

const getters = {
    activeTaskId(state) {
        return state.activeTimer?.task_id || null;
    },
    hasActiveTimer(state) {
        return Boolean(state.activeTimer);
    },
    isPaused(state) {
        return state.activeTimer?.is_paused === true;
    },
};

const mutations = {
    SET_BUSY(state, value) {
        state.busy = value;
    },
    SET_ACTIVE_TIMER(state, timer) {
        state.activeTimer = timer || null;
    },
};

const actions = {
    async fetchActiveTimer({ commit }) {
        const response = await api.getActiveTimer();
        commit('SET_ACTIVE_TIMER', response?.activeTimer ?? null);
        return response?.activeTimer ?? null;
    },

    async startTaskTimer({ commit }, taskId) {
        commit('SET_BUSY', true);
        try {
            const timer = await api.startTaskTimer(taskId);
            commit('SET_ACTIVE_TIMER', timer);
            return timer;
        } finally {
            commit('SET_BUSY', false);
        }
    },

    async pauseTaskTimer({ commit }, taskId) {
        commit('SET_BUSY', true);
        try {
            const timer = await api.pauseTaskTimer(taskId);
            commit('SET_ACTIVE_TIMER', timer);
            return timer;
        } finally {
            commit('SET_BUSY', false);
        }
    },

    async stopTaskTimer({ commit }, taskId) {
        commit('SET_BUSY', true);
        try {
            await api.stopTaskTimer(taskId);
            commit('SET_ACTIVE_TIMER', null);
        } finally {
            commit('SET_BUSY', false);
        }
    },

    async stopCurrentTimer({ commit }) {
        commit('SET_BUSY', true);
        try {
            await api.stopCurrentTimer();
            commit('SET_ACTIVE_TIMER', null);
        } finally {
            commit('SET_BUSY', false);
        }
    },
};

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
};
