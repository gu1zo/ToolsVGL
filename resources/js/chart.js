document.addEventListener("DOMContentLoaded", function () {
  var pieChartsConfig = [
    { id: "graficoNotas", url: "/ajax/graficos/notas" },
    { id: "graficoCSAT", url: "/ajax/graficos/csat" },
    { id: "graficoAgentesPositivo", url: "/ajax/graficos/agentesPositivo" },
    { id: "graficoAgentesNegativo", url: "/ajax/graficos/agentesNegativo" }
  ];

  // Capturar query params da página
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
      data: queryParams,  // Envia os query params junto
      success: function (data) {
        var canvas = document.getElementById(config.id);
        if (canvas) {
          if (data && data.labels && data.values) {
            // Define as cores específicas para o gráfico CSAT
            var colors = ['#a6f5b5', '#a1c4e8', '#f5a6a6']; // verde, cinza, vermelho
            if(config.id === "graficoNotas"){
              colors = ['#f5a6a6', '#f7b267', '#b0c4de', '#a6f5b5', '#76c7a6']; // paleta padrão para Notas
            } else if(config.id === "graficoCSAT"){
              colors = ['#a6f5b5', '#a1c4e8', '#f5a6a6']; // verde, cinza, vermelho
            } else {
              // Paleta maior para gráficos de agentes
              colors = [
                '#a1c4e8', // azul claro
                '#5a8fbf', // azul escuro
                '#a6f5b5', // verde claro
                '#76c7a6', // verde médio
                '#f5a6a6', // vermelho claro
                '#b0c4de', // cinza azulado
                '#d8e2dc', // cinza mais neutro
                '#ffe5b4', // bege claro
                '#f7b267', // laranja suave
                '#f79d84'  // salmão claro
              ];
            }

            createPieChart(config.id, data.labels, data.values, colors);
          } else {
            console.error("Dados inválidos para o gráfico " + config.id);
          }
        } else {
          console.error("Canvas " + config.id + " não encontrado!");
        }
      },
      error: function (xhr, status, error) {
        console.error("Erro ao obter dados de " + config.url + ":", error);
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
        anchor: 'center',   // centraliza dentro da fatia
        align: 'center',    // centraliza verticalmente
        textAlign: 'center' // garante que o texto fique bem centralizado
        }

      }
    }
  });
}

});


document.addEventListener("DOMContentLoaded", function () {
  var config = {
    id: "graficoAno",
    url: "/ajax/graficos/notasAno"
  };

  var queryParams = {};
  var searchParams = new URLSearchParams(window.location.search);
  searchParams.forEach(function(value, key) {
    queryParams[key] = value;
  });

  if (typeof Chart === 'undefined') {
    console.error("Chart.js não carregado!");
    return;
  }

  $.ajax({
    url: config.url,
    method: 'GET',
    dataType: 'json',
    data: queryParams,
    success: function (data) {
      var canvas = document.getElementById(config.id);
      if (canvas && data && data.labels && data.datasets) {
        createLineChart(config.id, data.labels, data.datasets);
      } else {
        console.error("Dados inválidos ou canvas não encontrado!");
      }
    },
    error: function (xhr, status, error) {
      console.error("Erro ao buscar dados:", error);
    }
  });

  function createLineChart(canvasId, labels, datasets) {
    var ctx = document.getElementById(canvasId).getContext('2d');
    var isDarkMode = document.body.classList.contains('dark-mode');
    var textColor = isDarkMode ? '#ddd' : '#333';
    var gridColor = isDarkMode ? '#444' : '#ddd';

    var colors = {
      "Promotores": "#a6f5b5",
      "Neutros": "#a1c4e8",
      "Detratores": "#f5a6a6"
    };

    var chartDatasets = datasets.map(function(ds) {
      return {
        label: ds.label,
        data: ds.data,
        borderColor: colors[ds.label] || '#888',
        backgroundColor: colors[ds.label] + '55',
        fill: false,
        borderWidth: 4,          // linhas mais grossas
        pointRadius: 7,          // pontos maiores
        pointHoverRadius: 9,
        pointBackgroundColor: colors[ds.label],
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
                color: textColor,                    // cor do texto nos pontos
                font: { size: 20, weight: 'bold' },  // aumenta a fonte
                align: 'top',                        // posiciona acima do ponto
                anchor: 'end',                       // ancora ao final do ponto
                formatter: function(value) {
                return value;                      // exibe o valor no ponto
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
                type: 'logarithmic',
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