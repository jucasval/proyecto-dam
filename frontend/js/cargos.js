// js/cargos.js

let todosCargos       = [];
let todasAsigCargos   = [];
let todosProfesores   = [];

async function cargarDatos() {
  try {
    [todosCargos, todasAsigCargos, todosProfesores] = await Promise.all([
      api.get('cargos'),
      api.get('cargos/asignaciones'),
      api.get('profesores'),
    ]);
    renderCargos();
    renderAsignaciones();
    poblarSelectores();
  } catch (err) {
    console.error(err);
  }
}

// ---- Catálogo de cargos --------------------------------

function renderCargos() {
  const tbody = document.getElementById('tbody-cargos');
  if (!todosCargos.length) {
    tbody.innerHTML = '<tr><td colspan="3" class="table-loading">Sin cargos registrados</td></tr>';
    return;
  }
  tbody.innerHTML = todosCargos.map(c => `
    <tr>
      <td><strong>${c.nombre}</strong></td>
      <td><span style="font-family:var(--font-mono);font-weight:600">${c.horas}h</span></td>
      <td>
        <button class="btn btn-secondary btn-sm" onclick="abrirModalCargo(${c.id})">Editar</button>
        <button class="btn btn-danger btn-sm"    onclick="eliminarCargo(${c.id})">Eliminar</button>
      </td>
    </tr>`).join('');
}

function abrirModalCargo(id = null) {
  document.getElementById('cargo-id').value    = '';
  document.getElementById('cargo-nombre').value = '';
  document.getElementById('cargo-horas').value  = 0;

  if (id) {
    const c = todosCargos.find(x => x.id == id);
    if (!c) return;
    document.getElementById('titulo-cargo').textContent = 'Editar cargo';
    document.getElementById('cargo-id').value    = c.id;
    document.getElementById('cargo-nombre').value = c.nombre;
    document.getElementById('cargo-horas').value  = c.horas;
  } else {
    document.getElementById('titulo-cargo').textContent = 'Nuevo cargo';
  }
  openModal('modal-cargo');
}

async function guardarCargo() {
  const id   = document.getElementById('cargo-id').value;
  const data = {
    nombre: document.getElementById('cargo-nombre').value.trim(),
    horas:  parseFloat(document.getElementById('cargo-horas').value) || 0,
  };

  if (!data.nombre) { showAlert('El nombre es obligatorio.', 'error'); return; }

  try {
    if (id) {
      await api.put('cargos', id, data);
      showAlert('Cargo actualizado.');
    } else {
      await api.post('cargos', data);
      showAlert('Cargo creado.');
    }
    closeModal('modal-cargo');
    todosCargos = await api.get('cargos');
    renderCargos();
    poblarSelectores();
  } catch (err) {
    showAlert(err.error || 'Error al guardar.', 'error');
  }
}

async function eliminarCargo(id) {
  if (!confirmar('¿Eliminar este cargo?')) return;
  try {
    await api.delete('cargos', id);
    showAlert('Cargo eliminado.');
    todosCargos = await api.get('cargos');
    renderCargos();
  } catch (err) {
    showAlert(err.error || 'Error al eliminar.', 'error');
  }
}

// ---- Asignaciones de cargos ----------------------------

function renderAsignaciones() {
  const tbody = document.getElementById('tbody-asignaciones-cargo');
  if (!todasAsigCargos.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Sin asignaciones en este curso</td></tr>';
    return;
  }
  tbody.innerHTML = todasAsigCargos.map(a => `
    <tr>
      <td><strong>${a.profesor}</strong></td>
      <td>${badgePuesto(a.puesto)}</td>
      <td>${a.cargo}</td>
      <td>
        <span style="font-family:var(--font-mono);font-weight:600">${a.horas}h</span>
        ${a.horas != a.horas_defecto
          ? `<span style="font-size:11px;color:var(--text-muted);margin-left:4px">(defecto: ${a.horas_defecto}h)</span>`
          : ''}
      </td>
      <td>
        <button class="btn btn-secondary btn-sm" onclick="abrirModalAsignacion(${a.id})">Editar</button>
        <button class="btn btn-danger btn-sm"    onclick="eliminarAsignacionCargo(${a.id})">Eliminar</button>
      </td>
    </tr>`).join('');
}

function poblarSelectores() {
  const selProf = document.getElementById('asig-cargo-profesor');
  selProf.innerHTML = '<option value="">— Selecciona un profesor —</option>' +
    todosProfesores
      .sort((a, b) => a.apellidos.localeCompare(b.apellidos))
      .map(p => `<option value="${p.id}">${p.apellidos}, ${p.nombre} (${p.puesto})</option>`)
      .join('');

  const selCargo = document.getElementById('asig-cargo-cargo');
  selCargo.innerHTML = '<option value="">— Selecciona un cargo —</option>' +
    todosCargos
      .map(c => `<option value="${c.id}" data-horas="${c.horas}">${c.nombre} (${c.horas}h)</option>`)
      .join('');
}

// Autocompletar horas al elegir cargo
function autocompletarHoras() {
  const sel   = document.getElementById('asig-cargo-cargo');
  const opt   = sel.options[sel.selectedIndex];
  const horas = opt?.dataset?.horas;
  if (horas !== undefined) {
    document.getElementById('asig-cargo-horas').value = horas;
  }
}

function abrirModalAsignacion(id = null) {
  document.getElementById('asig-cargo-id').value       = '';
  document.getElementById('asig-cargo-profesor').value = '';
  document.getElementById('asig-cargo-cargo').value    = '';
  document.getElementById('asig-cargo-horas').value    = 0;

  if (id) {
    const a = todasAsigCargos.find(x => x.id == id);
    if (!a) return;
    document.getElementById('titulo-asignacion').textContent  = 'Editar asignación';
    document.getElementById('asig-cargo-id').value       = a.id;
    document.getElementById('asig-cargo-profesor').value = a.profesor_id;
    document.getElementById('asig-cargo-cargo').value    = a.cargo_id;
    document.getElementById('asig-cargo-horas').value    = a.horas;
  } else {
    document.getElementById('titulo-asignacion').textContent = 'Asignar cargo';
  }
  openModal('modal-asignacion');
}

async function guardarAsignacionCargo() {
  const id   = document.getElementById('asig-cargo-id').value;
  const data = {
    profesor_id: document.getElementById('asig-cargo-profesor').value,
    cargo_id:    document.getElementById('asig-cargo-cargo').value,
    horas:       parseFloat(document.getElementById('asig-cargo-horas').value) || 0,
  };

  if (!data.profesor_id || !data.cargo_id) {
    showAlert('Profesor y cargo son obligatorios.', 'error');
    return;
  }

  try {
    if (id) {
      await fetch(`${API_BASE}/cargos/asignaciones/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      showAlert('Asignación actualizada.');
    } else {
      await fetch(`${API_BASE}/cargos/asignaciones`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      showAlert('Cargo asignado.');
    }
    closeModal('modal-asignacion');
    todasAsigCargos = await api.get('cargos/asignaciones');
    renderAsignaciones();
  } catch (err) {
    showAlert('Error al guardar.', 'error');
  }
}

async function eliminarAsignacionCargo(id) {
  if (!confirmar('¿Eliminar esta asignación de cargo?')) return;
  try {
    await fetch(`${API_BASE}/cargos/asignaciones/${id}`, { method: 'DELETE' });
    showAlert('Asignación eliminada.');
    todasAsigCargos = await api.get('cargos/asignaciones');
    renderAsignaciones();
  } catch (err) {
    showAlert('Error al eliminar.', 'error');
  }
}

cargarDatos();
