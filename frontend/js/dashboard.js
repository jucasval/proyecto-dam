// js/dashboard.js

async function cargarDashboard() {
  try {
    const [profesores, grupos, modulos, asignaciones] = await Promise.all([
      api.get('profesores'),
      api.get('grupos'),
      api.get('modulos'),
      api.get('asignaciones'),
    ]);

    document.getElementById('stat-profesores').textContent   = profesores.length;
    document.getElementById('stat-grupos').textContent       = grupos.length;
    document.getElementById('stat-modulos').textContent      = modulos.length;
    document.getElementById('stat-asignaciones').textContent = asignaciones.length;

    const horasDetalle = await api.get('profesores/horas');

    const tbody = document.getElementById('tbody-horas');
    tbody.innerHTML = horasDetalle
      .map(p => {
        const libre = parseFloat(p.horas_libres);
        const color = libre > 0 ? '#ef4444' : libre == 0 ? '#22c55e' : '#3b82f6';
        return `
          <tr>
            <td><strong>${p.profesor}</strong></td>
            <td>${badgePuesto(p.puesto)}</td>
            <td>
              ${horasBar(p.horas_asignadas, p.horas_contrato)}
              <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
                Modulos: ${p.horas_modulos}h - Cargos: ${p.horas_cargos}h
              </div>
            </td>
            <td style="color:${color};font-weight:500">
              ${libre}h
            </td>
          </tr>`;
      })
      .join('');

  } catch (err) {
    console.error('Error cargando dashboard:', err);
    document.getElementById('tbody-horas').innerHTML =
      '<tr><td colspan="4" class="table-loading">Error al cargar los datos</td></tr>';
  }
}

cargarDashboard();