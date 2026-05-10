// js/asignaciones.js

let todasAsignaciones = [];
let todosProfesores   = [];
let todosModulos      = [];
let todosGrupos       = [];

async function cargarDatos() {
  try {
    [todasAsignaciones, todosProfesores, todosModulos, todosGrupos] = await Promise.all([
      api.get('asignaciones'),
      api.get('profesores'),
      api.get('modulos'),
      api.get('grupos'),
    ]);
    poblarSelectores();
    poblarFiltroGrupos();
    renderTabla(todasAsignaciones);
  } catch (err) {
    document.getElementById('tbody-asignaciones').innerHTML =
      '<tr><td colspan="8" class="table-loading">Error al cargar datos</td></tr>';
  }
}

// ---- Selectores del modal --------------------------------

function poblarSelectores() {
  const selProf = document.getElementById('asig-profesor');
  selProf.innerHTML = '<option value="">— Selecciona un profesor —</option>' +
    todosProfesores
      .sort((a, b) => a.apellidos.localeCompare(b.apellidos))
      .map(p => `<option value="${p.id}" data-puesto="${p.puesto}">${p.apellidos}, ${p.nombre} (${p.puesto})</option>`)
      .join('');

  const selMod = document.getElementById('asig-modulo');
  selMod.innerHTML = '<option value="">— Selecciona un módulo —</option>' +
    todosModulos
      .sort((a, b) => a.nombre.localeCompare(b.nombre))
      .map(m => `<option value="${m.id}">${m.nombre}${m.codigo ? ' [' + m.codigo + ']' : ''}</option>`)
      .join('');

  const selGrupo = document.getElementById('asig-grupo');
  selGrupo.innerHTML = '<option value="">— Selecciona un grupo —</option>' +
    todosGrupos
      .sort((a, b) => a.ciclo.localeCompare(b.ciclo) || a.curso - b.curso)
      .map(g => `<option value="${g.id}">${g.nombre}</option>`)
      .join('');
}

function poblarFiltroGrupos() {
  const sel = document.getElementById('filtro-grupo');
  sel.innerHTML = '<option value="">Todos los grupos</option>' +
    todosGrupos
      .sort((a, b) => a.ciclo.localeCompare(b.ciclo) || a.curso - b.curso)
      .map(g => `<option value="${g.id}">${g.nombre}</option>`)
      .join('');
}

// Al cambiar profesor, actualiza el tipo de cuerpo y muestra aviso de horas
function actualizarTipoCuerpo() {
  const sel    = document.getElementById('asig-profesor');
  const opt    = sel.options[sel.selectedIndex];
  const puesto = opt?.dataset?.puesto;
  if (puesto) {
    document.getElementById('asig-tipo').value = puesto;
    mostrarAvisoHoras(parseInt(sel.value));
  } else {
    document.getElementById('aviso-horas').style.display = 'none';
  }
}

function mostrarAvisoHoras(profesorId) {
  const aviso = document.getElementById('aviso-horas');
  if (!profesorId) { aviso.style.display = 'none'; return; }

  const prof       = todosProfesores.find(p => p.id == profesorId);
  const asigActual = document.getElementById('asig-id').value;
  const asignadas  = todasAsignaciones
    .filter(a => a.profesor_id == profesorId && a.id != asigActual)
    .reduce((s, a) => s + parseFloat(a.horas), 0);

  const libres = prof.horas_totales - asignadas;
  const color  = libres <= 0
    ? { bg: '#fef2f2', border: '#fecaca', text: '#b91c1c' }
    : libres <= 2
    ? { bg: '#fef9c3', border: '#fde68a', text: '#854d0e' }
    : { bg: '#eff6ff', border: '#bfdbfe', text: '#1d4ed8' };

  aviso.style.cssText = `margin-top:14px;padding:10px 12px;border-radius:6px;font-size:13px;display:block;background:${color.bg};border:1px solid ${color.border};color:${color.text}`;
  aviso.textContent   = `${prof.apellidos}, ${prof.nombre} — ${asignadas}h asignadas de ${prof.horas_totales}h · Quedan ${libres}h libres`;
}

// ---- Render tabla ----------------------------------------

function renderTabla(lista) {
  const tbody = document.getElementById('tbody-asignaciones');
  document.getElementById('total-count').textContent = lista.length;

  if (!lista.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="table-loading">Sin resultados</td></tr>';
    return;
  }

  tbody.innerHTML = lista.map(a => `
    <tr>
      <td><strong>${a.profesor}</strong></td>
      <td>${badgePuesto(a.puesto)}</td>
      <td>
        ${a.modulo}
        ${a.codigo ? `<span style="margin-left:5px;font-size:11px;font-family:var(--font-mono);color:var(--text-muted)">[${a.codigo}]</span>` : ''}
      </td>
      <td>${a.grupo}</td>
      <td><strong>${a.horas}h</strong></td>
      <td>${badgePuesto(a.tipo_cuerpo)}</td>
      <td>${a.es_desdoble == 1
        ? '<span class="badge" style="background:#fdf4ff;color:#7e22ce;border:1px solid #e9d5ff">Sí</span>'
        : '<span style="color:var(--text-muted)">No</span>'}</td>
      <td>
        <button class="btn btn-secondary btn-sm" onclick="abrirModalEditar(${a.id})">Editar</button>
        <button class="btn btn-danger btn-sm"    onclick="eliminarAsignacion(${a.id})">Eliminar</button>
      </td>
    </tr>`).join('');
}

// ---- Filtros ---------------------------------------------

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

// ---- Modal -----------------------------------------------

function abrirModalNuevo() {
  document.getElementById('modal-titulo').textContent = 'Nueva asignación';
  document.getElementById('asig-id').value        = '';
  document.getElementById('asig-profesor').value  = '';
  document.getElementById('asig-modulo').value    = '';
  document.getElementById('asig-grupo').value     = '';
  document.getElementById('asig-horas').value     = 1;
  document.getElementById('asig-tipo').value      = 'PES';
  document.getElementById('asig-desdoble').value  = '0';
  document.getElementById('asig-obs').value       = '';
  document.getElementById('aviso-horas').style.display = 'none';
  openModal();
}

function abrirModalEditar(id) {
  const a = todasAsignaciones.find(x => x.id == id);
  if (!a) return;
  document.getElementById('modal-titulo').textContent = 'Editar asignación';
  document.getElementById('asig-id').value        = a.id;
  document.getElementById('asig-profesor').value  = a.profesor_id;
  document.getElementById('asig-modulo').value    = a.modulo_id;
  document.getElementById('asig-grupo').value     = a.grupo_id;
  document.getElementById('asig-horas').value     = a.horas;
  document.getElementById('asig-tipo').value      = a.tipo_cuerpo;
  document.getElementById('asig-desdoble').value  = a.es_desdoble;
  document.getElementById('asig-obs').value       = a.observaciones || '';
  mostrarAvisoHoras(a.profesor_id);
  openModal();
}

async function guardarAsignacion() {
  const id   = document.getElementById('asig-id').value;
  const data = {
    profesor_id:   document.getElementById('asig-profesor').value,
    modulo_id:     document.getElementById('asig-modulo').value,
    grupo_id:      document.getElementById('asig-grupo').value,
    horas:         parseFloat(document.getElementById('asig-horas').value),
    tipo_cuerpo:   document.getElementById('asig-tipo').value,
    es_desdoble:   parseInt(document.getElementById('asig-desdoble').value),
    observaciones: document.getElementById('asig-obs').value.trim() || null,
  };

  if (!data.profesor_id || !data.modulo_id || !data.grupo_id || !data.horas) {
    showAlert('Profesor, módulo, grupo y horas son obligatorios.', 'error');
    return;
  }

  try {
    if (id) {
      await api.put('asignaciones', id, data);
      showAlert('Asignación actualizada correctamente.');
    } else {
      await api.post('asignaciones', data);
      showAlert('Asignación creada correctamente.');
    }
    closeModal();
    // Recargar todo para que el aviso de horas sea correcto
    todasAsignaciones = await api.get('asignaciones');
    renderTabla(todasAsignaciones);
    aplicarFiltros();
  } catch (err) {
    showAlert(err.error || 'Error al guardar.', 'error');
  }
}

async function eliminarAsignacion(id) {
  if (!confirmar('¿Eliminar esta asignación?')) return;
  try {
    await api.delete('asignaciones', id);
    showAlert('Asignación eliminada.');
    todasAsignaciones = await api.get('asignaciones');
    renderTabla(todasAsignaciones);
    aplicarFiltros();
  } catch (err) {
    showAlert(err.error || 'Error al eliminar.', 'error');
  }
}

cargarDatos();
