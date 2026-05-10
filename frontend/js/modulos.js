// js/modulos.js

let todosModulos = [];

async function cargarModulos() {
  try {
    todosModulos = await api.get('modulos');
    renderTabla(todosModulos);
  } catch (err) {
    document.getElementById('tbody-modulos').innerHTML =
      '<tr><td colspan="6" class="table-loading">Error al cargar datos</td></tr>';
  }
}

function renderTabla(lista) {
  const tbody = document.getElementById('tbody-modulos');
  if (!lista.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Sin resultados</td></tr>';
    return;
  }
  tbody.innerHTML = lista
    .sort((a, b) => a.nombre.localeCompare(b.nombre))
    .map(m => {
      const total = parseFloat(m.horas_pes) + parseFloat(m.horas_ptfp);
      return `
        <tr>
          <td>${m.nombre}</td>
          <td>
            ${m.codigo
              ? `<span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;font-family:var(--font-mono)">${m.codigo}</span>`
              : '<span style="color:var(--text-muted)">—</span>'}
          </td>
          <td>${parseFloat(m.horas_pes) > 0 ? m.horas_pes + 'h' : '<span style="color:var(--text-muted)">—</span>'}</td>
          <td>${parseFloat(m.horas_ptfp) > 0 ? m.horas_ptfp + 'h' : '<span style="color:var(--text-muted)">—</span>'}</td>
          <td><strong>${total}h</strong></td>
          <td>
            <button class="btn btn-secondary btn-sm" onclick="abrirModalEditar(${m.id})">Editar</button>
            <button class="btn btn-danger btn-sm"    onclick="eliminarModulo(${m.id})">Eliminar</button>
          </td>
        </tr>`;
    })
    .join('');
}

document.getElementById('buscador').addEventListener('input', function () {
  const q = this.value.toLowerCase();
  renderTabla(todosModulos.filter(m =>
    m.nombre.toLowerCase().includes(q) ||
    (m.codigo && m.codigo.toLowerCase().includes(q))
  ));
});

function abrirModalNuevo() {
  document.getElementById('modal-titulo').textContent = 'Nuevo módulo';
  document.getElementById('mod-id').value         = '';
  document.getElementById('mod-nombre').value     = '';
  document.getElementById('mod-codigo').value     = '';
  document.getElementById('mod-horas-pes').value  = 0;
  document.getElementById('mod-horas-ptfp').value = 0;
  openModal();
}

function abrirModalEditar(id) {
  const m = todosModulos.find(x => x.id == id);
  if (!m) return;
  document.getElementById('modal-titulo').textContent = 'Editar módulo';
  document.getElementById('mod-id').value         = m.id;
  document.getElementById('mod-nombre').value     = m.nombre;
  document.getElementById('mod-codigo').value     = m.codigo || '';
  document.getElementById('mod-horas-pes').value  = m.horas_pes;
  document.getElementById('mod-horas-ptfp').value = m.horas_ptfp;
  openModal();
}

async function guardarModulo() {
  const id   = document.getElementById('mod-id').value;
  const data = {
    nombre:     document.getElementById('mod-nombre').value.trim(),
    codigo:     document.getElementById('mod-codigo').value.trim() || null,
    horas_pes:  parseFloat(document.getElementById('mod-horas-pes').value)  || 0,
    horas_ptfp: parseFloat(document.getElementById('mod-horas-ptfp').value) || 0,
  };

  if (!data.nombre) {
    showAlert('El nombre del módulo es obligatorio.', 'error');
    return;
  }

  try {
    if (id) {
      await api.put('modulos', id, data);
      showAlert('Módulo actualizado correctamente.');
    } else {
      await api.post('modulos', data);
      showAlert('Módulo creado correctamente.');
    }
    closeModal();
    cargarModulos();
  } catch (err) {
    showAlert(err.error || 'Error al guardar.', 'error');
  }
}

async function eliminarModulo(id) {
  if (!confirmar('¿Eliminar este módulo? Esta acción no se puede deshacer.')) return;
  try {
    await api.delete('modulos', id);
    showAlert('Módulo eliminado.');
    cargarModulos();
  } catch (err) {
    showAlert(err.error || 'Error al eliminar.', 'error');
  }
}

cargarModulos();
