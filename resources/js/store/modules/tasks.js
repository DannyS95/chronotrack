import api from '@/services/chronotrackApi';

const state = () => ({
    byGoalId: {},
    byId: {},
    loadingByGoalId: {},
    creatingByGoalId: {},
});

const getters = {
    tasksForGoal:
        (state) =>
        (goalId) =>
            state.byGoalId[goalId] || [],

    taskById:
        (state) =>
        (taskId) =>
            state.byId[taskId] || null,

    goalIdByTaskId:
        (state) =>
        (taskId) => {
            const goalIds = Object.keys(state.byGoalId);
            const foundGoalId = goalIds.find((goalId) =>
                (state.byGoalId[goalId] || []).some((task) => task.id === taskId)
            );

            return foundGoalId || null;
        },
};

const mutations = {
    SET_GOAL_LOADING(state, { goalId, value }) {
        state.loadingByGoalId = {
            ...state.loadingByGoalId,
            [goalId]: value,
        };
    },

    SET_GOAL_CREATING(state, { goalId, value }) {
        state.creatingByGoalId = {
            ...state.creatingByGoalId,
            [goalId]: value,
        };
    },

    SET_TASKS_FOR_GOAL(state, { goalId, tasks }) {
        state.byGoalId = {
            ...state.byGoalId,
            [goalId]: tasks,
        };

        const nextById = { ...state.byId };
        tasks.forEach((task) => {
            nextById[task.id] = task;
        });
        state.byId = nextById;
    },

    UPSERT_TASK(state, { goalId, task }) {
        const existing = state.byGoalId[goalId] || [];
        const index = existing.findIndex((item) => item.id === task.id);
        const next = [...existing];

        if (index === -1) {
            next.unshift(task);
        } else {
            next.splice(index, 1, {
                ...next[index],
                ...task,
            });
        }

        state.byGoalId = {
            ...state.byGoalId,
            [goalId]: next,
        };
        state.byId = {
            ...state.byId,
            [task.id]: task,
        };
    },

    REMOVE_TASK(state, { goalId, taskId }) {
        const next = (state.byGoalId[goalId] || []).filter((item) => item.id !== taskId);
        state.byGoalId = {
            ...state.byGoalId,
            [goalId]: next,
        };

        if (state.byId[taskId]) {
            const nextById = { ...state.byId };
            delete nextById[taskId];
            state.byId = nextById;
        }
    },
};

const actions = {
    async fetchTasksForGoal({ commit }, payload) {
        const goalId = typeof payload === 'string' ? payload : payload?.goalId;
        const silent = typeof payload === 'string' ? false : Boolean(payload?.silent);

        if (!goalId) {
            return [];
        }

        if (!silent) {
            commit('SET_GOAL_LOADING', { goalId, value: true });
        }

        try {
            const payload = await api.getGoalTasks(goalId);
            const tasks = payload.data ?? [];
            commit('SET_TASKS_FOR_GOAL', {
                goalId,
                tasks,
            });
            return tasks;
        } finally {
            if (!silent) {
                commit('SET_GOAL_LOADING', { goalId, value: false });
            }
        }
    },

    async createTask({ commit }, { goalId, payload }) {
        commit('SET_GOAL_CREATING', { goalId, value: true });

        try {
            const response = await api.createGoalTask(goalId, payload);
            if (response?.data) {
                commit('UPSERT_TASK', {
                    goalId,
                    task: response.data,
                });
            }

            return response?.data ?? null;
        } finally {
            commit('SET_GOAL_CREATING', { goalId, value: false });
        }
    },

    async fetchTaskById({ state, commit, getters }, taskId) {
        if (state.byId[taskId]) {
            return state.byId[taskId];
        }

        const task = await api.getTask(taskId);
        const goalId = task.goal_id || getters.goalIdByTaskId(taskId);

        if (goalId) {
            commit('UPSERT_TASK', {
                goalId,
                task,
            });
        } else {
            commit('SET_TASKS_FOR_GOAL', {
                goalId: '__orphan__',
                tasks: [
                    ...(state.byGoalId.__orphan__ || []).filter((item) => item.id !== task.id),
                    task,
                ],
            });
        }

        return task;
    },

    async updateTask({ commit, getters }, { taskId, goalId, payload }) {
        const response = await api.updateTask(taskId, payload);
        const task = response?.data ?? null;
        const resolvedGoalId = goalId || getters.goalIdByTaskId(taskId) || task?.goal_id;

        if (task && resolvedGoalId) {
            commit('UPSERT_TASK', {
                goalId: resolvedGoalId,
                task,
            });
        }

        return task;
    },

    async deleteTask({ commit, getters }, { taskId, goalId }) {
        await api.deleteTask(taskId);
        const resolvedGoalId = goalId || getters.goalIdByTaskId(taskId);

        if (resolvedGoalId) {
            commit('REMOVE_TASK', {
                goalId: resolvedGoalId,
                taskId,
            });
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
