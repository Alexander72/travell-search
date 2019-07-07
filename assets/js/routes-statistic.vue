<template>
    <div>
        <div class="card">
            <div class="card-body">
                <form v-on:submit.prevent="onSubmit">
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="originId">Origin</label>
                            <select id="originId" v-model="origin" class="form-control">
                                <option v-for="item in origins" :value="item.code">{{ item.name }}</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="destinationId">Destination</label>
                            <select id="destinationId" v-model="destination" class="form-control">
                                <option v-for="item in destinations" :value="item.code">{{ item.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-center">
                            <button class="btn btn-success">Apply</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-6 chart">
                <yearStatisticComponent :chartData="yearStatistic" :height="200" :options="chartOptions"></yearStatisticComponent>
            </div>
            <div class="col-6 chart">
                <weekStatisticComponent :chartData="weekStatistic" :height="200" :options="chartOptions"></weekStatisticComponent>
            </div>
        </div>
    </div>
</template>

<script>
    import YearStatistic from './routes-year-statistic';
    import backend from './Backend';

    export default {
        name: "routes-statistic",
        components: {
            yearStatisticComponent: YearStatistic,
            weekStatisticComponent: YearStatistic,
        },
        data() {
            return {
                origin: null,
                destination: null,
                origins: [],
                destinations: [],
                yearStatistic: {},
                weekStatistic: {},
                chartOptions: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    height: 100
                },
            };
        },
        created() {
            this.onSubmit();
            backend.getCities().then(cities => {
                this.destinations = this.origins = cities;
            });
        },
        methods: {
            onSubmit: function() {
                backend.getRouteAvgStatistic('year', this.origin, this.destination).then(data => {
                    this.yearStatistic = {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'avg flight price',
                            data: data,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }],
                    };
                });

                backend.getRouteAvgStatistic('week', this.origin, this.destination).then(data => {
                    this.weekStatistic = {
                        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                        datasets: [{
                            label: 'avg flight price',
                            data: data,
                            backgroundColor: 'rgba(235,208,67, 0.2)',
                            borderColor: 'rgb(235,208,67)',
                            borderWidth: 1
                        }],
                    };
                });
            }
        }
    }
</script>
<style scoped>
    .chart {
        height: 300px;
        max-height: 300px;
    }
</style>