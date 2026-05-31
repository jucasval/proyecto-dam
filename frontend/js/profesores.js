// js/profesores.js

let todosProfesores = [];
let horasDetalle    = [];

async function cargarProfesores() {
  try {
    [todosProfesores, horasDetalle] = await Promise.all([
      api.get('profesores'),
      api.get('profesores/horas'),
    ]);
    renderTabla(todosProfesores);
  } catch (err) {
    document.getElementById('tbody-profesores').innerHTML =
      '<tr><td colspan="5" class="table-loading">Error al cargar datos</td></tr>';
  }
}

function getHoras(profesorId) {
  return horasDetalle.find(h => h.id == profesorId) || {
    horas_modulos: 0, horas_cargos: 0,
    horas_asignadas: 0, horas_libres: 0
  };
}

function renderTabla(lista) {
  const tbody = document.getElementById('tbody-profesores');
  if (!lista.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Sin resultados</td></tr>';
    return;
  }
  tbody.innerHTML = lista
    .sort((a, b) => a.apellidos.localeCompare(b.apellidos))
    .map(p => {
      const h     = getHoras(p.id);
      const libre = h.horas_libres;
      return `
        <tr>
          <td><strong>${p.apellidos}</strong>, ${p.nombre}</td>
          <td>${badgePuesto(p.puesto)}</td>
          <td>
            ${horasBar(h.horas_asignadas, p.horas_totales)}
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
              Módulos: ${h.horas_modulos}h · Cargos: ${h.horas_cargos}h
            </div>
          </td>
          <td style="color:${libre < 0 ? '#ef4444' : libre === 0 ? '#22c55e' : 'inherit'};font-weight:600">
            ${libre}h
          </td>
          <td>
            <button class="btn btn-secondary btn-sm" onclick="abrirModalEditar(${p.id})">Editar</button>
            <button class="btn btn-danger btn-sm"    onclick="eliminarProfesor(${p.id})">Eliminar</button>
          </td>
        </tr>`;
    })
    .join('');
}

document.getElementById('buscador').addEventListener('input', function () {
  const q = this.value.toLowerCase();
  renderTabla(todosProfesores.filter(p =>
    `${p.nombre} ${p.apellidos}`.toLowerCase().includes(q)
  ));
});

function abrirModalNuevo() {
  document.getElementById('modal-titulo').textContent = 'Nuevo profesor';
  document.getElementById('prof-id').value        = '';
  document.getElementById('prof-nombre').value    = '';
  document.getElementById('prof-apellidos').value = '';
  document.getElementById('prof-puesto').value    = 'PES';
  document.getElementById('prof-horas').value     = 18;
  openModal();
}

function abrirModalEditar(id) {
  const p = todosProfesores.find(x => x.id == id);
  if (!p) return;
  document.getElementById('modal-titulo').textContent = 'Editar profesor';
  document.getElementById('prof-id').value        = p.id;
  document.getElementById('prof-nombre').value    = p.nombre;
  document.getElementById('prof-apellidos').value = p.apellidos;
  document.getElementById('prof-puesto').value    = p.puesto;
  document.getElementById('prof-horas').value     = p.horas_totales;
  openModal();
}

async function guardarProfesor() {
  const id   = document.getElementById('prof-id').value;
  const data = {
    nombre:        document.getElementById('prof-nombre').value.trim(),
    apellidos:     document.getElementById('prof-apellidos').value.trim(),
    puesto:        document.getElementById('prof-puesto').value,
    horas_totales: parseInt(document.getElementById('prof-horas').value),
  };

  if (!data.nombre || !data.apellidos) {
    showAlert('Nombre y apellidos son obligatorios.', 'error');
    return;
  }

  try {
    if (id) {
      await api.put('profesores', id, data);
      showAlert('Profesor actualizado correctamente.');
    } else {
      await api.post('profesores', data);
      showAlert('Profesor creado correctamente.');
    }
    closeModal();
    cargarProfesores();
  } catch (err) {
    showAlert(err.error || 'Error al guardar.', 'error');
  }
}

async function eliminarProfesor(id) {
  if (!confirmar('¿Eliminar este profesor? Esta acción no se puede deshacer.')) return;
  try {
    await api.delete('profesores', id);
    showAlert('Profesor eliminado.');
    cargarProfesores();
  } catch (err) {
    showAlert(err.error || 'Error al eliminar.', 'error');
  }
}

cargarProfesores();
