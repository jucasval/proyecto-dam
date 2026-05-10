// js/grupos.js

let todosGrupos = [];
let todasAsignaciones = [];

async function cargarGrupos() {
  try {
    [todosGrupos, todasAsignaciones] = await Promise.all([
      api.get('grupos'),
      api.get('asignaciones'),
    ]);
    renderTabla(todosGrupos);
  } catch (err) {
    document.getElementById('tbody-grupos').innerHTML =
      '<tr><td colspan="6" class="table-loading">Error al cargar datos</td></tr>';
  }
}

function asignacionesDe(grupoId) {
  return todasAsignaciones.filter(a => a.grupo_id == grupoId).length;
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
        <td style="color:var(--text-secondary)">${asignacionesDe(g.id)} módulos</td>
        <td>
          <button class="btn btn-secondary btn-sm" onclick="abrirModalEditar(${g.id})">Editar</button>
          <button class="btn btn-danger btn-sm"    onclick="eliminarGrupo(${g.id})">Eliminar</button>
        </td>
      </tr>`)
    .join('');
}

// Filtros
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