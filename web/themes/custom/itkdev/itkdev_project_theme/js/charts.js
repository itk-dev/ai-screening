import Chart from '../node_modules/chart.js/auto'
import annotationPlugin from 'chartjs-plugin-annotation';

(function (Drupal, once, drupalSettings) {

  'use strict';

  Drupal.behaviors.itkProjectThemeBehavior = {
    attach: function (context, settings) {
      Chart.register(annotationPlugin);

      const charts = once('itkProjectThemeBehavior', document.querySelectorAll('.chart'));
      charts.forEach((chart) => {
        const trackId = chart.getAttribute('track-id');
        const chartSettings = drupalSettings['reports_project_track'][trackId];

        if (charts && chartSettings) {
          let datasets = [];
          Object.entries(chartSettings.tracks).forEach(([key, data]) => {
            datasets.push({
              label: data.dataset.chart.label,
              title: data.dataset.chart.label,
              trackState: data.dataset.chart.track_state,
              data: data.dataset.plots,
              backgroundColor: data.dataset.chart.color,
              evaluationColor: data.dataset.chart.evaluation_color,
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
                      y: chartSettings.graph.thresholds.y
                    },
                    suggestedMin: 0,
                    suggestedMax: chartSettings.graph.axisMax.x,
                    title: {
                      display: false,
                    },
                    ticks: {
                      maxTicksLimit: 0,
                      display: false
                    }
                  },
                  y: {
                    position: {
                      x: chartSettings.graph.thresholds.x
                    },
                    suggestedMin: 0,
                    suggestedMax: chartSettings.graph.axisMax.y,
                    title: {
                      display: false,
                    },
                    ticks: {
                      maxTicksLimit: 0,
                      display: false
                    }
                  },
                },
                plugins: {
                  legend: {
                    display: false
                  },
                  tooltip: {
                    intersect: false,
                    callbacks: {
                      title: function (tooltipItem) {return tooltipItem[0].dataset.title},
                      label: function (tooltipItem) {return tooltipItem.dataset.trackState},
                      labelColor: function(tooltipItem) {
                        return {
                          borderColor: tooltipItem.dataset.evaluationColor,
                          backgroundColor: tooltipItem.dataset.evaluationColor,
                          borderWidth: 0,
                          borderRadius: 0,
                        };
                      },
                    }
                  },
                  annotation: {
                    annotations: {
                      refused: {
                        type: 'box',
                        xMin: 0,
                        xMax: chartSettings.graph.thresholds.x,
                        yMin: 0,
                        yMax: chartSettings.graph.thresholds.y,
                        backgroundColor: 'rgba(254, 202, 202, .3)',
                        opacity: '0'
                      },
                      undecided_1: {
                        type: 'box',
                        xMin: 0,
                        xMax: chartSettings.graph.thresholds.x,
                        yMin: chartSettings.graph.thresholds.y,
                        yMax: chartSettings.graph.axisMax.y,
                        backgroundColor: 'rgba(254, 249, 195, .3)',
                        opacity: '1'
                      },
                      undecided_2: {
                        type: 'box',
                        xMin: chartSettings.graph.thresholds.x,
                        xMax: chartSettings.graph.axisMax.x,
                        yMin: 0,
                        yMax: chartSettings.graph.thresholds.y,
                        backgroundColor: 'rgba(254, 249, 195, .3)',
                      },
                      approved: {
                        type: 'box',
                        xMin: chartSettings.graph.thresholds.x,
                        xMax: chartSettings.graph.axisMax.x,
                        yMin: chartSettings.graph.thresholds.y,
                        yMax: chartSettings.graph.axisMax.y,
                        backgroundColor: 'rgba(187, 247, 208, .3)'
                      }
                    }
                  }
                }
              },
            }
          );
        }
      });
    }
  };

})(Drupal, once, drupalSettings);
