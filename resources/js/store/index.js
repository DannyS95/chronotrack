import { createStore } from 'vuex';
import goals from '@/store/modules/goals';
import tasks from '@/store/modules/tasks';
import timers from '@/store/modules/timers';

export default createStore({
    modules: {
        goals,
        tasks,
        timers,
    },
});
