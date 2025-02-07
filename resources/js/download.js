document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("downloadPDF").addEventListener("click", function () {
      // Inicia a geração do PDF quando o botão for clicado
      const { jsPDF } = window.jspdf;
  
      // Seleciona todas as divs "graficos"
      const divs = document.querySelectorAll(".graficos");
  
      // Cria um novo PDF
      const pdf = new jsPDF({
        unit: "mm", // Usando milímetros como unidade
        format: "a4", // Tamanho A4
      });
  
      // Função para capturar as divs e adicioná-las ao PDF
      function captureDivs(divs, index) {
        if (index >= divs.length) {
          // Quando não houver mais divs, salve o PDF
          pdf.save("Graficos.pdf");
          return;
        }
  
        // Captura o canvas da div atual
        html2canvas(divs[index], {
          scale: 2, // Ajusta a qualidade da imagem
          scrollX: 0,
          scrollY: 0,
          windowWidth: window.innerWidth, // Usa a largura da janela
          windowHeight: window.innerHeight, // Usa a altura da janela
          x: 0, // Deslocamento no eixo X
          y: 0, // Deslocamento no eixo Y
        }).then((canvas) => {
          const imgData = canvas.toDataURL("image/png");
  
          const imgWidth = 210; // Largura da página A4 em mm
          const imgHeight = (canvas.height * imgWidth) / canvas.width; // Altura proporcional
  
          // Adiciona a imagem da div ao PDF
          pdf.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);
  
          // Se não for a última div, adiciona uma nova página no PDF
          if (index < divs.length - 1) {
            pdf.addPage();
          }
  
          // Chama a função recursivamente para capturar a próxima div
          captureDivs(divs, index + 1);
        });
      }
  
      // Inicia o processo de captura das divs, começando da primeira
      captureDivs(divs, 0);
    });
  });