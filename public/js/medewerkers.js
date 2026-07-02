// Kleurenschema's voor avatar-cirkels
const avatarColors = [
  { bg: '#fdecea', txt: '#c43b2f' },
  { bg: '#e8f0fe', txt: '#1a73e8' },
  { bg: '#e6f4ea', txt: '#2e7d32' },
  { bg: '#fff3e0', txt: '#e65100' },
  { bg: '#f3e5f5', txt: '#7b1fa2' },
  { bg: '#fce4ec', txt: '#c2185b' },
  { bg: '#e0f7fa', txt: '#00838f' },
];

// CSS-klassen per afdeling voor de badge-kleuren
const afdelingClass = {
  'Artistiek':  'badge-artistiek',
  'Techniek':   'badge-techniek',
  'Productie':  'badge-productie',
  'Kostuums':   'badge-kostuums',
  'Marketing':  'badge-marketing',
};

let medewerkers = [];

// Maak initialen van een naam (bijv. "Jan Jansen" -> "JJ")
function initials(naam) {
  const parts = naam.split(' ');
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

// Teken de tabel met de meegegeven medewerkers
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
        <td class="actions-cell">
          ${m.id ? `
            <a href="${baseUrl}MedewerkersController/wijzigen/${m.id}" class="btn-action btn-edit" title="Wijzigen">
              <i class="ti ti-edit"></i>
            </a>
            <form action="${baseUrl}MedewerkersController/verwijderen/${m.id}" method="POST" style="display:inline;" onsubmit="return confirm('Weet u zeker dat u deze medewerker wilt verwijderen?');">
              <button type="submit" class="btn-action btn-delete" title="Verwijderen">
                <i class="ti ti-trash"></i>
              </button>
            </form>
          ` : `<span class="text-muted">-</span>`}
        </td>
      </tr>`;
  }).join('');
}

// Filter medewerkers op zoekterm (naam, functie, afdeling)
function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  const gefilterd = medewerkers.filter(m =>
    m.naam.toLowerCase().includes(q) ||
    m.functie.toLowerCase().includes(q) ||
    m.afdeling.toLowerCase().includes(q)
  );
  renderTable(gefilterd);
}

// Haal medewerkers op van de server en toon ze in de tabel
fetch(dataUrl)
  .then(res => {
    if (!res.ok) throw new Error('Networkfout: ' + res.status);
    return res.json();
  })
  .then(data => {
    if (data.error) {
      throw new Error(data.error);
    }
    medewerkers = data;
    document.getElementById('totalCount').textContent = medewerkers.length;

    const afdelingen = [...new Set(medewerkers.map(m => m.afdeling))];
    document.getElementById('afdelingCount').textContent = afdelingen.length;

    renderTable(medewerkers);
  })
  .catch(err => {
    // Toon foutmelding in de tabel bij server-/netwerkfout
    console.error('Fout bij ophalen medewerkers:', err);
    const empty = document.getElementById('emptyState');
    empty.style.display = 'block';
    empty.querySelector('p').textContent = 'Medewerkers konden niet worden geladen. Probeer opnieuw.';
  });
