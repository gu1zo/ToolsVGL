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

function updateRamalAlert(ramais, active) {
  const el = document.getElementById("ramalAlert");

  if (active && ramais && ramais.length > 0) {
    el.innerHTML = ramais
      .map((r) => `<div class="ramal-item">${r}</div>`)
      .join("");

    el.classList.add("active");
  } else {
    el.classList.remove("active");
    el.innerHTML = "";
  }
}
function fetchDashboardData() {
  return fetch(`${API_URL}?queue=${encodeURIComponent(queue)}`, {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      // 🔵 Atualizações normais
      updateCircle("callsWaiting", data.calls_waiting, data.agents_logged);
      updateCircle("agentsOnCall", data.agents_on_call, data.agents_logged);
      updateCircle(
        "agentsAvailable",
        data.agents_available,
        data.agents_logged,
        true,
      );

      updateValue("callsOffered", data.calls_offered);
      updateValue("callsAnswered", data.calls_answered);
      updateValue("callsLost", data.calls_lost);
      updateLostCalls(data.calls_lost);

      const currentCalls = data.calls_waiting;
      const ramais = data.ramais;

      if (currentCalls > 0) {
        toggleScreenAlert(true);
        updateRamalAlert(ramais, true);

        if (currentCalls > lastCallsWaiting) {
          alertSound.currentTime = 0;
          alertSound.play().catch((e) => console.error(e));
        }

        alertActive = true;
      } else {
        toggleScreenAlert(false);
        updateRamalAlert(null, false); // 👈 AQUI
        alertActive = false;
      }

      lastCallsWaiting = currentCalls;
    })
    .catch((error) => {
      console.error("Erro ao buscar dados:", error);
    });
}

function fetchDashboardLoop() {
  Promise.resolve()
    .then(() => fetchDashboardData())
    .catch((err) => {
      console.error("Erro no loop:", err);
    })
    .finally(() => {
      setTimeout(fetchDashboardLoop, 1500);
    });
}

// 👇 chama só isso
fetchDashboardLoop();
