document.addEventListener("DOMContentLoaded", function () {
  var pieChartsConfig = [
    { id: "graficoNotas", url: "/ajax/graficos/notas" },
    { id: "graficoCSAT", url: "/ajax/graficos/csat" },
    { id: "graficoAgentesPositivo", url: "/ajax/graficos/agentesPositivo" },
    { id: "graficoAgentesNegativo", url: "/ajax/graficos/agentesNegativo" },
    { id: "graficoNotasCordialidade", url: "/ajax/graficos/notasCordialidade" },
    { id: "graficoCSATCordialidade", url: "/ajax/graficos/csatCordialidade" },
    { id: "graficoAgentesPositivoCordialidade", url: "/ajax/graficos/agentesPositivoCordialidade" },
    { id: "graficoAgentesNegativoCordialidade", url: "/ajax/graficos/agentesNegativoCordialidade" }
  ];

  var lineChartsConfig = [
    { id: "graficoAno", url: "/ajax/graficos/notasAno", yType: "logarithmic" },
    { id: "graficoMediasAno", url: "/ajax/graficos/mediaNotasAno", yType: "linear" },
    { id: "graficoAnoCordialidade", url: "/ajax/graficos/notasAnoCordialidade", yType: "logarithmic" },
    { id: "graficoMediasAnoCordialidade", url: "/ajax/graficos/mediaNotasAnoCordialidade", yType: "linear" }
  ];

  var queryParams = {};
  var searchParams = new URLSearchParams(window.location.search);
  searchParams.forEach(function(value, key) {
    queryParams[key] = value;
  });

  if (typeof Chart === 'undefined') {
    console.error("Chart.js não carregado!");
    return;
  }
  if (typeof ChartDataLabels !== 'undefined') {
    Chart.register(ChartDataLabels);
  } else {
    console.warn("ChartDataLabels não carregado!");
  }

  pieChartsConfig.forEach(function (config) {
    $.ajax({
      url: config.url,
      method: 'GET',
      dataType: 'json',
      data: queryParams,
      success: function (data) {
        var canvas = document.getElementById(config.id);
        if (canvas && data && data.labels && data.values) {
          var colors;
          if(config.id === "graficoNotas"){
            colors = ['#f5a6a6', '#f7b267', '#b0c4de', '#a6f5b5', '#76c7a6'];
          } else if(config.id === "graficoCSAT"){
            colors = ['#a6f5b5', '#a1c4e8', '#f5a6a6'];
          } else {
            colors = [
              '#a1c4e8', '#5a8fbf', '#a6f5b5', '#76c7a6',
              '#f5a6a6', '#b0c4de', '#d8e2dc', '#ffe5b4',
              '#f7b267', '#f79d84'
            ];
          }
          createPieChart(config.id, data.labels, data.values, colors);
        } else {
          console.error("Dados inválidos para o gráfico " + config.id);
        }
      },
      error: function (xhr, status, error) {
        console.error("Erro ao obter dados de " + config.url + ":", error);
      }
    });
  });

  lineChartsConfig.forEach(function (config) {
    $.ajax({
      url: config.url,
      method: 'GET',
      dataType: 'json',
      data: queryParams,
      success: function (data) {
        var canvas = document.getElementById(config.id);
        if (canvas && data && data.labels && data.datasets) {
          createLineChart(config.id, data.labels, data.datasets, config.yType);
        } else {
          console.error("Dados inválidos ou canvas não encontrado para " + config.id);
        }
      },
      error: function (xhr, status, error) {
        console.error("Erro ao buscar dados de " + config.url + ":", error);
      }
    });
  });

  function createPieChart(canvasId, labels, values, colors) {
    var ctx = document.getElementById(canvasId).getContext('2d');
    var total = values.reduce((a, b) => a + b, 0);
    var legendTextColor = '#999';

    new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'right',
            labels: {
              font: { size: 14, weight: 'bold' },
              color: legendTextColor
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                var value = context.parsed;
                var percentage = ((value / total) * 100).toFixed(1);
                return `${context.label}: ${percentage}% (${value})`;
              }
            }
          },
          datalabels: {
            color: '#000',
            font: { size: 15, weight: 'bold' },
            formatter: function(value) {
              var percentage = ((value / total) * 100).toFixed(1);
              return percentage + "%\n" + value;
            },
            anchor: 'center',
            align: 'center',
            textAlign: 'center'
          }
        }
      }
    });
  }

  function createLineChart(canvasId, labels, datasets, yType) {
    var ctx = document.getElementById(canvasId).getContext('2d');
    var isDarkMode = document.body.classList.contains('dark-mode');
    var textColor = isDarkMode ? '#ddd' : '#333';
    var gridColor = isDarkMode ? '#444' : '#ddd';

    var colors = {
      "Promotores": "#a6f5b5",
      "Neutros": "#a1c4e8",
      "Detratores": "#f5a6a6",
      "Média das Notas": "#5a8fbf"
    };

    var chartDatasets = datasets.map(function(ds) {
      var color = colors[ds.label] || '#888';
      return {
        label: ds.label,
        data: ds.data,
        borderColor: color,
        backgroundColor: color + '55',
        fill: false,
        borderWidth: 4,
        pointRadius: 7,
        pointHoverRadius: 9,
        pointBackgroundColor: color,
        tension: 0.1
      };
    });

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: chartDatasets
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            labels: {
              color: textColor,
              font: { size: 25, weight: 'bold' }
            }
          },
          tooltip: {
            bodyFont: { size: 25, weight: 'bold' },
            callbacks: {
              label: function(context) {
                return `${context.dataset.label}: ${context.parsed.y}`;
              }
            }
          },
          datalabels: {
            color: textColor,
            font: { size: 20, weight: 'bold' },
            align: 'top',
            anchor: 'end',
            formatter: function(value) {
              return value;
            }
          }
        },
        scales: {
          x: {
            title: { display: true, text: 'Meses', color: textColor, font: { size: 25, weight: 'bold' } },
            ticks: { color: textColor, font: { size: 25, weight: 'bold' } },
            grid: { color: gridColor }
          },
          y: {
            type: yType || 'linear',
            title: { display: true, text: 'Quantidade', color: textColor, font: { size: 25, weight: 'bold' } },
            ticks: {
              color: textColor,
              font: { size: 25, weight: 'bold' },
              callback: function(value) { return value; }
            },
            grid: { color: gridColor }
          }
        }
      }
    });
  }
});
