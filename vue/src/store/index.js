import {createStore} from 'vuex';
import axiosClient from '../axios';

const tmpSurveys = [
    {
        id: 100,
        title: 'Survey 1',
        slug: 'survey-1',
        status: 'draft',
        image: './img/bnWmD60.png',
        description: 'This is a survey',
        created_at: '2021-01-01 00:00:00',
        updated_at: '2021-01-01 00:00:00',
        expire_date: '2024-01-01 00:00:00',
        questions: [
            {
                id: 1,
                type: 'select',
                question: 'What Country are you from?',
                description: null,
                data: {
                    options: [
                        {uuid: '553e180e-42a6-4b45-9e22-79ee49ac5c6f', value: 'Vietnam'},
                        {uuid: '22cd39d2-c21b-4e66-a455-4f07a7aa7125', value: 'USA'},
                        {uuid: 'c8aeadd7-f63d-4e2f-bece-eba1411efdbe', value: 'UK'},
                    ]
                },
            },
            {
                id: 2,
                type: 'checkbox',
                question: 'What programming language do you like?',
                description: 'They all rule but choose one yaaahh',
                data: {
                    options: [
                        {uuid: 'ee27a62c-a708-4a12-8869-ee53bf372c46', value: 'Javascript'},
                        {uuid: 'b457c330-79c7-46a7-a2fe-aac9d5c49369', value: 'Python'},
                        {uuid: '2ee8761a-0185-4d84-8877-44a695fec998', value: 'PHP'},
                    ]
                },
            },
            {
                id: 3,
                type: 'radio',
                question: 'What JS Framework do you like?',
                description: 'They all rule but choose one yaaahh',
                data: {
                    options: [
                        {uuid: 'dad22d64-27b6-48e8-b908-46a3bacbcc30', value: 'Vue'},
                        {uuid: '97a239ad-79e6-465a-986e-23602a805992', value: 'React'},
                        {uuid: 'd63ffd0b-65a3-41e9-8994-515f62cc7928', value: 'Angular'},
                    ]
                },
            },
            {
                id: 3,
                type: 'text',
                question: 'Why is JS cool?',
                description: null,
                data: {},
            },
            {
                id: 4,
                type: 'textarea',
                question: 'WRITE MORE',
                description: null,
                data: {},
            },
        ],
    },
    {
        id: 200,
        title: 'Survey 2',
        slug: 'survey-2',
        status: 'active',
        image: './img/VHoJ47f.gif',
        description: 'Nothing lika a nice cuppa tea!',
        created_at: '2021-01-01 00:00:00',
        updated_at: '2021-01-01 00:00:00',
        expire_date: '2024-01-01 00:00:00',
        questions: [
        ],
    },
    {
        id: 300,
        title: 'Survey 3',
        slug: 'survey-3',
        status: 'active',
        image: './img/bnWmD60.png',
        description: 'MEMEZZZZ',
        created_at: '2021-01-01 00:00:00',
        updated_at: '2021-01-01 00:00:00',
        expire_date: '2024-01-01 00:00:00',
        questions: [
        ],
    },
    {
        id: 400,
        title: 'Survey 4',
        slug: 'survey-4',
        status: 'active',
        image: './img/VHoJ47f.gif',
        description: 'Nothing lika a nice cuppa tea!',
        created_at: '2021-01-01 00:00:00',
        updated_at: '2021-01-01 00:00:00',
        expire_date: '2024-01-01 00:00:00',
        questions: [
        ],
    },
];

const store = createStore({
    state: {
        user: {
            data: {},
            token: sessionStorage.getItem('TOKEN'),
        },
        currentSurvey: {
            loading: false,
            data: {},
        },
        surveys: [...tmpSurveys],
        questionTypes: ['text', 'select', 'checkbox', 'radio','textarea']
    },
    getters: {},
    actions: {
        getSurvey({commit}, id) {
            commit('setCurrentSurveyLoading', true);
            return axiosClient
                .get(`/survey/${id}`)
                .then((res) => {
                    commit('setCurrentSurvey', res.data);
                    commit('setCurrentSurveyLoading', false);
                    return res;
                })
                .catch((err) => {
                    commit('setCurrentSurveyLoading', false);
                    throw err;
                });
        },
        saveSurvey({ commit }, survey) {
            delete survey.image_url;
            let response;
            if (survey.id) {
                response = axiosClient
                    .put(`/survey/${survey.id}`, survey)
                    .then((res) => {
                        commit('updateSurvey', res.data);
                        return res;
                    })
            } else {
                response = axiosClient.post('/survey', survey).then((res) => {
                    commit('saveSurvey', res.data);
                    return res;
                });
            }

            return response;
        },
        register({ commit }, user) {
            return axiosClient.post('/register', user)
            .then(({data}) => {
                commit('setUser', data);
                return data;
            })
        }, 
        login({ commit }, user) {
            return axiosClient.post('/login', user)
            .then(({data}) => {
                commit('setUser', data);
                return data;
            })
        },
        logout({ commit }) {
            return axiosClient.post('/logout')
            .then(response => {
                commit('logout');
                return response;
            })
        }
    },
    mutations: {
        setCurrentSurveyLoading: (state, loading) => {
            state.currentSurvey.loading = loading;
        },
        setCurrentSurvey: (state, survey) => {
            state.currentSurvey.data = survey.data;
        },
        saveSurvey: (state, survey) => {
            state.surveys = [...state.surveys, survey.data];
        },
        updateSurvey: (state, survey) => {
            // map each survey to its own object
            // then find the survey with the same id as the one we are updating
            // and return it
            state.surveys = state.surveys.map((s) => {
                if (s.id == survey.data.id) {
                    return survey.data;
                }
                return s;
            });
        },
        logout: state => {
            state.user.data = {};
            state.user.token = null;
        },
        setUser: (state, userData) => {
            state.user.token = userData.token;
            state.user.data = userData.user;
            sessionStorage.setItem('TOKEN', userData.token);
        }
    },
    modules: {}
});

export default store;