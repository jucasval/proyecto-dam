// js/grupos.js

let todosGrupos  = [];
let todosModulos = [];
let grupoActivoId = null;
let modulosAsignados = [];

async function cargarGrupos() {
  try {
    [todosGrupos, todosModulos] = await Promise.all([
      api.get('grupos'),
      api.get('modulos'),
    ]);
    renderTabla(todosGrupos);
  } catch (err) {
    document.getElementById('tbody-grupos').innerHTML =
      '<tr><td colspan="6" class="table-loading">Error al cargar datos</td></tr>';
  }
}

const CICLO_COLORS = {
  DAM:  { bg: '#eff6ff', color: '#1d4ed8', border: '#bfdbfe' },
  DAW:  { bg: '#f0fdf4', color: '#166534', border: '#bbf7d0' },
  ASIR: { bg: '#fef9c3', color: '#854d0e', border: '#fde68a' },
  SMR:  { bg: '#fdf4ff', color: '#7e22ce', border: '#e9d5ff' },
  FPB:  { bg: '#fff7ed', color: '#9a3412', border: '#fed7aa' },
  ESO:  { bg: '#f0f9ff', color: '#0c4a6e', border: '#bae6fd' },
  BTO:  { bg: '#fef2f2', color: '#991b1b', border: '#fecaca' },
};

function badgeCiclo(ciclo) {
  const s = CICLO_COLORS[ciclo] || { bg: '#f1f5f9', color: '#334155', border: '#e2e8f0' };
  return `<span class="badge" style="background:${s.bg};color:${s.color};border-color:${s.border}">${ciclo}</span>`;
}

function renderTabla(lista) {
  const tbody = document.getElementById('tbody-grupos');
  if (!lista.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Sin resultados</td></tr>';
    return;
  }
  tbody.innerHTML = lista
    .sort((a, b) => a.ciclo.localeCompare(b.ciclo) || a.curso - b.curso || a.nombre.localeCompare(b.nombre))
    .map(g => `
      <tr>
        <td><strong>${g.nombre}</strong></td>
        <td>${badgeCiclo(g.ciclo)}</td>
        <td>${g.curso}º</td>
        <td>${g.modalidad}</td>
        <td>
          <button class="btn btn-secondary btn-sm" onclick="abrirModalModulos(${g.id}, '${g.nombre}')">Módulos</button>
        </td>
        <td>
          <button class="btn btn-secondary btn-sm" onclick="abrirModalEditar(${g.id})">Editar</button>
          <button class="btn btn-danger btn-sm"    onclick="eliminarGrupo(${g.id})">Eliminar</button>
        </td>
      </tr>`)
    .join('');
}

function aplicarFiltros() {
  const q     = document.getElementById('buscador').value.toLowerCase();
  const ciclo = document.getElementById('filtro-ciclo').value;
  renderTabla(todosGrupos.filter(g =>
    g.nombre.toLowerCase().includes(q) &&
    (ciclo === '' || g.ciclo === ciclo)
  ));
}

document.getElementById('buscador').addEventListener('input', aplicarFiltros);
document.getElementById('filtro-ciclo').addEventListener('change', aplicarFiltros);

// ---- Modal módulos del grupo ---------------------------

async function abrirModalModulos(grupoId, grupoNombre) {
  grupoActivoId = grupoId;
  document.getElementById('titulo-modulos').textContent = `Módulos — ${grupoNombre}`;
  document.getElementById('alert-modulos').classList.remove('show');
  openModal('modal-modulos');
  await cargarModulosGrupo();
}

async function cargarModulosGrupo() {
  try {
    modulosAsignados = await api.get(`grupos/${grupoActivoId}/modulos`);
    renderModulosAsignados();
    renderModulosDisponibles();
  } catch (err) {
    console.error(err);
  }
}

function renderModulosAsignados() {
  const tbody = document.getElementById('tbody-modulos-asignados');
  if (!modulosAsignados.length) {
    tbody.innerHTML = '<tr><td class="table-loading">Sin módulos</td></tr>';
    return;
  }
  tbody.innerHTML = modulosAsignados.map(m => `
    <tr>
      <td>
        <div style="font-size:13px">${m.nombre}</div>
        ${m.codigo ? `<span style="font-size:11px;font-family:var(--font-mono);color:var(--text-muted)">${m.codigo}</span>` : ''}
      </td>
      <td style="width:60px;text-align:right">
        <button class="btn btn-danger btn-sm" onclick="quitarModulo(${m.id})" title="Quitar">✕</button>
      </td>
    </tr>`).join('');
}

function renderModulosDisponibles(filtro = '') {
  const asignadosIds = new Set(modulosAsignados.map(m => m.id));
  const disponibles  = todosModulos.filter(m =>
    !asignadosIds.has(m.id) &&
    (filtro === '' || m.nombre.toLowerCase().includes(filtro) || (m.codigo && m.codigo.toLowerCase().includes(filtro)))
  );

  const tbody = document.getElementById('tbody-modulos-disponibles');
  if (!disponibles.length) {
    tbody.innerHTML = '<tr><td class="table-loading">Sin módulos disponibles</td></tr>';
    return;
  }
  tbody.innerHTML = disponibles
    .sort((a, b) => a.nombre.localeCompare(b.nombre))
    .map(m => `
      <tr>
        <td>
          <div style="font-size:13px">${m.nombre}</div>
          ${m.codigo ? `<span style="font-size:11px;font-family:var(--font-mono);color:var(--text-muted)">${m.codigo}</span>` : ''}
        </td>
        <td style="width:60px;text-align:right">
          <button class="btn btn-primary btn-sm" onclick="añadirModulo(${m.id})" title="Añadir">+</button>
        </td>
      </tr>`).join('');
}

document.getElementById('buscar-modulo-disponible').addEventListener('input', function () {
  renderModulosDisponibles(this.value.toLowerCase());
});

async function añadirModulo(moduloId) {
  try {
    await fetch(`${API_BASE}/grupos/${grupoActivoId}/modulos`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ modulo_id: moduloId }),
    });
    await cargarModulosGrupo();
    showAlert('Módulo añadido al grupo.', 'success', 'alert-modulos');
  } catch (err) {
    showAlert('Error al añadir el módulo.', 'error', 'alert-modulos');
  }
}

async function quitarModulo(moduloId) {
  if (!confirmar('¿Quitar este módulo del grupo?')) return;
  try {
    const res = await fetch(`${API_BASE}/grupos/${grupoActivoId}/modulos/${moduloId}`, {
      method: 'DELETE',
    });
    const data = await res.json();
    if (!res.ok) {
      showAlert(data.error || 'Error al quitar el módulo.', 'error', 'alert-modulos');
      return;
    }
    await cargarModulosGrupo();
    showAlert('Módulo quitado del grupo.', 'success', 'alert-modulos');
  } catch (err) {
    showAlert('Error al quitar el módulo.', 'error', 'alert-modulos');
  }
}

// ---- Modal editar grupo --------------------------------

function abrirModalNuevo() {
  document.getElementById('modal-titulo').textContent = 'Nuevo grupo';
  document.getElementById('grupo-id').value       = '';
  document.getElementById('grupo-nombre').value   = '';
  document.getElementById('grupo-ciclo').value    = 'DAM';
  document.getElementById('grupo-curso').value    = '1';
  document.getElementById('grupo-modalidad').value = 'Presencial';
  openModal();
}

function abrirModalEditar(id) {
  const g = todosGrupos.find(x => x.id == id);
  if (!g) return;
  document.getElementById('modal-titulo').textContent = 'Editar grupo';
  document.getElementById('grupo-id').value       = g.id;
  document.getElementById('grupo-nombre').value   = g.nombre;
  document.getElementById('grupo-ciclo').value    = g.ciclo;
  document.getElementById('grupo-curso').value    = g.curso;
  document.getElementById('grupo-modalidad').value = g.modalidad;
  openModal();
}

async function guardarGrupo() {
  const id   = document.getElementById('grupo-id').value;
  const data = {
    nombre:    document.getElementById('grupo-nombre').value.trim(),
    ciclo:     document.getElementById('grupo-ciclo').value,
    curso:     parseInt(document.getElementById('grupo-curso').value),
    modalidad: document.getElementById('grupo-modalidad').value,
  };

  if (!data.nombre) {
    showAlert('El nombre del grupo es obligatorio.', 'error');
    return;
  }

  try {
    if (id) {
      await api.put('grupos', id, data);
      showAlert('Grupo actualizado correctamente.');
    } else {
      await api.post('grupos', data);
      showAlert('Grupo creado correctamente.');
    }
    closeModal();
    cargarGrupos();
  } catch (err) {
    showAlert(err.error || 'Error al guardar.', 'error');
  }
}

async function eliminarGrupo(id) {
  if (!confirmar('¿Eliminar este grupo? Se perderán sus asignaciones.')) return;
  try {
    await api.delete('grupos', id);
    showAlert('Grupo eliminado.');
    cargarGrupos();
  } catch (err) {
    showAlert(err.error || 'Error al eliminar.', 'error');
  }
}

cargarGrupos();
