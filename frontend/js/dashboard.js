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

    // Calcular horas por profesor
    const horasMap = {};
    asignaciones.forEach(a => {
      if (!horasMap[a.profesor_id]) {
        horasMap[a.profesor_id] = { nombre: a.profesor, puesto: a.puesto, asignadas: 0 };
      }
      horasMap[a.profesor_id].asignadas += parseFloat(a.horas);
    });

    const horasPorContrato = {};
    profesores.forEach(p => {
      horasPorContrato[p.id] = { contrato: p.horas_totales, puesto: p.puesto, nombre: `${p.apellidos}, ${p.nombre}` };
    });

    const tbody = document.getElementById('tbody-horas');
    tbody.innerHTML = profesores
      .sort((a, b) => a.apellidos.localeCompare(b.apellidos))
      .map(p => {
        const asig = horasMap[p.id]?.asignadas ?? 0;
        const libre = p.horas_totales - asig;
        return `
          <tr>
            <td>${p.apellidos}, ${p.nombre}</td>
            <td>${badgePuesto(p.puesto)}</td>
            <td>${p.horas_totales}</td>
            <td>${horasBar(asig, p.horas_totales)}</td>
            <td style="color:${libre < 0 ? '#ef4444' : libre === 0 ? '#22c55e' : 'inherit'};font-weight:500">${libre}</td>
          </tr>`;
      })
      .join('');

  } catch (err) {
    console.error('Error cargando dashboard:', err);
    document.getElementById('tbody-horas').innerHTML =
      '<tr><td colspan="5" class="table-loading">Error al cargar los datos</td></tr>';
  }
}

cargarDashboard();
