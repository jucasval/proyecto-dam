// sync.js - Sistema de sincronización con Polling
// Uso: initSync('asignaciones', cargarDatos, 5000)

let syncIntervals = {};

/**
 * Inicializar sincronización automática de datos
 * @param {string} recurso - Nombre del recurso (asignaciones, profesores, etc)
 * @param {function} callbackActualizar - Función que se ejecuta cuando hay cambios
 * @param {number} intervalo - Milisegundos entre sincronizaciones (default: 5000)
 */
function initSync(recurso, callbackActualizar, intervalo = 5000) {
  // Limpiar intervalo anterior si existe
  if (syncIntervals[recurso]) {
    clearInterval(syncIntervals[recurso]);
  }

  // Primera sincronización inmediata
  sincronizar(recurso, callbackActualizar);

  // Luego cada X segundos
  syncIntervals[recurso] = setInterval(() => {
    sincronizar(recurso, callbackActualizar);
  }, intervalo);

  console.log(`✅ Sincronización de ${recurso} iniciada (cada ${intervalo}ms)`);
}

/**
 * Función interna que realiza la sincronización
 */
async function sincronizar(recurso, callback) {
  try {
    const datos = await api.get(recurso);
    
    // Comparar con datos anteriores
    const elementoDatos = document.getElementById(`_sync_${recurso}`);
    const datosAnteriores = elementoDatos ? JSON.parse(elementoDatos.textContent) : null;
    
    // Si hay cambios, actualizar y notificar
    if (!datosAnteriores || JSON.stringify(datos) !== JSON.stringify(datosAnteriores)) {
      // Guardar nuevos datos
      if (!elementoDatos) {
        const div = document.createElement('div');
        div.id = `_sync_${recurso}`;
        div.style.display = 'none';
        document.body.appendChild(div);
      }
      document.getElementById(`_sync_${recurso}`).textContent = JSON.stringify(datos);
      
      // Ejecutar callback para actualizar UI
      if (callback && typeof callback === 'function') {
        callback(datos);
      }
      
      // Notificación (opcional)
      mostrarNotificacionSync(recurso);
    }
  } catch (err) {
    console.error(`❌ Error sincronizando ${recurso}:`, err);
  }
}

/**
 * Mostrar notificación visual de sincronización
 */
function mostrarNotificacionSync(recurso) {
  // Opcional: agregar badge o animación
  const badge = document.getElementById('sync-badge');
  if (badge) {
    badge.style.display = 'inline-block';
    badge.textContent = '🔄 Actualizado';
    setTimeout(() => {
      badge.style.display = 'none';
    }, 2000);
  }
}

/**
 * Detener sincronización de un recurso
 */
function stopSync(recurso) {
  if (syncIntervals[recurso]) {
    clearInterval(syncIntervals[recurso]);
    delete syncIntervals[recurso];
    console.log(`⏹️ Sincronización de ${recurso} detenida`);
  }
}

/**
 * Detener todas las sincronizaciones
 */
function stopAllSync() {
  Object.keys(syncIntervals).forEach(recurso => stopSync(recurso));
}
