$(document).ready(function () {
    const map = L.map("map").setView([0, 0], 2); // Inicializa o mapa aqui
  
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "© OpenStreetMap",
    }).addTo(map);
  
    // Garante que o mapa tenha sido carregado e o tamanho tenha sido corrigido
    map.on("load", function () {
      map.invalidateSize(); // Garante que o tamanho do mapa seja atualizado
      fetchHeatData(); // Chama a função para buscar os dados de calor
    });
  
    function fetchHeatData() {
      fetch("/ajax/graficos/heatmap") // Substitua pela URL real da API
        .then((response) => response.json())
        .then((data) => {
          if (!Array.isArray(data)) {
            console.error("Dados inválidos recebidos:", data);
            return;
          }
  
          // Filtra e valida os dados de coordenadas
          const heatData = data.map((coord) => {
            const lat = parseFloat(coord.lat);
            const lng = parseFloat(coord.lng);
            const intensidade = parseFloat(coord.intensidade);
            if (isNaN(lat) || isNaN(lng)) {
              console.error("Coordenadas inválidas:", coord);
              return null;  // Filtra coordenadas inválidas
            }
            return { lat, lng, intensidade }; // Armazena os dados como objeto
          }).filter((item) => item !== null); // Filtra coordenadas inválidas
  
          if (heatData.length === 0) {
            console.error("Nenhuma coordenada válida encontrada.");
            return;
          }
  
          // Calcula o centro com base na maior densidade de pontos
          const center = calculateCenter(heatData);
          map.setView(center, 14); // Altera a posição do mapa
  
          // Adiciona a camada de calor ao mapa
          const heatArray = heatData.map(({ lat, lng, intensidade }) => [lat, lng, intensidade]);
          L.heatLayer(heatArray, { radius: 25, opacity: 0.9 }).addTo(map);
        })
        .catch((error) => console.error("Erro ao buscar dados:", error));
    }
  
    function calculateCenter(coords) {
      // Define a área de busca para a densidade, por exemplo, 0.1 graus de latitude/longitude
      const densityRadius = 0.1;  // Ajuste esse valor conforme necessário
      let maxDensity = 0;
      let center = [0, 0];
  
      // Para cada ponto de coordenada, contamos quantos pontos estão próximos a ele
      coords.forEach(({ lat, lng }) => {
        let density = 0;
  
        // Conta quantos pontos estão dentro do raio de densidade
        coords.forEach(({ lat: lat2, lng: lng2 }) => {
          const distance = getDistance(lat, lng, lat2, lng2);
          if (distance <= densityRadius) {
            density++;
          }
        });
  
        // Atualiza a maior densidade e o centro
        if (density > maxDensity) {
          maxDensity = density;
          center = [lat, lng];
        }
      });
  
      return center;
    }
  
    // Função para calcular a distância entre duas coordenadas (em quilômetros)
    function getDistance(lat1, lng1, lat2, lng2) {
      const R = 6371; // Raio da Terra em km
      const dLat = toRadians(lat2 - lat1);
      const dLng = toRadians(lng2 - lng1);
      const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      return R * c; // Distância em km
    }
  
    // Função para converter graus para radianos
    function toRadians(degrees) {
      return degrees * (Math.PI / 180);
    }
  
    // Ajuste o tamanho do mapa para que ele tenha a largura mínima dos outros containers
    $("#map").css("min-width", "100%"); // Garante que a largura do mapa seja no mínimo 100% do container
  
    // Chama a função para buscar os dados de heatmap
    fetchHeatData();
  });
