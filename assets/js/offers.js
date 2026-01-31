(function(){
  const cfg = window.IBIS_OFFER_LOOKUPS || {};
  const serviceSelect = document.getElementById('serviceSelect');
  const subWrap = document.getElementById('subserviceWrap');
  const specWrap = document.getElementById('specificWrap');
  const specSelect = document.getElementById('specificSelect');

  const offerDate = document.getElementById('offerDate');
  const validityDays = document.getElementById('validityDays');
  const expiryDate = document.getElementById('expiryDate');

  const phasesTable = document.getElementById('phasesTable');
  const addPhaseBtn = document.getElementById('addPhaseBtn');

  function setSpecificOptions(service) {
    const list = (cfg.lists && cfg.lists[service]) ? cfg.lists[service] : [];
    specSelect.innerHTML = '<option value="">-- seleziona --</option>';
    list.forEach(v => {
      const o = document.createElement('option');
      o.value = v; o.textContent = v;
      if (cfg.specificSelected && cfg.specificSelected === v) o.selected = true;
      specSelect.appendChild(o);
    });
  }

  function toggleServiceFields() {
    const s = serviceSelect.value;
    if (s === 'SISTEMI DI GESTIONE AZIENDALE') {
      subWrap.classList.remove('d-none');
      specWrap.classList.add('d-none');
      specSelect.value = '';
    } else if (s) {
      subWrap.classList.add('d-none');
      specWrap.classList.remove('d-none');
      setSpecificOptions(s);
    } else {
      subWrap.classList.remove('d-none');
      specWrap.classList.add('d-none');
    }
  }

  // date helpers
  function parseDate(val) {
    if (!val) return null;
    const [y,m,d] = val.split('-').map(Number);
    if (!y || !m || !d) return null;
    return new Date(Date.UTC(y, m-1, d));
  }
  function fmtDate(dt) {
    const y = dt.getUTCFullYear();
    const m = String(dt.getUTCMonth()+1).padStart(2,'0');
    const d = String(dt.getUTCDate()).padStart(2,'0');
    return `${y}-${m}-${d}`;
  }
  function diffDays(a,b) {
    const ms = b.getTime() - a.getTime();
    return Math.round(ms / (24*3600*1000));
  }

  let lock = false;

  function updateExpiryFromValidity() {
    if (lock) return;
    const od = parseDate(offerDate.value);
    const v = parseInt(validityDays.value || '0', 10);
    if (!od || !v) return;
    lock = true;
    const ex = new Date(od.getTime() + v * 24*3600*1000);
    expiryDate.value = fmtDate(ex);
    lock = false;
  }

  function updateValidityFromExpiry() {
    if (lock) return;
    const od = parseDate(offerDate.value);
    const ex = parseDate(expiryDate.value);
    if (!od || !ex) return;
    lock = true;
    const v = diffDays(od, ex);
    validityDays.value = String(v);
    lock = false;
  }

  function ensureAtLeastOnePhaseRow() {
    const tbody = phasesTable.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    // se c'è solo la riga "nessuna fase"
    if (rows.length === 1 && rows[0].querySelector('td') && rows[0].children.length === 1) {
      tbody.innerHTML = '';
    }
  }

  function currentIndex() {
    const tbody = phasesTable.querySelector('tbody');
    return tbody.querySelectorAll('tr').length;
  }

  function addPhaseRow() {
    ensureAtLeastOnePhaseRow();
    const tbody = phasesTable.querySelector('tbody');
    const i = currentIndex();
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select class="form-select form-select-sm" name="phase[${i}][phase_type]">
          <option value="apertura">apertura</option>
          <option value="chiusura">chiusura</option>
        </select>
      </td>
      <td><input class="form-control form-control-sm" name="phase[${i}][amount_eur]" placeholder="0,00"></td>
      <td><input type="date" class="form-control form-control-sm" name="phase[${i}][planned_date]"></td>
      <td class="text-center"><input type="checkbox" class="form-check-input" name="phase[${i}][is_billable]" value="1"></td>
      <td><input class="form-control form-control-sm" name="phase[${i}][day_man_value_eur]" placeholder="0,00"></td>
      <td><input class="form-control form-control-sm" name="phase[${i}][hours]" placeholder="0"></td>
      <td><input class="form-control form-control-sm" name="phase[${i}][days]" placeholder="0"></td>
      <td><button type="button" class="btn btn-sm btn-outline-danger remove-phase">×</button></td>
    `;
    tbody.appendChild(tr);
  }

  function reindexPhases() {
    const tbody = phasesTable.querySelector('tbody');
    const trs = Array.from(tbody.querySelectorAll('tr'));
    trs.forEach((tr, idx) => {
      tr.querySelectorAll('input,select').forEach(el => {
        const name = el.getAttribute('name');
        if (!name) return;
        el.setAttribute('name', name.replace(/phase\[\d+\]/, `phase[${idx}]`));
      });
    });
    if (trs.length === 0) {
      const tr = document.createElement('tr');
      tr.className = 'text-muted';
      tr.innerHTML = `<td colspan="8">Nessuna fase inserita. Clicca “Aggiungi fase”.</td>`;
      tbody.appendChild(tr);
    }
  }

  // events
  serviceSelect && serviceSelect.addEventListener('change', toggleServiceFields);
  toggleServiceFields();

  offerDate && offerDate.addEventListener('change', () => {
    updateExpiryFromValidity();
    updateValidityFromExpiry();
  });
  validityDays && validityDays.addEventListener('input', updateExpiryFromValidity);
  expiryDate && expiryDate.addEventListener('change', updateValidityFromExpiry);

  addPhaseBtn && addPhaseBtn.addEventListener('click', addPhaseRow);

  phasesTable && phasesTable.addEventListener('click', (e) => {
    if (e.target && e.target.classList.contains('remove-phase')) {
      e.preventDefault();
      const tr = e.target.closest('tr');
      if (tr) tr.remove();
      reindexPhases();
    }
  });
})();
