require('chart.js');
import $ from 'jquery';

const updateFrequencyInMilliseconds = 3000;
const chartLengthInSeconds = 60;
const formatTime = (date) => {return date.getHours()+':'+date.getMinutes()};
const updateMemoryUsageChart = () => {
    $.get('/admin/statistic/api', (response) => {
        let memoryUsage = response.lastUnfinishedState ? response.lastUnfinishedState.memoryUsage / 1024 / 1024 : 0;
        if(myLineChart.data.datasets[0].data.length > chartLengthInSeconds * 1000 / updateFrequencyInMilliseconds ) {
            myLineChart.data.datasets[0].data.shift();
            myLineChart.data.labels.shift();
        }
        myLineChart.data.datasets[0].data.push(memoryUsage);
        myLineChart.data.labels.push(formatTime(new Date()));
        myLineChart.update();
    });
};

setInterval(function(){
    updateMemoryUsageChart();
}, updateFrequencyInMilliseconds);

const myLineChart = new Chart($('#memoryUsageChart')[0], {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            {
                label: "Memory usage by current load process, Mb",
                data: [],
                backgroundColor: [
                    'rgba(0, 137, 132, .2)',
                ],
                borderColor: [
                    'rgba(0, 10, 130, .7)',
                ],
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true
    }
});