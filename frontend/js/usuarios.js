// js/usuarios.js

let todosUsuarios = [];

async function cargarUsuarios() {
  try {
    todosUsuarios = await api.get('usuarios');
    renderTabla(todosUsuarios);
  } catch (err) {
    document.getElementById('tbody-usuarios').innerHTML =
      '<tr><td colspan="5" class="table-loading">Error al cargar datos</td></tr>';
  }
}

function renderTabla(lista) {
  const tbody = document.getElementById('tbody-usuarios');
  if (!lista.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="table-loading">Sin usuarios registrados</td></tr>';
    return;
  }
  tbody.innerHTML = lista.map(u => `
    <tr>
      <td><strong>${u.nombre}</strong></td>
      <td style="font-family:var(--font-mono);font-size:13px">${u.username}</td>
      <td class="hide-mobile">
        ${u.activo == 1
          ? '<span class="badge" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0">Activo</span>'
          : '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0">Inactivo</span>'}
      </td>
      <td class="hide-mobile" style="color:var(--text-muted);font-size:12px">
        ${new Date(u.created_at).toLocaleDateString('es-ES')}
      </td>
      <td>
        <button class="btn btn-secondary btn-sm" onclick="abrirModalEditar(${u.id})">Editar</button>
        <button class="btn btn-danger btn-sm"    onclick="eliminarUsuario(${u.id})">Eliminar</button>
      </td>
    </tr>`).join('');
}

function abrirModalNuevo() {
  document.getElementById('modal-titulo').textContent = 'Nuevo usuario';
  document.getElementById('user-id').value       = '';
  document.getElementById('user-nombre').value   = '';
  document.getElementById('user-username').value = '';
  document.getElementById('user-password').value = '';
  document.getElementById('user-activo').value   = '1';
  document.getElementById('user-username').disabled = false;
  document.getElementById('pass-hint').style.display = 'none';
  openModal();
}

function abrirModalEditar(id) {
  const u = todosUsuarios.find(x => x.id == id);
  if (!u) return;
  document.getElementById('modal-titulo').textContent = 'Editar usuario';
  document.getElementById('user-id').value       = u.id;
  document.getElementById('user-nombre').value   = u.nombre;
  document.getElementById('user-username').value = u.username;
  document.getElementById('user-password').value = '';
  document.getElementById('user-activo').value   = u.activo;
  document.getElementById('user-username').disabled = true;
  document.getElementById('pass-hint').style.display = 'inline';
  openModal();
}

async function guardarUsuario() {
  const id   = document.getElementById('user-id').value;
  const data = {
    nombre:   document.getElementById('user-nombre').value.trim(),
    username: document.getElementById('user-username').value.trim(),
    password: document.getElementById('user-password').value,
    activo:   document.getElementById('user-activo').value,
  };

  if (!data.nombre) { showAlert('El nombre es obligatorio.', 'error'); return; }
  if (!id && !data.password) { showAlert('La contrasena es obligatoria para nuevos usuarios.', 'error'); return; }
  if (data.password && data.password.length < 6) { showAlert('La contrasena debe tener al menos 6 caracteres.', 'error'); return; }

  try {
    if (id) {
      await api.put('usuarios', id, data);
      showAlert('Usuario actualizado correctamente.');
    } else {
      await api.post('usuarios', data);
      showAlert('Usuario creado correctamente.');
    }
    closeModal();
    cargarUsuarios();
  } catch (err) {
    showAlert(err.error || 'Error al guardar.', 'error');
  }
}

async function eliminarUsuario(id) {
  if (!confirmar('Eliminar este usuario? Esta accion no se puede deshacer.')) return;
  try {
    await api.delete('usuarios', id);
    showAlert('Usuario eliminado.');
    cargarUsuarios();
  } catch (err) {
    showAlert(err.error || 'Error al eliminar.', 'error');
  }
}

cargarUsuarios();
