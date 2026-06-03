// Tijdelijke data — vervang dit later met een fetch() naar get_medewerkers.php
const avatarColors = [
  { bg: '#0c1e35', txt: '#85b7eb' },
  { bg: '#1a1733', txt: '#afa9ec' },
  { bg: '#051f14', txt: '#5dcaa5' },
  { bg: '#2a1c05', txt: '#ef9f27' },
  { bg: '#0f2209', txt: '#97c459' },
  { bg: '#1e0b0c', txt: '#f09595' },
  { bg: '#1f0a12', txt: '#ed93b1' },
];

const afdelingClass = {
  'Artistiek':  'badge-artistiek',
  'Techniek':   'badge-techniek',
  'Productie':  'badge-productie',
  'Kostuums':   'badge-kostuums',
  'Marketing':  'badge-marketing',
};

const medewerkers = [
  { naam: 'Sophie de Vries',   functie: 'Regisseur',        afdeling: 'Artistiek' },
  { naam: 'Lars Bakker',       functie: 'Acteur',            afdeling: 'Artistiek' },
  { naam: 'Noor van den Berg', functie: 'Lichtontwerper',    afdeling: 'Techniek'  },
  { naam: 'Daan Janssen',      functie: 'Geluidsontwerper',  afdeling: 'Techniek'  },
  { naam: 'Emma Smit',         functie: 'Kostuumontwerper',  afdeling: 'Kostuums'  },
  { naam: 'Tim Visser',        functie: 'Productieleider',   afdeling: 'Productie' },
  { naam: 'Lisa Meijer',       functie: 'Marketingmanager',  afdeling: 'Marketing' },
];

function initials(naam) {
  const parts = naam.split(' ');
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
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
  tbody.innerHTML = data.map((m, i) => {
    const c = avatarColors[i % avatarColors.length];
    const badgeClass = afdelingClass[m.afdeling] || 'badge-artistiek';
    return `
      <tr>
        <td>
          <div class="name-cell">
            <span class="avatar" style="background:${c.bg}; color:${c.txt};">${initials(m.naam)}</span>
            ${m.naam}
          </div>
        </td>
        <td>${m.functie}</td>
        <td><span class="badge ${badgeClass}">${m.afdeling}</span></td>
      </tr>`;
  }).join('');
}

function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  const gefilterd = medewerkers.filter(m =>
    m.naam.toLowerCase().includes(q) ||
    m.functie.toLowerCase().includes(q) ||
    m.afdeling.toLowerCase().includes(q)
  );
  renderTable(gefilterd);
}

renderTable(medewerkers);
