import Chart from '../node_modules/chart.js/auto'

(function (Drupal, once, drupalSettings) {

  'use strict';

  Drupal.behaviors.itkProjectThemeBehavior = {
    attach: function (context, settings) {
      const chart = once('itkProjectThemeBehavior', document.getElementById('chart'));
      const chartSettings = drupalSettings['reports_project_track'];
      let datasets = [];

      if (chart) {
        chartSettings['dataset'].forEach(function(drupalData) {
          datasets.push({
              label: drupalData.chart.label,
              title: drupalData.chart.label,
              data: drupalData.plots,
              backgroundColor: drupalData.chart.color
            });
        });

        new Chart(
          chart,
          {
            type: 'bubble',
            data: {datasets: datasets},
            options: {
              scales: {
                x: {
                  position: {
                    y: chartSettings.thresholds.y
                  },
                  suggestedMin: 0,
                  suggestedMax: chartSettings.axisMax.x,
                  title: {
                    display: true,
                    align: 'start',
                    text: `${chartSettings.labels.y} (${chartSettings.thresholds.y})`,
                  },
                  ticks: {
                    maxTicksLimit: 0,
                    display: false
                  }
                },
                y: {
                  position: {
                    x: chartSettings.thresholds.x
                  },
                  suggestedMin: 0,
                  suggestedMax: chartSettings.axisMax.y,
                  title: {
                    display: true,
                    align: 'center',
                    text: `${chartSettings.labels.x} (${chartSettings.thresholds.x})`,
                  },
                  ticks: {
                    maxTicksLimit: 0,
                    display: false
                  }
                },
              },
            },
            plugins: [{
              afterDatasetsDraw: function(chart, easing) {
                // @see https://jsfiddle.net/dovvas/s4zwzp3w/
                let ctx = chart.ctx;
                ctx.fillStyle = 'rgb(0, 0, 0)';
                ctx.font = 'bold 16px Helvetica, Arial, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                chart.data.datasets.forEach(function(dataset, i) {
                  const meta = chart.getDatasetMeta(i);
                  if (meta.type === "bubble") {
                    meta.data.forEach(function(element, index) {
                      const position = element.tooltipPosition(false);
                      ctx.fillText(dataset.title, position.x, position.y - 20);
                    })
                  }
                })
              }
            }]
          }
        );
      }
    }
  };

})(Drupal, once, drupalSettings);
