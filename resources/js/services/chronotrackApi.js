import axios from 'axios';

const client = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
    withXSRFToken: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
});

const unwrap = async (promise) => {
    const response = await promise;
    return response.data;
};

export default {
    getDailyGoals(params = {}) {
        return unwrap(client.get('/daily-goals', { params }));
    },

    createDailyGoal(payload) {
        return unwrap(client.post('/daily-goals', payload));
    },

    getDailyGoalProgress(goalId) {
        return unwrap(client.get(`/daily-goals/${goalId}/progress`));
    },

    completeDailyGoal(goalId) {
        return unwrap(client.post(`/daily-goals/${goalId}/complete`));
    },

    getGoalTasks(goalId, params = {}) {
        return unwrap(client.get(`/daily-goals/${goalId}/tasks`, { params }));
    },

    createGoalTask(goalId, payload) {
        return unwrap(client.post(`/daily-goals/${goalId}/tasks`, payload));
    },

    getTask(taskId) {
        return unwrap(client.get(`/tasks/${taskId}`));
    },

    updateTask(taskId, payload) {
        return unwrap(client.patch(`/tasks/${taskId}`, payload));
    },

    deleteTask(taskId) {
        return unwrap(client.delete(`/tasks/${taskId}`));
    },

    startTaskTimer(taskId) {
        return unwrap(client.post(`/tasks/${taskId}/timers/start`));
    },

    pauseTaskTimer(taskId) {
        return unwrap(client.post(`/tasks/${taskId}/timers/pause`));
    },

    stopTaskTimer(taskId) {
        return unwrap(client.post(`/tasks/${taskId}/timers/stop`));
    },

    getTaskTimers(taskId, params = {}) {
        return unwrap(client.get(`/tasks/${taskId}/timers`, { params }));
    },

    getActiveTimer() {
        return unwrap(client.get('/timers/active'));
    },

    stopCurrentTimer() {
        return unwrap(client.post('/timers/stop'));
    },
};
