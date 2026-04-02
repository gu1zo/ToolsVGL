function getStatusClass(status) {
  switch (status) {
    case "available":
      return "status-available";

    case "call":
      return "status-call";

    case "pause":
      return "status-pause";

    default:
      return "";
  }
}

function renderAgents(agents) {
  const tbody = document.getElementById("agentsTable");

  tbody.innerHTML = "";

  agents.forEach((agent) => {
    const statusClass = getStatusClass(agent.status);

    const tr = document.createElement("tr");

    tr.innerHTML = `
      <td>
        <span class="status-icon ${statusClass}"></span>
      </td>

      <td class="agent-name">
        ${agent.name}
      </td>

      <td class="pause-reason">
        ${agent.pause ?? "-"}
      </td>
    `;

    tbody.appendChild(tr);
  });
}

function fetchAgents() {
  fetch(`${API_URL}?queue=${encodeURIComponent(queue)}`)
    .then((r) => r.json())
    .then((data) => {
      renderAgents(data);
    })
    .catch((err) => {
      console.error(err);
    })
    .finally(() => {
      setTimeout(fetchAgents, 1500);
    });
}

fetchAgents();

setTimeout(
  () => {
    location.reload();
  },
  6 * 60 * 60 * 1000,
);
