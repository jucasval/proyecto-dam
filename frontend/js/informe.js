// js/informe.js — Generacion de informes de horas por profesor

async function cargarDatosInforme() {
  const [horas, asignaciones, cargos] = await Promise.all([
    api.get('profesores/horas'),
    api.get('asignaciones'),
    api.get('cargos/asignaciones'),
  ]);
  return { horas, asignaciones, cargos };
}

// ---- Imprimir ------------------------------------------

async function imprimirInforme() {
  const { horas, asignaciones, cargos } = await cargarDatosInforme();
  const win = window.open('', '_blank');
  win.document.write(generarHTML(horas, asignaciones, cargos));
  win.document.close();
  win.focus();
  setTimeout(() => { win.print(); }, 500);
}

// ---- Exportar PDF --------------------------------------

async function exportarPDF() {
  const { horas, asignaciones, cargos } = await cargarDatosInforme();

  // Cargar jsPDF dinamicamente
  if (!window.jspdf) {
    await new Promise((res, rej) => {
      const s = document.createElement('script');
      s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
      s.onload = res; s.onerror = rej;
      document.head.appendChild(s);
    });
    await new Promise((res, rej) => {
      const s = document.createElement('script');
      s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js';
      s.onload = res; s.onerror = rej;
      document.head.appendChild(s);
    });
  }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

  const curso = document.getElementById('curso-activo-label')?.textContent || '';
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text('Informe de Horas por Profesor', 14, 15);
  doc.setFontSize(10);
  doc.setFont('helvetica', 'normal');
  doc.text(`Departamento de Informatica - Curso ${curso}`, 14, 22);

  let y = 30;

  horas.forEach(p => {
    if (y > 180) { doc.addPage(); y = 15; }

    // Cabecera profesor
    doc.setFillColor(15, 27, 45);
    doc.setTextColor(255, 255, 255);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.rect(14, y, 269, 7, 'F');
    doc.text(`${p.profesor}  (${p.puesto})`, 16, y + 5);
    doc.text(`Total: ${p.horas_asignadas}h  Libres: ${p.horas_libres}h`, 230, y + 5);
    y += 9;

    // Modulos
    const modsProf = asignaciones.filter(a => a.profesor_id == p.id);
    if (modsProf.length) {
      const rows = modsProf.map(a => [a.grupo, a.modulo, a.codigo || '-', `${a.horas}h`, a.es_desdoble == 1 ? 'Si' : 'No']);
      doc.autoTable({
        startY: y,
        head: [['Grupo', 'Modulo', 'Cod.', 'Horas', 'Desdoble']],
        body: rows,
        theme: 'striped',
        headStyles: { fillColor: [59, 130, 246], fontSize: 8 },
        bodyStyles: { fontSize: 8 },
        margin: { left: 14, right: 14 },
        tableWidth: 269,
      });
      y = doc.lastAutoTable.finalY + 3;
    }

    // Cargos
    const cargosProf = cargos.filter(c => c.profesor_id == p.id);
    if (cargosProf.length) {
      const rows = cargosProf.map(c => [c.cargo, `${c.horas}h`]);
      doc.autoTable({
        startY: y,
        head: [['Cargo', 'Horas']],
        body: rows,
        theme: 'striped',
        headStyles: { fillColor: [100, 116, 139], fontSize: 8 },
        bodyStyles: { fontSize: 8 },
        margin: { left: 14, right: 14 },
        tableWidth: 120,
      });
      y = doc.lastAutoTable.finalY + 6;
    } else {
      y += 4;
    }
  });

  doc.save(`informe_horas_${curso}.pdf`);
}

// ---- Exportar Excel ------------------------------------

async function exportarExcel() {
  const { horas, asignaciones, cargos } = await cargarDatosInforme();

  if (!window.XLSX) {
    await new Promise((res, rej) => {
      const s = document.createElement('script');
      s.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
      s.onload = res; s.onerror = rej;
      document.head.appendChild(s);
    });
  }

  const wb = XLSX.utils.book_new();
  const curso = document.getElementById('curso-activo-label')?.textContent || '';

  // Hoja 1: Resumen por profesor
  const resumen = [
    ['Informe de Horas por Profesor'],
    [`Departamento de Informatica - Curso ${curso}`],
    [],
    ['Profesor', 'Puesto', 'Horas Modulos', 'Horas Cargos', 'Total Asignadas', 'Horas Libres'],
  ];
  horas.forEach(p => {
    resumen.push([p.profesor, p.puesto, p.horas_modulos, p.horas_cargos, p.horas_asignadas, p.horas_libres]);
  });
  const ws1 = XLSX.utils.aoa_to_sheet(resumen);
  ws1['!cols'] = [{ wch: 35 }, { wch: 8 }, { wch: 16 }, { wch: 14 }, { wch: 17 }, { wch: 14 }];
  XLSX.utils.book_append_sheet(wb, ws1, 'Resumen');

  // Hoja 2: Detalle de modulos
  const detalle = [
    ['Detalle de Asignaciones por Modulo'],
    [`Curso ${curso}`],
    [],
    ['Profesor', 'Puesto', 'Grupo', 'Ciclo', 'Modulo', 'Codigo', 'Horas', 'Desdoble'],
  ];
  asignaciones.forEach(a => {
    detalle.push([a.profesor, a.puesto, a.grupo, a.ciclo, a.modulo, a.codigo || '', a.horas, a.es_desdoble == 1 ? 'Si' : 'No']);
  });
  const ws2 = XLSX.utils.aoa_to_sheet(detalle);
  ws2['!cols'] = [{ wch: 35 }, { wch: 8 }, { wch: 14 }, { wch: 8 }, { wch: 40 }, { wch: 10 }, { wch: 8 }, { wch: 10 }];
  XLSX.utils.book_append_sheet(wb, ws2, 'Modulos');

  // Hoja 3: Cargos
  const hojaCargos = [
    ['Asignacion de Cargos'],
    [`Curso ${curso}`],
    [],
    ['Profesor', 'Puesto', 'Cargo', 'Horas'],
  ];
  cargos.forEach(c => {
    hojaCargos.push([c.profesor, c.puesto, c.cargo, c.horas]);
  });
  const ws3 = XLSX.utils.aoa_to_sheet(hojaCargos);
  ws3['!cols'] = [{ wch: 35 }, { wch: 8 }, { wch: 30 }, { wch: 8 }];
  XLSX.utils.book_append_sheet(wb, ws3, 'Cargos');

  XLSX.writeFile(wb, `informe_horas_${curso}.xlsx`);
}

// ---- HTML para impresion -------------------------------

function generarHTML(horas, asignaciones, cargos) {
  const curso = document.getElementById('curso-activo-label')?.textContent || '';
  let html = `<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
  <title>Informe de Horas - ${curso}</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 20px; }
    h1 { font-size: 16px; margin-bottom: 4px; }
    h2 { font-size: 12px; color: #555; font-weight: normal; margin-bottom: 20px; }
    .profesor { margin-bottom: 20px; page-break-inside: avoid; }
    .prof-header { background: #0f1b2d; color: #fff; padding: 6px 10px; border-radius: 4px 4px 0 0; display: flex; justify-content: space-between; }
    .prof-nombre { font-weight: bold; font-size: 12px; }
    .prof-total { font-size: 11px; }
    table { width: 100%; border-collapse: collapse; margin-top: 1px; }
    th { background: #3b82f6; color: #fff; padding: 4px 8px; text-align: left; font-size: 10px; }
    th.cargo { background: #64748b; }
    td { padding: 3px 8px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
    tr:nth-child(even) td { background: #f8fafc; }
    .sin-datos { color: #94a3b8; font-style: italic; padding: 4px 8px; }
    @media print { body { margin: 10px; } }
  </style></head><body>
  <h1>Informe de Horas por Profesor</h1>
  <h2>Departamento de Informatica &mdash; Curso ${curso}</h2>`;

  horas.forEach(p => {
    const modsProf   = asignaciones.filter(a => a.profesor_id == p.id);
    const cargosProf = cargos.filter(c => c.profesor_id == p.id);
    const libre      = parseFloat(p.horas_libres);
    const colorLibre = libre > 0 ? '#ef4444' : libre == 0 ? '#22c55e' : '#3b82f6';

    html += `<div class="profesor">
      <div class="prof-header">
        <span class="prof-nombre">${p.profesor} &nbsp;&nbsp; ${p.puesto}</span>
        <span class="prof-total">Asignadas: ${p.horas_asignadas}h &nbsp;|&nbsp; <span style="color:${colorLibre}">Libres: ${libre}h</span></span>
      </div>`;

    if (modsProf.length) {
      html += `<table><thead><tr>
        <th>Grupo</th><th>Modulo</th><th>Cod.</th><th>Horas</th><th>Desdoble</th>
      </tr></thead><tbody>`;
      modsProf.forEach(a => {
        html += `<tr><td>${a.grupo}</td><td>${a.modulo}</td><td>${a.codigo || '-'}</td><td>${a.horas}h</td><td>${a.es_desdoble == 1 ? 'Si' : 'No'}</td></tr>`;
      });
      html += `</tbody></table>`;
    } else {
      html += `<p class="sin-datos">Sin modulos asignados</p>`;
    }

    if (cargosProf.length) {
      html += `<table><thead><tr><th class="cargo">Cargo</th><th class="cargo">Horas</th></tr></thead><tbody>`;
      cargosProf.forEach(c => {
        html += `<tr><td>${c.cargo}</td><td>${c.horas}h</td></tr>`;
      });
      html += `</tbody></table>`;
    }

    html += `</div>`;
  });

  html += `</body></html>`;
  return html;
}
