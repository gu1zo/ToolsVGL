// altere para sua rota real

function updateValue(elementId, newValue) {
  const el = document.getElementById(elementId);

  if (el.innerText != newValue) {
    el.style.opacity = 0.3;

    setTimeout(() => {
      el.innerText = newValue;
      el.style.opacity = 1;
    }, 200);
  }
}

function updateAgentsAvailable(available, totalLogged) {
  const el = document.getElementById("agentsAvailable");

  updateValue("agentsAvailable", available);

  el.classList.add("circle-dynamic");

  if (totalLogged === 0) {
    el.style.background = "#6c757d"; // cinza (ninguém logado)
    el.style.color = "#fff";
    return;
  }

  const percentage = available / totalLogged;

  /*
    🔴 0% disponível
    🟡 até 30% disponível
    🟢 acima de 30%
  */

  if (available === 0) {
    el.style.background = "#dc3545"; // vermelho
    el.style.color = "#fff";
  } else if (percentage <= 0.3) {
    el.style.background = "#ffc107"; // amarelo
    el.style.color = "#000";
  } else {
    el.style.background = "#198754"; // verde
    el.style.color = "#fff";
  }
}
function updateCircle(id, value, maxValue, invert = false) {
  const el = document.getElementById(id);
  const span = el.querySelector("span");

  span.innerText = value;

  if (!maxValue || maxValue === 0) {
    el.style.background = `conic-gradient(#e9ecef 0deg 360deg)`;
    return;
  }

  const percentage = value / maxValue;
  const degrees = percentage * 360;

  let color = "#e9ecef";

  if (!invert) {
    // 🔴 NORMAL (quanto maior, pior)

    if (value === 0) {
      el.style.background = `conic-gradient(#e9ecef 0deg 360deg)`;
      return;
    }

    if (percentage <= 0.5) {
      color = "#ffc107"; // amarelo
    } else {
      color = "#dc3545"; // vermelho
    }

    el.style.background = `
      conic-gradient(
        ${color} 0deg ${degrees}deg,
        #e9ecef ${degrees}deg 360deg
      )
    `;
  } else {
    // 🟢 INVERTIDO (quanto maior, melhor)

    if (value === 0) {
      // 🔴 FORÇA VERMELHO TOTAL
      el.style.background = `conic-gradient(#dc3545 0deg 360deg)`;
      return;
    }

    if (percentage <= 0.5) {
      color = "#ffc107"; // amarelo
    } else {
      color = "#198754"; // verde
    }

    el.style.background = `
      conic-gradient(
        ${color} 0deg ${degrees}deg,
        #e9ecef ${degrees}deg 360deg
      )
    `;
  }
}
function updateLostCalls(value) {
  const cardEl = document.getElementById("lostCard");

  // 🔴 Fundo vermelho se > 0
  if (value > 0) {
    cardEl.style.backgroundColor = "#dc3545";
  } else {
    cardEl.style.backgroundColor = "#ffffff";
  }
}
function toggleScreenAlert(active) {
  const overlay = document.getElementById("alertOverlay");

  if (active) {
    overlay.classList.add("overlay-active");
  } else {
    overlay.classList.remove("overlay-active");
  }
}

function fetchDashboardData() {
  // 🔹 Enviando fila via query string
  fetch(`${API_URL}?queue=${encodeURIComponent(queue)}`, {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      updateCircle("callsWaiting", data.calls_waiting, data.agents_logged);

      updateCircle("agentsOnCall", data.agents_on_call, data.agents_logged);

      updateCircle(
        "agentsAvailable",
        data.agents_available,
        data.agents_logged,
        true,
      );
      // 🚨 ALERTA TELA VERMELHA SE 0 DISPONÍVEIS
      if (data.agents_available === 0 && data.agents_logged > 0) {
        toggleScreenAlert(true);
      } else {
        toggleScreenAlert(false);
      }
      updateValue("callsOffered", data.calls_offered);
      updateValue("callsAnswered", data.calls_answered);
      updateValue("callsLost", data.calls_lost);
      updateLostCalls(data.calls_lost);
    })
    .catch((error) => {
      console.error("Erro ao buscar dados:", error);
    });
}

setInterval(fetchDashboardData, 1500);

setTimeout(
  () => {
    location.reload();
  },
  30 * 60 * 1000,
);

fetchDashboardData();
