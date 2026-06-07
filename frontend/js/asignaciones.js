// js/asignaciones.js

let todasAsignaciones = [];
let todosProfesores   = [];
let todosGrupos       = [];
let todosModulos      = [];

async function cargarDatos() {
  try {
    [todasAsignaciones, todosProfesores, todosGrupos, todosModulos] = await Promise.all([
      api.get('asignaciones'),
      api.get('profesores'),
      api.get('grupos'),
      api.get('modulos'),
    ]);
    poblarFiltroGrupos();
    poblarSelectGrupos();
    renderTabla(todasAsignaciones);
  } catch (err) {
    document.getElementById('tbody-asignaciones').innerHTML =
      '<tr><td colspan="7" class="table-loading">Error al cargar datos</td></tr>';
  }
}

function poblarSelectGrupos() {
  const sel = document.getElementById('asig-grupo');
  sel.innerHTML = '<option value="">— Selecciona un grupo —</option>' +
    todosGrupos.sort((a, b) => a.ciclo.localeCompare(b.ciclo) || a.curso - b.curso)
      .map(g => `<option value="${g.id}">${g.nombre}</option>`).join('');
}

function poblarFiltroGrupos() {
  const sel = document.getElementById('filtro-grupo');
  sel.innerHTML = '<option value="">Todos los grupos</option>' +
    todosGrupos.sort((a, b) => a.ciclo.localeCompare(b.ciclo) || a.curso - b.curso)
      .map(g => `<option value="${g.id}">${g.nombre}</option>`).join('');
}

async function onGrupoChange() {
  const grupoId = document.getElementById('asig-grupo').value;
  const selMod  = document.getElementById('asig-modulo');
  const selProf = document.getElementById('asig-profesor');
  selMod.innerHTML = '<option value="">— Cargando módulos... —</option>';
  selMod.disabled = true;
  selProf.innerHTML = '<option value="">— Primero elige un módulo —</option>';
  selProf.disabled = true;
  document.getElementById('asig-horas').value = 0;
  document.getElementById('aviso-horas').style.display = 'none';
  if (!grupoId) return;
  try {
    const modulosGrupo = await api.get(`grupos/${grupoId}/modulos`);
    if (!modulosGrupo.length) { selMod.innerHTML = '<option value="">Sin módulos asignados</option>'; return; }
    selMod.innerHTML = '<option value="">— Selecciona un módulo —</option>' +
      modulosGrupo.sort((a, b) => a.nombre.localeCompare(b.nombre))
        .map(m => `<option value="${m.id}" data-pes="${m.horas_pes}" data-ptfp="${m.horas_ptfp}">
          ${m.nombre}${m.codigo ? ' [' + m.codigo + ']' : ''}</option>`).join('');
    selMod.disabled = false;
  } catch (err) {
    selMod.innerHTML = '<option value="">Error al cargar módulos</option>';
  }
}

function onModuloChange() {
  const selProf = document.getElementById('asig-profesor');
  selProf.innerHTML = '<option value="">— Selecciona un profesor —</option>' +
    todosProfesores.sort((a, b) => a.apellidos.localeCompare(b.apellidos))
      .map(p => `<option value="${p.id}" data-puesto="${p.puesto}">
        ${p.apellidos}, ${p.nombre} (${p.puesto})</option>`).join('');
  selProf.disabled = false;
  document.getElementById('asig-horas').value = 0;
  document.getElementById('aviso-horas').style.display = 'none';
}

function onProfesorChange() {
  const selProf = document.getElementById('asig-profesor');
  const selMod  = document.getElementById('asig-modulo');
  const opt     = selProf.options[selProf.selectedIndex];
  const modOpt  = selMod.options[selMod.selectedIndex];
  if (!opt.value || !modOpt.value) return;
  const puesto = opt.dataset.puesto;
  const horas  = puesto === 'PES' ? parseFloat(modOpt.dataset.pes) || 0 : parseFloat(modOpt.dataset.ptfp) || 0;
  document.getElementById('asig-horas').value = horas;
  mostrarAvisoHoras(parseInt(opt.value), horas);
}

function mostrarAvisoHoras(profesorId, horasNuevas) {
  const aviso      = document.getElementById('aviso-horas');
  const asigActual = document.getElementById('asig-id').value;
  const asignadas  = todasAsignaciones
    .filter(a => a.profesor_id == profesorId && a.id != asigActual)
    .reduce((s, a) => s + parseFloat(a.horas), 0);
  const despues = asignadas + parseFloat(horasNuevas || 0);
  const color = despues < 18
    ? { bg: '#fef2f2', border: '#fecaca', text: '#b91c1c' }
    : despues == 18
    ? { bg: '#f0fdf4', border: '#bbf7d0', text: '#166534' }
    : { bg: '#eff6ff', border: '#bfdbfe', text: '#1d4ed8' };
  aviso.style.cssText = `margin-top:14px;padding:10px 12px;border-radius:6px;font-size:13px;display:block;background:${color.bg};border:1px solid ${color.border};color:${color.text}`;
  aviso.innerHTML = `Ya asignadas: <strong>${asignadas}h</strong> &nbsp;·&nbsp; Esta asignación: <strong>${horasNuevas}h</strong> &nbsp;·&nbsp; Total: <strong>${despues}h</strong>`;
}

function renderTabla(lista) {
  const tbody = document.getElementById('tbody-asignaciones');
  document.getElementById('total-count').textContent = lista.length;
  if (!lista.length) { tbody.innerHTML = '<tr><td colspan="7" class="table-loading">Sin resultados</td></tr>'; return; }
  tbody.innerHTML = lista.map(a => `
    <tr>
      <td><strong>${a.profesor}</strong></td>
      <td class="hide-tablet">${badgePuesto(a.puesto)}</td>
      <td>${a.modulo}${a.codigo ? `<span style="margin-left:5px;font-size:11px;font-family:var(--font-mono);color:var(--text-muted)">[${a.codigo}]</span>` : ''}</td>
      <td class="hide-tablet">${a.grupo}</td>
      <td class="hide-mobile"><strong>${a.horas}h</strong></td>
      <td class="hide-tablet">${a.es_desdoble == 1
        ? '<span class="badge" style="background:#fdf4ff;color:#7e22ce;border:1px solid #e9d5ff">Sí</span>'
        : '<span style="color:var(--text-muted)">No</span>'}</td>
      <td>
        <button class="btn btn-secondary btn-sm" onclick="abrirModalEditar(${a.id})">Editar</button>
        <button class="btn btn-danger btn-sm"    onclick="eliminarAsignacion(${a.id})">Eliminar</button>
      </td>
    </tr>`).join('');
}

function aplicarFiltros() {
  const q     = document.getElementById('buscador').value.toLowerCase();
  const grupo = document.getElementById('filtro-grupo').value;
  const ciclo = document.getElementById('filtro-ciclo').value;
  renderTabla(todasAsignaciones.filter(a =>
    (a.profesor.toLowerCase().includes(q) || a.modulo.toLowerCase().includes(q)) &&
    (grupo === '' || a.grupo_id == grupo) &&
    (ciclo === '' || a.ciclo === ciclo)
  ));
}

document.getElementById('buscador').addEventListener('input', aplicarFiltros);
document.getElementById('filtro-grupo').addEventListener('change', aplicarFiltros);
document.getElementById('filtro-ciclo').addEventListener('change', aplicarFiltros);

function showModalError(msg) {
  let alertEl = document.getElementById('modal-alert');
  if (!alertEl) {
    alertEl = document.createElement('div');
    alertEl.id = 'modal-alert';
    alertEl.style.cssText = 'padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:14px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c';
    const modalBody = document.querySelector('#modal .modal-body');
    modalBody.insertBefore(alertEl, modalBody.firstChild);
  }
  alertEl.textContent = msg;
  alertEl.style.display = 'block';
}

function hideModalError() {
  const alertEl = document.getElementById('modal-alert');
  if (alertEl) alertEl.style.display = 'none';
}

function abrirModalNuevo() {
  document.getElementById('modal-titulo').textContent = 'Nueva asignación';
  document.getElementById('asig-id').value       = '';
  document.getElementById('asig-grupo').value    = '';
  document.getElementById('asig-modulo').value   = '';
  document.getElementById('asig-modulo').disabled  = true;
  document.getElementById('asig-profesor').value = '';
  document.getElementById('asig-profesor').disabled = true;
  document.getElementById('asig-horas').value    = 0;
  document.getElementById('asig-desdoble').value = '0';
  document.getElementById('asig-obs').value      = '';
  document.getElementById('aviso-horas').style.display = 'none';
  hideModalError();
  openModal();
}

async function abrirModalEditar(id) {
  const a = todasAsignaciones.find(x => x.id == id);
  if (!a) return;
  document.getElementById('modal-titulo').textContent = 'Editar asignación';
  document.getElementById('asig-id').value       = a.id;
  document.getElementById('asig-desdoble').value = a.es_desdoble;
  document.getElementById('asig-obs').value      = a.observaciones || '';
  hideModalError();
  poblarSelectGrupos();
  document.getElementById('asig-grupo').value = a.grupo_id;
  await onGrupoChange();
  document.getElementById('asig-modulo').value = a.modulo_id;
  onModuloChange();
  document.getElementById('asig-profesor').value = a.profesor_id;
  document.getElementById('asig-horas').value = a.horas;
  mostrarAvisoHoras(a.profesor_id, a.horas);
  openModal();
}

async function guardarAsignacion() {
  hideModalError();
  const id   = document.getElementById('asig-id').value;
  const data = {
    profesor_id:   document.getElementById('asig-profesor').value,
    modulo_id:     document.getElementById('asig-modulo').value,
    grupo_id:      document.getElementById('asig-grupo').value,
    horas:         parseFloat(document.getElementById('asig-horas').value),
    es_desdoble:   parseInt(document.getElementById('asig-desdoble').value),
    observaciones: document.getElementById('asig-obs').value.trim() || null,
  };
  if (!data.profesor_id || !data.modulo_id || !data.grupo_id) {
    showModalError('Debes seleccionar grupo, módulo y profesor.');
    return;
  }
  try {
    if (id) { await api.put('asignaciones', id, data); closeModal(); showAlert('Asignación actualizada.'); }
    else     { await api.post('asignaciones', data);   closeModal(); showAlert('Asignación creada.'); }
    todasAsignaciones = await api.get('asignaciones');
    renderTabla(todasAsignaciones);
    aplicarFiltros();
  } catch (err) { showModalError(err.error || 'Error al guardar.'); }
}

async function eliminarAsignacion(id) {
  if (!confirmar('¿Eliminar esta asignación?')) return;
  try {
    await api.delete('asignaciones', id);
    showAlert('Asignación eliminada.');
    todasAsignaciones = await api.get('asignaciones');
    renderTabla(todasAsignaciones);
    aplicarFiltros();
  } catch (err) { showAlert(err.error || 'Error al eliminar.', 'error'); }
}

cargarDatos();

// ============================================================
// SINCRONIZACIÓN - Polling cada 5 segundos
// ============================================================
initSync('asignaciones', async (datosNuevos) => {
  console.log('📊 Asignaciones actualizadas desde otro dispositivo');
  todasAsignaciones = datosNuevos;
  aplicarFiltros();
}, 5000);

// Detener sincronización si el usuario se va de la página
window.addEventListener('beforeunload', () => {
  stopSync('asignaciones');
});
