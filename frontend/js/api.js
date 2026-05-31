// js/api.js

const API_BASE = window.location.hostname === 'localhost'
  ? '/Proyecto DAM/proyecto/api'
  : '/api';

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

document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open')
      .forEach(m => m.classList.remove('open'));
  }
});

function badgePuesto(puesto) {
  return puesto === 'PES'
    ? '<span class="badge badge-pes">PES</span>'
    : '<span class="badge badge-ptfp">PTFP</span>';
}

function horasBar(asignadas, total) {
  const a   = parseFloat(asignadas) || 0;
  const t   = parseFloat(total)     || 18;
  const pct = Math.min((a / t) * 100, 100);
  const color = a < t  ? '#ef4444'
              : a == t ? '#22c55e'
              :           '#3b82f6';
  return `
    <div class="horas-bar">
      <div class="bar-track">
        <div class="bar-fill" style="width:${pct}%;background:${color}"></div>
      </div>
      <span>${a}/${t}</span>
    </div>`;
}

function confirmar(msg) {
  return window.confirm(msg);
}
