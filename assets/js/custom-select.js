/* ═══════════════════════════════════════════════════════════════
   ATLAS FIT — Minimalist Searchable Custom Select Engine
   Reemplaza los <select> nativos con un componente Bento compacto,
   buscador integrado, scroll estilizado y sincronización bidireccional.
═══════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  if (window.__xpCustomSelectInitialized) return;
  window.__xpCustomSelectInitialized = true;

  const instances = new WeakMap();
  let activeInstance = null;

  function normText(str) {
    return String(str || '')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .trim();
  }

  function createSvg(pathD, cls) {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '2.2');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');
    if (cls) svg.setAttribute('class', cls);
    const p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    p.setAttribute('d', pathD);
    svg.appendChild(p);
    return svg;
  }

  function buildDropdownNode() {
    const dd = document.createElement('div');
    dd.className = 'xp-cs-dropdown';
    dd.setAttribute('role', 'listbox');

    const searchWrap = document.createElement('div');
    searchWrap.className = 'xp-cs-search-wrap';

    const sIcon = createSvg('M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z', 'xp-cs-search-icon');
    const searchInput = document.createElement('input');
    searchInput.type = 'search';
    searchInput.className = 'xp-cs-search';
    searchInput.placeholder = 'Buscar...';
    searchInput.autocomplete = 'off';
    searchInput.spellcheck = false;
    searchInput.setAttribute('aria-label', 'Buscar opción');

    const clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'xp-cs-search-clear';
    clearBtn.textContent = '×';
    clearBtn.setAttribute('aria-label', 'Limpiar búsqueda');

    searchWrap.appendChild(sIcon);
    searchWrap.appendChild(searchInput);
    searchWrap.appendChild(clearBtn);

    const list = document.createElement('div');
    list.className = 'xp-cs-list';

    dd.appendChild(searchWrap);
    dd.appendChild(list);

    return { dd, searchInput, clearBtn, list };
  }

  function enhanceSelect(selectEl) {
    if (!selectEl || selectEl.nodeName !== 'SELECT') return null;
    if (selectEl.multiple) return null;
    if (selectEl.classList.contains('no-custom-select') || selectEl.classList.contains('swal2-select') || selectEl.hasAttribute('data-native-select')) return null;
    if (selectEl.closest('.swal2-popup, .swal2-container')) return null;
    if ((selectEl.style && selectEl.style.display === 'none') || selectEl.hasAttribute('hidden')) return null;
    if (instances.has(selectEl)) return instances.get(selectEl);

    const wrapper = document.createElement('div');
    wrapper.className = 'xp-cs';
    if (selectEl.style.flex) wrapper.style.flex = selectEl.style.flex;
    if (selectEl.style.maxWidth) wrapper.style.maxWidth = selectEl.style.maxWidth;

    const parent = selectEl.parentNode;
    if (!parent) return null;
    parent.insertBefore(wrapper, selectEl);
    wrapper.appendChild(selectEl);

    selectEl.classList.add('xp-cs-native');
    selectEl.setAttribute('tabindex', '-1');
    selectEl.setAttribute('aria-hidden', 'true');

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'xp-cs-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');

    const labelSpan = document.createElement('span');
    labelSpan.className = 'xp-cs-label';
    const caret = document.createElement('span');
    caret.className = 'xp-cs-caret';
    caret.appendChild(createSvg('M6 9l6 6 6-6', ''));

    trigger.appendChild(labelSpan);
    trigger.appendChild(caret);
    wrapper.appendChild(trigger);

    const ui = buildDropdownNode();
    let highlightIdx = -1;
    let visibleOpts = [];
    let isOpen = false;

    const protoDesc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');

    function syncLabelAndState() {
      const selOpt = selectEl.options[selectEl.selectedIndex] || null;
      const text = selOpt ? selOpt.textContent.trim() : '-- Seleccionar --';
      labelSpan.textContent = text;
      const isEmptyVal = !selOpt || selOpt.value === '';
      trigger.classList.toggle('is-placeholder', isEmptyVal);
      trigger.classList.toggle('is-disabled', !!selectEl.disabled);
      trigger.disabled = !!selectEl.disabled;

      if (isOpen) {
        renderOptions();
      }
    }

    function positionDropdown() {
      if (!isOpen) return;
      const rect = trigger.getBoundingClientRect();
      const vw = window.innerWidth || document.documentElement.clientWidth;
      const vh = window.innerHeight || document.documentElement.clientHeight;
      const width = Math.min(Math.max(rect.width, 210), vw - 16);
      let left = rect.left;
      if (left + width > vw - 8) {
        left = Math.max(8, vw - width - 8);
      }
      if (left < 8) left = 8;

      const spaceBelow = vh - rect.bottom - 10;
      const spaceAbove = rect.top - 10;
      const openUp = spaceBelow < 200 && spaceAbove > spaceBelow;

      ui.dd.style.width = width + 'px';
      ui.dd.style.left = left + 'px';
      ui.dd.classList.toggle('is-top', openUp);

      const availH = Math.max(120, Math.min(248, (openUp ? spaceAbove : spaceBelow) - 8));
      ui.list.style.maxHeight = Math.max(90, availH - 44) + 'px';

      const ddHeight = ui.dd.offsetHeight || 220;
      if (openUp) {
        ui.dd.style.top = Math.max(8, rect.top - ddHeight - 5) + 'px';
      } else {
        ui.dd.style.top = Math.min(vh - ddHeight - 8, rect.bottom + 5) + 'px';
      }
    }

    function close() {
      if (!isOpen) return;
      isOpen = false;
      trigger.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
      ui.dd.classList.remove('is-visible');
      if (ui.dd.parentNode) ui.dd.parentNode.removeChild(ui.dd);
      if (activeInstance === inst) activeInstance = null;
    }

    function selectValue(val) {
      const prevVal = protoDesc.get.call(selectEl);
      protoDesc.set.call(selectEl, val);
      syncLabelAndState();
      close();
      try { trigger.focus({ preventScroll: true }); } catch (_) {}
      if (prevVal !== val) {
        selectEl.dispatchEvent(new Event('input', { bubbles: true }));
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    function renderOptions() {
      const q = normText(ui.searchInput.value);
      ui.clearBtn.classList.toggle('is-shown', ui.searchInput.value.length > 0);
      ui.list.innerHTML = '';
      visibleOpts = [];
      highlightIdx = -1;

      const currentVal = protoDesc.get.call(selectEl);
      const children = Array.from(selectEl.children);

      const appendOption = (opt) => {
        const txt = (opt.textContent || '').trim();
        if (q !== '' && !normText(txt).includes(q)) return false;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'xp-cs-opt';
        btn.setAttribute('role', 'option');
        const isSel = opt.value === currentVal;
        btn.setAttribute('aria-selected', isSel ? 'true' : 'false');
        if (isSel) btn.classList.add('is-selected');
        if (opt.disabled) {
          btn.classList.add('is-disabled');
          btn.disabled = true;
        }

        const span = document.createElement('span');
        span.className = 'xp-cs-opt-text';
        span.textContent = txt;

        const chk = createSvg('M20 6L9 17l-5-5', 'xp-cs-opt-check');

        btn.appendChild(span);
        btn.appendChild(chk);

        if (!opt.disabled) {
          btn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            selectValue(opt.value);
          });
          visibleOpts.push(btn);
          if (isSel) highlightIdx = visibleOpts.length - 1;
        }
        ui.list.appendChild(btn);
        return true;
      };

      children.forEach((node) => {
        if (node.nodeName === 'OPTGROUP') {
          const groupOpts = Array.from(node.querySelectorAll('option'));
          const matchedOpts = groupOpts.filter((o) => q === '' || normText(o.textContent).includes(q));
          if (matchedOpts.length > 0) {
            const lbl = document.createElement('div');
            lbl.className = 'xp-cs-group-label';
            lbl.textContent = node.label || '';
            ui.list.appendChild(lbl);
            matchedOpts.forEach(appendOption);
          }
        } else if (node.nodeName === 'OPTION') {
          appendOption(node);
        }
      });

      if (ui.list.children.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'xp-cs-empty';
        empty.textContent = 'Sin coincidencias';
        ui.list.appendChild(empty);
      }
    }

    function open() {
      if (selectEl.disabled) return;
      if (activeInstance && activeInstance !== inst) {
        activeInstance.close();
      }
      isOpen = true;
      activeInstance = inst;
      ui.searchInput.value = '';
      renderOptions();

      const host = trigger.closest('.swal2-container') || document.body;
      host.appendChild(ui.dd);
      ui.dd.classList.add('is-visible');
      trigger.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');

      positionDropdown();

      const selectedBtn = ui.list.querySelector('.xp-cs-opt.is-selected');
      if (selectedBtn && typeof selectedBtn.scrollIntoView === 'function') {
        selectedBtn.scrollIntoView({ block: 'nearest' });
      }

      const isCoarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
      if (!isCoarse) {
        setTimeout(() => {
          try { ui.searchInput.focus({ preventScroll: true }); } catch (_) {}
        }, 15);
      }
    }

    trigger.addEventListener('click', (ev) => {
      ev.preventDefault();
      ev.stopPropagation();
      if (isOpen) close();
      else open();
    });

    // Click en <label for="id"> enfoca/abre nuestro trigger
    if (selectEl.id) {
      selectEl.addEventListener('focus', () => {
        try { trigger.focus({ preventScroll: true }); } catch (_) {}
      });
    }

    ui.dd.addEventListener('click', (ev) => ev.stopPropagation());
    ui.searchInput.addEventListener('input', () => {
      renderOptions();
      positionDropdown();
    });
    ui.clearBtn.addEventListener('click', (ev) => {
      ev.stopPropagation();
      ui.searchInput.value = '';
      renderOptions();
      positionDropdown();
      ui.searchInput.focus();
    });

    ui.searchInput.addEventListener('keydown', (ev) => {
      if (ev.key === 'Escape') {
        ev.preventDefault();
        close();
        trigger.focus();
      } else if (ev.key === 'ArrowDown') {
        ev.preventDefault();
        if (visibleOpts.length === 0) return;
        highlightIdx = (highlightIdx + 1) % visibleOpts.length;
        visibleOpts.forEach((b, i) => b.classList.toggle('is-highlighted', i === highlightIdx));
        visibleOpts[highlightIdx].scrollIntoView({ block: 'nearest' });
      } else if (ev.key === 'ArrowUp') {
        ev.preventDefault();
        if (visibleOpts.length === 0) return;
        highlightIdx = (highlightIdx - 1 + visibleOpts.length) % visibleOpts.length;
        visibleOpts.forEach((b, i) => b.classList.toggle('is-highlighted', i === highlightIdx));
        visibleOpts[highlightIdx].scrollIntoView({ block: 'nearest' });
      } else if (ev.key === 'Enter') {
        if (visibleOpts.length > 0 && highlightIdx >= 0 && visibleOpts[highlightIdx]) {
          ev.preventDefault();
          visibleOpts[highlightIdx].click();
        } else if (visibleOpts.length === 1) {
          ev.preventDefault();
          visibleOpts[0].click();
        }
      }
    });

    selectEl.addEventListener('change', syncLabelAndState);

    if (protoDesc && protoDesc.configurable) {
      Object.defineProperty(selectEl, 'value', {
        configurable: true,
        get() {
          return protoDesc.get.call(selectEl);
        },
        set(newVal) {
          protoDesc.set.call(selectEl, newVal);
          syncLabelAndState();
        }
      });
    }

    if (selectEl.form) {
      selectEl.form.addEventListener('reset', () => {
        setTimeout(syncLabelAndState, 0);
      });
    }

    const optObserver = new MutationObserver(() => syncLabelAndState());
    optObserver.observe(selectEl, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'selected', 'label'] });

    const inst = { selectEl, trigger, close, open, sync: syncLabelAndState, position: positionDropdown };
    instances.set(selectEl, inst);
    syncLabelAndState();
    return inst;
  }

  function enhanceAll(root) {
    const scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll('select').forEach(enhanceSelect);
  }

  document.addEventListener('click', () => {
    if (activeInstance) activeInstance.close();
  });

  document.addEventListener('keydown', (ev) => {
    if (ev.key === 'Escape' && activeInstance) {
      const cur = activeInstance;
      cur.close();
      try { cur.trigger.focus({ preventScroll: true }); } catch (_) {}
    }
  });

  window.addEventListener('resize', () => {
    if (activeInstance) activeInstance.position();
  }, { passive: true });

  window.addEventListener('scroll', (ev) => {
    if (!activeInstance) return;
    if (ev.target && ev.target.closest && ev.target.closest('.xp-cs-dropdown')) return;
    activeInstance.position();
  }, { capture: true, passive: true });

  const domObserver = new MutationObserver((mutations) => {
    mutations.forEach((m) => {
      m.addedNodes.forEach((node) => {
        if (!node || node.nodeType !== 1) return;
        if (node.nodeName === 'SELECT') enhanceSelect(node);
        else if (node.querySelectorAll) node.querySelectorAll('select').forEach(enhanceSelect);
      });
    });
  });

  function boot() {
    enhanceAll(document);
    if (document.body) {
      domObserver.observe(document.body, { childList: true, subtree: true });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  window.XpSelect = {
    enhance: enhanceSelect,
    refreshAll: () => enhanceAll(document),
  };
})();

