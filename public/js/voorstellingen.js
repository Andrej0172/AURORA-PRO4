const maanden = ['jan','feb','mrt','apr','mei','jun','jul','aug','sep','okt','nov','dec'];

const locatieColors = [
  { bg: '#fdecea', txt: '#c43b2f', border: '#f5c6c2' },
  { bg: '#e8f0fe', txt: '#1a73e8', border: '#b8d0f8' },
  { bg: '#e6f4ea', txt: '#2e7d32', border: '#b7dfbc' },
  { bg: '#fff3e0', txt: '#e65100', border: '#ffcc80' },
  { bg: '#f3e5f5', txt: '#7b1fa2', border: '#ce93d8' },
  { bg: '#fce4ec', txt: '#c2185b', border: '#f48fb1' },
  { bg: '#e0f7fa', txt: '#00838f', border: '#80deea' },
];

let voorstellingen = [];
let locatieColorMap = {};
let colorIndex = 0;

function getLocatieColor(locatie) {
  if (!locatieColorMap[locatie]) {
    locatieColorMap[locatie] = locatieColors[colorIndex % locatieColors.length];
    colorIndex++;
  }
  return locatieColorMap[locatie];
}

function formatDatum(dateStr) {
  const d = new Date(dateStr);
  return {
    dag: d.getDate(),
    maand: maanden[d.getMonth()],
    jaar: d.getFullYear(),
    volledig: d.toLocaleDateString('nl-NL', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
  };
}

function renderTable(data) {
  const tbody = document.getElementById('tableBody');
  const empty = document.getElementById('emptyState');
  document.getElementById('resultCount').textContent = data.length;

  if (data.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = 'block';
    return;
  }

  empty.style.display = 'none';
  tbody.innerHTML = data.map(v => {
    const d = formatDatum(v.datum);
    const c = getLocatieColor(v.locatie);
    return `
      <tr>
        <td>${v.titel}</td>
        <td>
          <div class="datum-cell">
            <span class="datum-badge">
              <span class="datum-dag">${d.dag}</span>
              <span class="datum-maand">${d.maand}</span>
            </span>
            <span class="datum-volledig">${d.volledig}</span>
          </div>
        </td>
        <td>
          <span class="badge" style="background:${c.bg}; color:${c.txt}; border-color:${c.border};">
            ${v.locatie}
          </span>
        </td>
      </tr>`;
  }).join('');
}

function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  const gefilterd = voorstellingen.filter(v =>
    v.titel.toLowerCase().includes(q) ||
    v.datum.toLowerCase().includes(q) ||
    v.locatie.toLowerCase().includes(q)
  );
  renderTable(gefilterd);
}

fetch(dataUrl)
  .then(res => {
    if (!res.ok) throw new Error('Networkfout: ' + res.status);
    return res.json();
  })
  .then(data => {
    voorstellingen = data;
    document.getElementById('totalCount').textContent = voorstellingen.length;

    const locaties = [...new Set(voorstellingen.map(v => v.locatie))];
    document.getElementById('locatieCount').textContent = locaties.length;

    renderTable(voorstellingen);
  })
  .catch(err => {
    console.error('Fout bij ophalen voorstellingen:', err);
    const empty = document.getElementById('emptyState');
    empty.style.display = 'block';
    empty.querySelector('p').textContent = 'Kon voorstellingen niet laden.';
  });
