const maanden = ['jan','feb','mrt','apr','mei','jun','jul','aug','sep','okt','nov','dec'];

const locatieColors = [
  { bg: '#0c1e35', txt: '#85b7eb', border: '#185fa5' },
  { bg: '#1a1733', txt: '#afa9ec', border: '#534ab7' },
  { bg: '#051f14', txt: '#5dcaa5', border: '#0f6e56' },
  { bg: '#2a1c05', txt: '#ef9f27', border: '#854f0b' },
  { bg: '#0f2209', txt: '#97c459', border: '#3b6d11' },
  { bg: '#1e0b0c', txt: '#f09595', border: '#8c2020' },
  { bg: '#1f0a12', txt: '#ed93b1', border: '#7a1f42' },
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

// Data ophalen via PHP
fetch('get_voorstellingen.php')
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
    empty.querySelector('p').textContent =
      'Kon voorstellingen niet laden. Controleer of get_voorstellingen.php bereikbaar is.';
  });
