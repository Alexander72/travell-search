import axios from 'axios';
import queryString from 'query-string';

export default {
    getCities() {
        return axios.get('/api/v1/geo/city').then(response => {
            return response.data;
        });
    },
    getYearStatistic(origin, destination) {
        const data = {
            origin: origin,
            destination: destination
        };
        return axios.get('/api/v1/statistic/year?'+queryString.stringify(data)).then(response => {
            return response.data;
        });
    },
    getWeekStatistic(origin, destination) {
        const data = {
            origin: origin,
            destination: destination
        };
        return axios.get('/api/v1/statistic/week?'+queryString.stringify(data)).then(response => {
            return response.data;
        });
    }
}