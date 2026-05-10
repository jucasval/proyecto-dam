// js/api.js — Funciones genéricas para consumir la API REST

const API_BASE = '/api';  // Ajusta si tu servidor tiene otra ruta base

const api = {

  async get(recurso, id = null) {
    const url = id ? `${API_BASE}/${recurso}/${id}` : `${API_BASE}/${recurso}`;
    const res = await fetch(url);
    if (!res.ok) throw await res.json();
    return res.json();
  },

  async post(recurso, data) {
    const res = await fetch(`${API_BASE}/${recurso}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!res.ok) throw await res.json();
    return res.json();
  },

  async put(recurso, id, data) {
    const res = await fetch(`${API_BASE}/${recurso}/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!res.ok) throw await res.json();
    return res.json();
  },

  async delete(recurso, id) {
    const res = await fetch(`${API_BASE}/${recurso}/${id}`, {
      method: 'DELETE',
    });
    if (!res.ok) throw await res.json();
    return res.json();
  },
};

// --- Utilidades de UI ------------------------------------

function showAlert(msg, tipo = 'success', contenedor = 'alert-box') {
  const el = document.getElementById(contenedor);
  if (!el) return;
  el.textContent = msg;
  el.className = `alert alert-${tipo} show`;
  setTimeout(() => el.classList.remove('show'), 3500);
}

function openModal(id = 'modal') {
  document.getElementById(id)?.classList.add('open');
}

function closeModal(id = 'modal') {
  document.getElementById(id)?.classList.remove('open');
}

// Cerrar modal al pulsar fuera
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});

// Cerrar modal con Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open')
      .forEach(m => m.classList.remove('open'));
  }
});

function badgePuesto(puesto) {
  return puesto === 'PES'
    ? `<span class="badge badge-pes">PES</span>`
    : `<span class="badge badge-ptfp">PTFP</span>`;
}

function horasBar(asignadas, total) {
  const pct = Math.min((asignadas / total) * 100, 100);
  const cls = asignadas > total ? 'over' : asignadas === total ? 'full' : '';
  return `
    <div class="horas-bar">
      <div class="bar-track"><div class="bar-fill ${cls}" style="width:${pct}%"></div></div>
      <span>${asignadas}/${total}</span>
    </div>`;
}

function confirmar(msg) {
  return window.confirm(msg);
}
