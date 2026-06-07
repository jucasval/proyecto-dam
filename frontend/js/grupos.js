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
        <td class="hide-tablet">${badgeCiclo(g.ciclo)}</td>
        <td class="hide-tablet">${g.curso}º</td>
        <td class="hide-tablet">${g.modalidad}</td>
        <td>
          <button class="btn btn-secondary btn-sm" onclick="abrirModalModulos(${g.id}, '${g.nombre}')">Ver módulos</button>
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

function checkItem(valor, checked, texto, codigo) {
  return `
    <label style="display:flex;align-items:flex-start;gap:10px;padding:7px 8px;cursor:pointer;border-radius:4px;transition:background 0.1s"
           onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
      <input type="checkbox" class="check-modulo-grupo" value="${valor}"
             ${checked ? 'checked' : ''}
             style="margin-top:2px;flex-shrink:0;width:15px;height:15px;cursor:pointer;accent-color:#3b82f6">
      <span style="font-size:13px;line-height:1.4;color:#0f172a">
        ${texto}
        ${codigo ? `<span style="color:#94a3b8;font-family:monospace;font-size:11px;margin-left:4px">[${codigo}]</span>` : ''}
      </span>
    </label>`;
}

function renderCheckModulos(modulosSeleccionadosIds = []) {
  const container = document.getElementById('check-modulos-grupo');
  if (!container) return;
  container.innerHTML = todosModulos
    .sort((a, b) => a.nombre.localeCompare(b.nombre))
    .map(m => checkItem(m.id, modulosSeleccionadosIds.includes(parseInt(m.id)), m.nombre, m.codigo))
    .join('');
}

function getModulosSeleccionados() {
  return Array.from(document.querySelectorAll('.check-modulo-grupo:checked'))
    .map(cb => parseInt(cb.value));
}

function abrirModalNuevo() {
  document.getElementById('modal-titulo').textContent = 'Nuevo grupo';
  document.getElementById('grupo-id').value        = '';
  document.getElementById('grupo-nombre').value    = '';
  document.getElementById('grupo-ciclo').value     = 'DAM';
  document.getElementById('grupo-curso').value     = '1';
  document.getElementById('grupo-modalidad').value = 'Presencial';
  renderCheckModulos([]);
  openModal();
}

async function abrirModalEditar(id) {
  const g = todosGrupos.find(x => x.id == id);
  if (!g) return;
  document.getElementById('modal-titulo').textContent = 'Editar grupo';
  document.getElementById('grupo-id').value        = g.id;
  document.getElementById('grupo-nombre').value    = g.nombre;
  document.getElementById('grupo-ciclo').value     = g.ciclo;
  document.getElementById('grupo-curso').value     = g.curso;
  document.getElementById('grupo-modalidad').value = g.modalidad;
  try {
    const mods = await fetch(`${API_BASE}/grupos/${id}/modulos`).then(r => r.json());
    renderCheckModulos(mods.map(m => m.id));
  } catch {
    renderCheckModulos([]);
  }
  openModal();
}

async function guardarGrupo() {
  const id   = document.getElementById('grupo-id').value;
  const data = {
    nombre:      document.getElementById('grupo-nombre').value.trim(),
    ciclo:       document.getElementById('grupo-ciclo').value,
    curso:       parseInt(document.getElementById('grupo-curso').value),
    modalidad:   document.getElementById('grupo-modalidad').value,
    modulos_ids: getModulosSeleccionados(),
  };
  if (!data.nombre) { showAlert('El nombre del grupo es obligatorio.', 'error'); return; }
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
  if (!confirmar('¿Eliminar este grupo?')) return;
  try {
    await api.delete('grupos', id);
    showAlert('Grupo eliminado.');
    cargarGrupos();
  } catch (err) {
    showAlert(err.error || 'Error al eliminar.', 'error');
  }
}

async function abrirModalModulos(grupoId, grupoNombre) {
  grupoActivoId = grupoId;
  document.getElementById('titulo-modulos').textContent = `Plan de estudios — ${grupoNombre}`;
  document.getElementById('alert-modulos').style.display = 'none';
  document.getElementById('alert-modulos').classList.remove('show');
  cambiarTab('asignados');
  openModal('modal-modulos');
  await cargarModulosGrupo();
}

async function cargarModulosGrupo() {
  try {
    modulosAsignados = await fetch(`${API_BASE}/grupos/${grupoActivoId}/modulos`).then(r => r.json());
    renderModulosAsignados();
    renderModulosDisponibles();
    document.getElementById('count-asignados').textContent = modulosAsignados.length;
    document.getElementById('count-disponibles').textContent = 
      todosModulos.filter(m => !modulosAsignados.map(ma => ma.id).includes(parseInt(m.id))).length;
  } catch (err) { console.error(err); }
}

function cambiarTab(tab) {
  const tabAsignados = document.getElementById('tab-asignados');
  const tabDisponibles = document.getElementById('tab-disponibles');
  
  if (!tabAsignados || !tabDisponibles) {
    console.error('Elementos de tabs no encontrados');
    return;
  }
  
  tabAsignados.style.display = tab === 'asignados' ? 'block' : 'none';
  tabDisponibles.style.display = tab === 'disponibles' ? 'block' : 'none';
  
  const btns = document.querySelectorAll('.tab-btn');
  if (btns.length > 0) {
    btns.forEach((btn, idx) => {
      const isActive = (idx === 0 && tab === 'asignados') || (idx === 1 && tab === 'disponibles');
      btn.style.color = isActive ? 'var(--text-primary)' : 'var(--text-secondary)';
      btn.style.borderBottomColor = isActive ? '#3b82f6' : 'transparent';
      btn.style.fontWeight = isActive ? '600' : '500';
    });
  }
}

function renderModulosAsignados() {
  const container = document.getElementById('lista-modulos-asignados');
  if (!modulosAsignados.length) { 
    container.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);font-size:14px">No hay módulos asignados</div>'; 
    return; 
  }
  container.innerHTML = modulosAsignados
    .sort((a, b) => a.nombre.localeCompare(b.nombre))
    .map(m => `
      <div style="padding:12px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;transition:background 0.1s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <div style="flex:1;min-width:0">
          <div style="font-weight:500;color:#0f172a;font-size:13px">${m.nombre}</div>
          ${m.codigo ? `<div style="font-size:11px;font-family:monospace;color:#64748b;margin-top:2px">${m.codigo}</div>` : ''}
        </div>
        <button class="btn btn-danger btn-sm" onclick="quitarModulo(${m.id})" style="flex-shrink:0">✕ Quitar</button>
      </div>`).join('');
}

function renderModulosDisponibles(filtro = '') {
  const asignadosIds = new Set(modulosAsignados.map(m => m.id));
  const disponibles  = todosModulos.filter(m =>
    !asignadosIds.has(parseInt(m.id)) &&
    (filtro === '' || m.nombre.toLowerCase().includes(filtro) || (m.codigo && m.codigo.toLowerCase().includes(filtro)))
  );
  const container = document.getElementById('lista-modulos-disponibles');
  if (!disponibles.length) { 
    container.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);font-size:14px">No hay módulos disponibles</div>'; 
    return; 
  }
  container.innerHTML = disponibles
    .sort((a, b) => a.nombre.localeCompare(b.nombre))
    .map(m => `
      <div style="padding:12px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;transition:background 0.1s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <div style="flex:1;min-width:0">
          <div style="font-weight:500;color:#0f172a;font-size:13px">${m.nombre}</div>
          ${m.codigo ? `<div style="font-size:11px;font-family:monospace;color:#64748b;margin-top:2px">${m.codigo}</div>` : ''}
        </div>
        <button class="btn btn-primary btn-sm" onclick="anadirModulo(${m.id})" style="flex-shrink:0">+ Agregar</button>
      </div>`).join('');
}

document.addEventListener('input', function(e) {
  if (e.target.id === 'buscar-disponibles') {
    renderModulosDisponibles(e.target.value.toLowerCase());
  }
});

async function anadirModulo(moduloId) {
  try {
    const res = await fetch(`${API_BASE}/grupos/${grupoActivoId}/modulos`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ modulo_id: moduloId }),
    });
    const data = await res.json();
    if (!res.ok) { showAlert(data.error || 'Error.', 'error', 'alert-modulos'); return; }
    await cargarModulosGrupo();
    showAlert('Módulo agregado.', 'success', 'alert-modulos');
  } catch (err) { showAlert('Error al agregar.', 'error', 'alert-modulos'); }
}

async function quitarModulo(moduloId) {
  if (!confirmar('¿Quitar este módulo del grupo?')) return;
  try {
    const res = await fetch(`${API_BASE}/grupos/${grupoActivoId}/modulos/${moduloId}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) { showAlert(data.error || 'Error.', 'error', 'alert-modulos'); return; }
    await cargarModulosGrupo();
    showAlert('Módulo quitado.', 'success', 'alert-modulos');
  } catch (err) { showAlert('Error al quitar.', 'error', 'alert-modulos'); }
}

cargarGrupos();

// Sincronización automática
initSync('grupos', async (datosNuevos) => {
  console.log('👥 Grupos actualizados desde otro dispositivo');
  todosGrupos = datosNuevos;
  aplicarFiltros();
}, 5000);

window.addEventListener('beforeunload', () => stopSync('grupos'));
