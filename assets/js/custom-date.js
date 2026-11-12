/* ═══════════════════════════════════════════════════════════════
   ATLAS FIT — Minimalist Bento Custom Date Picker Engine
   Reemplaza el selector nativo de <input type="date"> para evitar
   desbordamientos y mantener una estética minimalista coherente.
   ═══════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  if (window.__xpCustomDateInitialized) return;
  window.__xpCustomDateInitialized = true;

  const MESES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ];
  const MESES_CORTOS = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
  const DIAS_SEM = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do'];

  const registry = new WeakMap();
  let backdrop = null;
  let popover = null;
  let activeInput = null;
  let viewYear = 2026;
  let viewMonth = 0; // 0..11
  let mode = 'days'; // 'days' | 'months'

  function pad2(n) {
    return n < 10 ? '0' + n : String(n);
  }

  function parseIso(iso) {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(iso || '').trim());
    if (!m) return null;
    const y = parseInt(m[1], 10);
    const mo = parseInt(m[2], 10) - 1;
    const d = parseInt(m[3], 10);
    const dt = new Date(y, mo, d);
    if (dt.getFullYear() !== y || dt.getMonth() !== mo || dt.getDate() !== d) return null;
    return dt;
  }

  function formatIso(y, mo, d) {
    return y + '-' + pad2(mo + 1) + '-' + pad2(d);
  }

  function formatDisplay(iso) {
    const dt = parseIso(iso);
    if (!dt) return 'Seleccionar';
    return pad2(dt.getDate()) + '/' + pad2(dt.getMonth() + 1) + '/' + dt.getFullYear();
  }

  function createSvg(pathD) {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('width', '15');
    svg.setAttribute('height', '15');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '2.1');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');
    const p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    p.setAttribute('d', pathD);
    svg.appendChild(p);
    return svg;
  }

  function ensurePopover() {
    if (popover) return;
    backdrop = document.createElement('div');
    backdrop.className = 'xp-cd-backdrop';
    backdrop.setAttribute('role', 'presentation');

    popover = document.createElement('div');
    popover.className = 'xp-cd-popover';
    popover.setAttribute('role', 'dialog');
    popover.setAttribute('aria-modal', 'true');
    popover.setAttribute('aria-label', 'Seleccionar fecha');

    backdrop.appendChild(popover);
    document.body.appendChild(backdrop);

    backdrop.addEventListener('mousedown', function (e) {
      if (e.target === backdrop) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && activeInput) close();
    });
    window.addEventListener('resize', function () {
      if (activeInput) positionPopover();
    }, { passive: true });
  }

  function syncTrigger(input) {
    const inst = registry.get(input);
    if (!inst) return;
    const hasVal = !!parseIso(input.value);
    inst.valEl.textContent = hasVal ? formatDisplay(input.value) : (input.getAttribute('placeholder') || 'Seleccionar');
    inst.trigger.classList.toggle('is-placeholder', !hasVal);
  }

  function selectIsoDate(iso) {
    if (!activeInput) return;
    const inp = activeInput;
    const changed = inp.value !== iso;
    inp.value = iso;
    syncTrigger(inp);
    close();
    if (changed) {
      inp.dispatchEvent(new Event('input', { bubbles: true }));
      inp.dispatchEvent(new Event('change', { bubbles: true }));
    }
  }

  function render() {
    if (!popover || !activeInput) return;
    popover.innerHTML = '';

    // Cabecera
    const head = document.createElement('div');
    head.className = 'xp-cd-head';

    const btnPrev = document.createElement('button');
    btnPrev.type = 'button';
    btnPrev.className = 'xp-cd-nav';
    btnPrev.setAttribute('aria-label', mode === 'months' ? 'Año anterior' : 'Mes anterior');
    btnPrev.appendChild(createSvg('M15 18l-6-6 6-6'));
    btnPrev.addEventListener('click', function () {
      if (mode === 'months') {
        viewYear--;
      } else {
        viewMonth--;
        if (viewMonth < 0) { viewMonth = 11; viewYear--; }
      }
      render();
    });

    const titleBtn = document.createElement('button');
    titleBtn.type = 'button';
    titleBtn.className = 'xp-cd-title-btn';
    titleBtn.textContent = mode === 'months' ? String(viewYear) : (MESES[viewMonth] + ' ' + viewYear);
    titleBtn.title = mode === 'months' ? 'Volver a días' : 'Elegir mes';
    titleBtn.addEventListener('click', function () {
      mode = (mode === 'days') ? 'months' : 'days';
      render();
    });

    const btnNext = document.createElement('button');
    btnNext.type = 'button';
    btnNext.className = 'xp-cd-nav';
    btnNext.setAttribute('aria-label', mode === 'months' ? 'Año siguiente' : 'Mes siguiente');
    btnNext.appendChild(createSvg('M9 18l6-6-6-6'));
    btnNext.addEventListener('click', function () {
      if (mode === 'months') {
        viewYear++;
      } else {
        viewMonth++;
        if (viewMonth > 11) { viewMonth = 0; viewYear++; }
      }
      render();
    });

    head.appendChild(btnPrev);
    head.appendChild(titleBtn);
    head.appendChild(btnNext);
    popover.appendChild(head);

    if (mode === 'months') {
      const mGrid = document.createElement('div');
      mGrid.className = 'xp-cd-months';
      MESES_CORTOS.forEach(function (label, idx) {
        const mb = document.createElement('button');
        mb.type = 'button';
        mb.className = 'xp-cd-month-btn' + (idx === viewMonth ? ' is-active' : '');
        mb.textContent = label;
        mb.addEventListener('click', function () {
          viewMonth = idx;
          mode = 'days';
          render();
        });
        mGrid.appendChild(mb);
      });
      popover.appendChild(mGrid);
    } else {
      // Días de la semana
      const wRow = document.createElement('div');
      wRow.className = 'xp-cd-weekdays';
      DIAS_SEM.forEach(function (d) {
        const sp = document.createElement('span');
        sp.textContent = d;
        wRow.appendChild(sp);
      });
      popover.appendChild(wRow);

      // Matriz 42 días (Lunes a Domingo)
      const grid = document.createElement('div');
      grid.className = 'xp-cd-grid';

      const firstDay = new Date(viewYear, viewMonth, 1);
      const startDow = (firstDay.getDay() + 6) % 7; // 0 = Lunes
      const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
      const prevMonthDays = new Date(viewYear, viewMonth, 0).getDate();

      const now = new Date();
      const todayIso = formatIso(now.getFullYear(), now.getMonth(), now.getDate());
      const selectedIso = activeInput.value || '';

      for (let i = 0; i < 42; i++) {
        let cellYear = viewYear;
        let cellMonth = viewMonth;
        let cellDay;
        let outside = false;

        if (i < startDow) {
          outside = true;
          cellMonth--;
          if (cellMonth < 0) { cellMonth = 11; cellYear--; }
          cellDay = prevMonthDays - startDow + 1 + i;
        } else if (i >= startDow + daysInMonth) {
          outside = true;
          cellMonth++;
          if (cellMonth > 11) { cellMonth = 0; cellYear++; }
          cellDay = i - (startDow + daysInMonth) + 1;
        } else {
          cellDay = i - startDow + 1;
        }

        const iso = formatIso(cellYear, cellMonth, cellDay);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'xp-cd-day';
        if (outside) btn.classList.add('is-outside');
        if (iso === todayIso) btn.classList.add('is-today');
        if (iso === selectedIso) btn.classList.add('is-selected');
        btn.textContent = String(cellDay);
        btn.addEventListener('click', function () {
          selectIsoDate(iso);
        });
        grid.appendChild(btn);
      }
      popover.appendChild(grid);
    }

    // Pie minimalista
    const foot = document.createElement('div');
    foot.className = 'xp-cd-foot';

    const btnToday = document.createElement('button');
    btnToday.type = 'button';
    btnToday.className = 'xp-cd-foot-btn is-accent';
    btnToday.textContent = 'Hoy';
    btnToday.addEventListener('click', function () {
      const now = new Date();
      selectIsoDate(formatIso(now.getFullYear(), now.getMonth(), now.getDate()));
    });

    const btnClose = document.createElement('button');
    btnClose.type = 'button';
    btnClose.className = 'xp-cd-foot-btn';
    btnClose.textContent = 'Cerrar';
    btnClose.addEventListener('click', close);

    foot.appendChild(btnToday);
    foot.appendChild(btnClose);
    popover.appendChild(foot);
  }

  function positionPopover() {
    if (!backdrop || !popover || !activeInput) return;
    const inst = registry.get(activeInput);
    const anchor = (inst && inst.trigger) || activeInput;
    const vw = window.innerWidth;
    const vh = window.innerHeight;

    // En móvil o pantallas estrechas centrar siempre para evitar cortes fuera de pantalla
    if (vw <= 600) {
      backdrop.classList.add('is-centered');
      popover.style.left = '';
      popover.style.top = '';
      return;
    }

    const rect = anchor.getBoundingClientRect();
    const popRect = popover.getBoundingClientRect();
    const popW = popRect.width || 276;
    const popH = popRect.height || 310;
    const margin = 10;

    const spaceBelow = vh - rect.bottom - margin;
    const spaceAbove = rect.top - margin;

    if (spaceBelow < popH && spaceAbove < popH) {
      backdrop.classList.add('is-centered');
      popover.style.left = '';
      popover.style.top = '';
      return;
    }

    backdrop.classList.remove('is-centered');
    let left = rect.left;
    if (left + popW > vw - margin) {
      left = vw - popW - margin;
    }
    left = Math.max(margin, left);

    let top = (spaceBelow >= popH) ? (rect.bottom + 6) : (rect.top - popH - 6);
    top = Math.max(margin, Math.min(top, vh - popH - margin));

    popover.style.left = Math.round(left) + 'px';
    popover.style.top = Math.round(top) + 'px';
  }

  function open(input) {
    if (!input || input.disabled) return;
    enhanceOne(input);
    ensurePopover();

    if (activeInput && activeInput !== input) {
      const prevInst = registry.get(activeInput);
      if (prevInst) prevInst.trigger.classList.remove('is-open');
    }

    activeInput = input;
    const inst = registry.get(input);
    if (inst) inst.trigger.classList.add('is-open');

    const dt = parseIso(input.value) || new Date();
    viewYear = dt.getFullYear();
    viewMonth = dt.getMonth();
    mode = 'days';

    render();
    backdrop.classList.add('is-open');
    positionPopover();
  }

  function close() {
    if (activeInput) {
      const inst = registry.get(activeInput);
      if (inst) inst.trigger.classList.remove('is-open');
    }
    activeInput = null;
    if (backdrop) {
      backdrop.classList.remove('is-open', 'is-centered');
    }
  }

  function enhanceOne(input) {
    if (!input || input.nodeName !== 'INPUT' || input.type !== 'date') return null;
    if (input.hasAttribute('data-native-date')) return null;
    if (registry.has(input)) {
      syncTrigger(input);
      return registry.get(input);
    }

    const wrap = document.createElement('div');
    wrap.className = 'xp-cd';

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'xp-cd-trigger';
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.setAttribute('aria-label', input.getAttribute('aria-label') || input.getAttribute('title') || 'Seleccionar fecha');

    const valEl = document.createElement('span');
    valEl.className = 'xp-cd-value';

    const ico = document.createElement('span');
    ico.className = 'xp-cd-ico';
    ico.appendChild(createSvg('M8 2v3M16 2v3M3 9h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z'));

    trigger.appendChild(valEl);
    trigger.appendChild(ico);

    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    wrap.appendChild(trigger);
    input.classList.add('xp-cd-native');
    input.setAttribute('tabindex', '-1');
    input.setAttribute('aria-hidden', 'true');

    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      open(input);
    });
    input.addEventListener('change', function () { syncTrigger(input); });
    input.addEventListener('input', function () { syncTrigger(input); });

    const inst = { wrap: wrap, trigger: trigger, valEl: valEl };
    registry.set(input, inst);

    const valueDesc = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
    if (valueDesc && valueDesc.get && valueDesc.set) {
      try {
        Object.defineProperty(input, 'value', {
          configurable: true,
          get: function () { return valueDesc.get.call(this); },
          set: function (v) {
            valueDesc.set.call(this, v);
            syncTrigger(this);
          }
        });
      } catch (_) {}
    }

    syncTrigger(input);
    return inst;
  }

  function initAll(root) {
    const scope = root || document;
    scope.querySelectorAll('input[type="date"]').forEach(enhanceOne);
  }

  window.XpDatePicker = {
    open: open,
    close: close,
    sync: syncTrigger,
    refresh: initAll
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { initAll(); });
  } else {
    initAll();
  }
})();
