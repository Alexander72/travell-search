require('chart.js');
import $ from 'jquery';

setInterval(function(){
    var number = Math.round(Math.random()*100);
    console.log(number);
    myLineChart.data.datasets[0].data.shift();
    myLineChart.data.labels.shift();
    myLineChart.data.datasets[0].data.push(number);
    myLineChart.data.labels.push(number);
    myLineChart.update();
}, 1000);

var myLineChart = new Chart($('#memoryUsageChart')[0], {
    type: 'line',
    data: {
        labels: ["January", "February", "March", "April", "May", "June", "July"],
        datasets: [
            {
                label: "My Second dataset",
                data: [28, 48, 40, 19, 86, 27, 90],
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