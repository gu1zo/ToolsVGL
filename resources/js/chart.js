
document.addEventListener("DOMContentLoaded", function () {
  var chartsConfig = [
    { id: "graficoDEX", url: "/ajax/graficos/dex", type: "normal", label: "Eventos" },
    { id: "graficoEventos", url: "/ajax/graficos/total-eventos", type: "normal", label: "Eventos" },
    { id: "graficoEventos2", url: "/ajax/graficos/total-horas", type: "hour", label: "Horas" },
    { id: "graficoEventos3", url: "/ajax/graficos/total-clientes", type: "normal", label: "Clientes" },
    { id: "graficoEventos4", url: "/ajax/graficos/tempo-medio", type: "hour", label: "Horas" },
    { id: "graficoEventos5", url: "/ajax/graficos/disponibilidade-rede", type: "normal", label: "Disponibilidade (%)" },
    { id: "graficoEventos6", url: "/ajax/graficos/forca-maior", type: "percent", label: "Força Maior(%)" },
    { id: "graficoEventos7", url: "/ajax/graficos/cronograma", type: "normal", label: "Adesão ao Cronograma (%)" }
  ];

  // Verificar se Chart.js está carregado
  if (typeof Chart === 'undefined') {
    console.error("Chart.js não carregado! Verifique se a biblioteca foi incluída no HTML.");
    return;
  }

  // Registrar o plugin ChartDataLabels, se necessário
  if (typeof ChartDataLabels !== 'undefined') {
    Chart.register(ChartDataLabels);
  } else {
    console.error("ChartDataLabels não carregado!");
  }

  // Obter dados para os gráficos
  chartsConfig.forEach(function (config) {
    $.ajax({
      url: config.url,
      method: 'GET',
      dataType: 'json',
      success: function (data) {

        // Garantir que o canvas exista
        var canvas = document.getElementById(config.id);
        if (canvas) {
          if (data && data.labels && data.datasets) {
            createChart(config.id, config.label, data.labels, data.datasets, config.type);
          } else {
            console.error("Dados inválidos recebidos para o gráfico " + config.id);
          }
        } else {
          console.error("Canvas " + config.id + " não encontrado!");
        }
      },
      error: function (xhr, status, error) {
        console.error("Erro ao obter os dados de " + config.url + ":", error);
      }
    });
  });
});

// Função para gerar cores
function getBorderColor(index) {
  var colors = ['#e74c3c', '#3498db', '#2ecc71', '#f1c40f'];
  return colors[index % colors.length];
}

// Formatar valores com base no tipo
function formatValue(value, type) {
  switch (type) {
    case "percent": return value + "%";  
    case "hour": return value + "h";
    case "time":
      var hours = Math.floor(value);
      var minutes = Math.round((value - hours) * 60);
      return String(hours).padStart(2, '0') + ":" + String(minutes).padStart(2, '0');
    case "currency": return "R$ " + value.toFixed(2).replace('.', ',');
    default: return value;
  }
}

// Função genérica para criar gráficos
function createChart(canvasId, label, labels, datasets, formatType) {
  var ctx = document.getElementById(canvasId).getContext('2d');
  if (!ctx) {
    console.error("Erro ao obter o contexto do canvas " + canvasId + "!");
    return;
  }

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: datasets.map(function(dataset, index) {
        return {
          label: dataset.label || 'Dataset ' + (index + 1), // Adiciona um rótulo para cada dataset
          data: dataset.data,
          borderColor: getBorderColor(index),
          backgroundColor: getBorderColor(index) + '33',
          fill: false,
          borderWidth: 3,
          pointBackgroundColor: '#fff',
          pointBorderColor: getBorderColor(index),
          pointRadius: 6,
          tension: 0
        };
      })
    },
    options: {
      responsive: true,
      plugins: {
        legend: { 
          display: true, 
          labels: { font: { size: 14, weight: 'bold' }, color: '#333' }
        },
        datalabels: {
          display: true,
          color: '#000',
          font: { size: 15, weight: 'bold' },
          anchor: 'end',
          align: 'top',
          offset: 0,
          formatter: function(value) { return formatValue(value, formatType); }
        }
      },
      scales: {
        x: { 
          title: { display: true, text: 'Meses', font: { size: 14, weight: 'bold' }, color: '#333' },
          grid: { display: true, borderColor: '#ddd' }
        },
        y: { 
          title: { display: true, text: label, font: { size: 14, weight: 'bold' }, color: '#333' },
          ticks: {
            callback: function(value) { return formatValue(value, formatType); },
            font: { size: 12, weight: 'bold' },
            color: '#333'
          },
          grid: { display: true, borderColor: '#ddd' }
        }
      }
    }
  });
}
