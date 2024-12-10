import Chart from '../node_modules/chart.js/auto'

(function (Drupal, once, drupalSettings) {

  'use strict';

  Drupal.behaviors.itkProjectThemeBehavior = {
    attach: function (context, settings) {
      const chart = once('itkProjectThemeBehavior', document.getElementById('chart'));
      const chartSettings = drupalSettings['reports_project_track'];
      if (chart && chartSettings) {
        const datasets = chartSettings['dataset'].map(drupalData => {
          return {
            label: drupalData.chart.label,
            title: drupalData.chart.label,
            data: drupalData.plots,
            backgroundColor: drupalData.chart.color,
          }
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
          }
        );
      }
    }
  };

})(Drupal, once, drupalSettings);
