import axios from 'axios';
import queryString from 'query-string';

export default {
    getCities() {
        return axios.get('/api/v1/geo/city').then(response => {
            return response.data;
        });
    },
    getRouteAvgStatistic(periodType, origin, destination) {
        const data = {
            periodType: periodType,
            origin: origin,
            destination: destination
        };
        return axios.get('/api/v1/statistic/route_avg_price?'+queryString.stringify(data)).then(response => {
            return response.data.map(item => item.price);
        });
    },
}