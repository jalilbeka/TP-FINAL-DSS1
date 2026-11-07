/* ============================================================
   DSS University — Main Application Controller
   ============================================================ */

const app = (() => {
  /* ----------------------------------------------------------
     State
  ---------------------------------------------------------- */
  let activeTab = 'dashboard';
  let studentsData = [];
  let specsData = [];
  let criteriaData = [];
  let evaluationsData = [];

  /* ----------------------------------------------------------
     SVG Icons (inline, hand-crafted)
  ---------------------------------------------------------- */
  const icons = {
    user: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
    lock: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`,
    email: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>`,
    plus: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>`,
    edit: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`,
    trash: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>`,
    chart: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>`,
    trophy: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>`,
    sun: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>`,
    moon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>`,
    globe: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>`,
    logout: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>`,
    search: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>`,
    chevron: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>`,
    close: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
    menu: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="18" x2="20" y2="18"/></svg>`,
    check: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
    alert: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    students: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    grid: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
    clipboard: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>`,
    star: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`,
  };

  function icon(name, size = 16) {
    return `<span class="inline-flex" style="width:${size}px;height:${size}px">${icons[name] || ''}</span>`;
  }

  /* ----------------------------------------------------------
     API Helper
  ---------------------------------------------------------- */
  async function api(endpoint, method = 'GET', data = null) {
    const options = {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      credentials: 'same-origin',
    };

    if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
      options.body = JSON.stringify(data);
    }

    try {
      const response = await fetch(`api/${endpoint}`, options);
      const json = await response.json().catch(() => null);
      if (!response.ok) {
        const error = new Error(json?.message || `HTTP ${response.status}`);
        error.data = json;
        error.status = response.status;
        throw error;
      }
      return json;
    } catch (err) {
      console.warn(`API error [${endpoint}]:`, err.message);
      throw err;
    }
  }

  /* ----------------------------------------------------------
     Toast Notification System
  ---------------------------------------------------------- */
  function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toastIcons = {
      success: icons.check,
      error: icons.close,
      warning: icons.alert,
      info: icons.alert,
    };

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.innerHTML = `
      <span class="toast__icon">${toastIcons[type] || toastIcons.info}</span>
      <span class="toast__message">${escapeHtml(message)}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('removing');
      toast.addEventListener('animationend', () => toast.remove());
    }, 4000);
  }

  /* ----------------------------------------------------------
     Theme Toggle
  ---------------------------------------------------------- */
  function initTheme() {
    const saved = localStorage.getItem('dss_theme');
    if (saved === 'light') {
      document.documentElement.classList.remove('dark');
    } else {
      document.documentElement.classList.add('dark');
    }
    updateThemeIcon();
  }

  function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('dss_theme', isDark ? 'dark' : 'light');
    updateThemeIcon();
    // Refresh charts with new theme colors
    setTimeout(() => {
      if (activeTab === 'results') {
        renderResultsTab();
      }
    }, 100);
  }

  function updateThemeIcon() {
    const btn = document.getElementById('btn-theme');
    if (!btn) return;
    const isDark = document.documentElement.classList.contains('dark');
    btn.innerHTML = isDark ? icon('sun', 20) : icon('moon', 20);
  }

  /* ----------------------------------------------------------
     Language Toggle
  ---------------------------------------------------------- */
  function initLanguage() {
    updateLangButtons();
    i18n.applyTranslations();
  }

  function toggleLanguage() {
    const current = i18n.getCurrentLanguage();
    const next = current === 'en' ? 'fr' : 'en';
    i18n.setLanguage(next);
    updateLangButtons();
    // Re-render current tab with new translations
    renderActiveTab();
  }

  function updateLangButtons() {
    const lang = i18n.getCurrentLanguage();
    const btnEn = document.getElementById('lang-en');
    const btnFr = document.getElementById('lang-fr');
    if (btnEn) btnEn.classList.toggle('text-[var(--accent-primary)]', lang === 'en');
    if (btnEn) btnEn.classList.toggle('font-bold', lang === 'en');
    if (btnFr) btnFr.classList.toggle('text-[var(--accent-primary)]', lang === 'fr');
    if (btnFr) btnFr.classList.toggle('font-bold', lang === 'fr');
  }

  /* ----------------------------------------------------------
     Tab Switching
  ---------------------------------------------------------- */
  function switchTab(tabName) {
    activeTab = tabName;

    // Update tab button states
    document.querySelectorAll('.tab-btn').forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.tab === tabName);
    });

    // Show/hide panels
    document.querySelectorAll('.tab-panel').forEach((panel) => {
      panel.classList.toggle('hidden', panel.id !== `panel-${tabName}`);
    });

    renderActiveTab();
  }

  function renderActiveTab() {
    switch (activeTab) {
      case 'dashboard':
        renderDashboardTab();
        break;
      case 'students':
        renderStudentsTab();
        break;
      case 'specializations':
        renderSpecsTab();
        break;
      case 'criteria':
        renderCriteriaTab();
        break;
      case 'evaluations':
        renderEvaluationsTab();
        break;
      case 'results':
        renderResultsTab();
        break;
    }
  }

  /* ----------------------------------------------------------
     Admin vs Student View
  ---------------------------------------------------------- */
  function applyRoleView() {
    const isAdmin = window.auth.isAdmin();
    document.querySelectorAll('[data-role="admin"]').forEach((el) => {
      el.classList.toggle('hidden', !isAdmin);
    });
    document.querySelectorAll('[data-role="student"]').forEach((el) => {
      el.classList.toggle('hidden', isAdmin);
    });

    // Update header user info
    const user = window.auth.getUser();
    const userNameEl = document.getElementById('header-username');
    const roleBadgeEl = document.getElementById('header-role-badge');

    if (userNameEl && user) {
      userNameEl.textContent = user.fullname || user.username;
    }
    if (roleBadgeEl && user) {
      const roleKey = isAdmin ? 'header.role_admin' : 'header.role_student';
      roleBadgeEl.textContent = i18n.t(roleKey);
      roleBadgeEl.className = `text-xs px-2 py-0.5 rounded-full font-medium ${
        isAdmin
          ? 'bg-[var(--accent-primary)]/20 text-[var(--accent-primary)]'
          : 'bg-[var(--accent-success)]/20 text-[var(--accent-success)]'
      }`;
    }

    // Hide certain tabs for students
    const adminTabs = ['students'];
    adminTabs.forEach((tab) => {
      const tabBtn = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
      if (tabBtn) tabBtn.classList.toggle('hidden', !isAdmin);
    });
  }

  /* ----------------------------------------------------------
     Data Loading
  ---------------------------------------------------------- */
  async function loadAllData() {
    try {
      const user = window.auth.getUser();
      const currentStudentId = user?.student_id || user?.id;
      const lang = i18n.getCurrentLanguage();

      const promises = [
        api('students.php'),
        api('specializations.php'),
        api('criteria.php'),
      ];

      if (currentStudentId) {
        promises.push(api(`evaluations.php?student_id=${currentStudentId}`));
      }

      const results = await Promise.allSettled(promises);
      const [studentsRes, specsRes, criteriaRes, evalsRes] = results;

      if (studentsRes.status === 'fulfilled' && studentsRes.value?.data) {
        studentsData = studentsRes.value.data.map((st) => ({
          ...st,
          id: Number(st.id),
          fullname: st.fullname || (st.first_name ? `${st.first_name} ${st.last_name}` : st.username),
          role: st.role || 'student',
        }));
      } else {
        studentsData = getDemoStudents();
      }

      if (specsRes.status === 'fulfilled' && specsRes.value?.data) {
        specsData = specsRes.value.data.map((s) => ({
          ...s,
          id: Number(s.id),
          name: lang === 'fr' ? (s.name_fr || s.name_en || s.name) : (s.name_en || s.name_fr || s.name),
          description: lang === 'fr' ? (s.description_fr || s.description_en || s.description) : (s.description_en || s.description_fr || s.description),
        }));
      } else {
        specsData = getDemoSpecs();
      }

      if (criteriaRes.status === 'fulfilled' && criteriaRes.value?.data) {
        criteriaData = criteriaRes.value.data.map((c) => ({
          ...c,
          id: Number(c.id),
          name: lang === 'fr' ? (c.name_fr || c.name_en || c.name) : (c.name_en || c.name_fr || c.name),
          weight: parseFloat(c.weight),
        }));
      } else {
        criteriaData = getDemoCriteria();
      }

      if (evalsRes && evalsRes.status === 'fulfilled' && evalsRes.value?.data) {
        const matrixList = evalsRes.value.data.evaluations || [];
        const flat = [];
        matrixList.forEach((item) => {
          if (Array.isArray(item.scores)) {
            item.scores.forEach((sc) => {
              flat.push({
                student_id: Number(currentStudentId),
                specialization_id: Number(item.specialization_id),
                criterion_id: Number(sc.criterion_id),
                score: Number(sc.score),
              });
            });
          }
        });
        evaluationsData = flat;
      }
    } catch {
      // Use demo data as fallback
      studentsData = getDemoStudents();
      specsData = getDemoSpecs();
      criteriaData = getDemoCriteria();
      evaluationsData = [];
    }
  }

  /* ----------------------------------------------------------
     Demo Data (fallback when API is unavailable)
  ---------------------------------------------------------- */
  function getDemoStudents() {
    return [
      { id: 1, username: 'ahmed', fullname: 'Ahmed Benali', email: 'ahmed@univ.dz', role: 'student' },
      { id: 2, username: 'sara', fullname: 'Sara Khelifi', email: 'sara@univ.dz', role: 'student' },
      { id: 3, username: 'karim', fullname: 'Karim Boudiaf', email: 'karim@univ.dz', role: 'student' },
    ];
  }

  function getDemoSpecs() {
    return [
      { id: 1, name: i18n.t('specs.ai'), code: 'AI', description: i18n.t('specs.ai_desc') },
      { id: 2, name: i18n.t('specs.cybersecurity'), code: 'CYBER', description: i18n.t('specs.cybersecurity_desc') },
      { id: 3, name: i18n.t('specs.data_science'), code: 'DS', description: i18n.t('specs.data_science_desc') },
      { id: 4, name: i18n.t('specs.isse'), code: 'ISSE', description: i18n.t('specs.isse_desc') },
    ];
  }

  function getDemoCriteria() {
    return [
      { id: 1, name: i18n.t('criteria.programming'), type: 'maximize', weight: 0.2 },
      { id: 2, name: i18n.t('criteria.mathematics'), type: 'maximize', weight: 0.2 },
      { id: 3, name: i18n.t('criteria.competency'), type: 'maximize', weight: 0.15 },
      { id: 4, name: i18n.t('criteria.career'), type: 'maximize', weight: 0.2 },
      { id: 5, name: i18n.t('criteria.difficulty'), type: 'minimize', weight: 0.1 },
      { id: 6, name: i18n.t('criteria.interest'), type: 'maximize', weight: 0.15 },
    ];
  }

  /* ----------------------------------------------------------
     Dashboard Tab
  ---------------------------------------------------------- */
  function renderDashboardTab() {
    const panel = document.getElementById('panel-dashboard');
    if (!panel) return;

    const user = window.auth.getUser();
    const completedEvals = evaluationsData.length;
    const totalPossible = specsData.length * criteriaData.length;
    const avgScore =
      evaluationsData.length > 0
        ? (evaluationsData.reduce((sum, e) => sum + Number(e.score || 0), 0) / evaluationsData.length).toFixed(1)
        : '—';

    panel.innerHTML = `
      <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-1" data-i18n="dashboard.title">${i18n.t('dashboard.title')}</h2>
        <p class="text-[var(--text-secondary)]">
          <span data-i18n="dashboard.welcome">${i18n.t('dashboard.welcome')}</span>, ${escapeHtml(user?.fullname || user?.username || '')}
        </p>
      </div>

      <!-- Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        ${overviewCard(icon('students', 24), i18n.t('dashboard.total_students'), studentsData.length, 'var(--accent-primary)', 'dashboard.total_students')}
        ${overviewCard(icon('grid', 24), i18n.t('dashboard.total_specs'), specsData.length, 'var(--chart-2)', 'dashboard.total_specs')}
        ${overviewCard(icon('clipboard', 24), i18n.t('dashboard.total_criteria'), criteriaData.length, 'var(--chart-3)', 'dashboard.total_criteria')}
        ${overviewCard(icon('chart', 24), i18n.t('dashboard.evaluations_done'), `${completedEvals}`, 'var(--chart-4)', 'dashboard.evaluations_done')}
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-xl p-6">
          <h3 class="text-lg font-semibold mb-4" data-i18n="dashboard.quick_stats">${i18n.t('dashboard.quick_stats')}</h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-[var(--text-secondary)]" data-i18n="dashboard.avg_score">${i18n.t('dashboard.avg_score')}</span>
              <span class="font-mono text-lg font-semibold">${avgScore}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-[var(--text-secondary)]" data-i18n="dashboard.evaluations_done">${i18n.t('dashboard.evaluations_done')}</span>
              <span class="font-mono text-lg font-semibold">${completedEvals} / ${totalPossible}</span>
            </div>
            <div class="w-full bg-[var(--bg-tertiary)] rounded-full h-2 mt-2">
              <div class="h-2 rounded-full bg-[var(--accent-primary)] transition-all" style="width: ${totalPossible > 0 ? (completedEvals / totalPossible) * 100 : 0}%"></div>
            </div>
          </div>
        </section>

        <section class="bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-xl p-6">
          <h3 class="text-lg font-semibold mb-4" data-i18n="dashboard.total_specs">${i18n.t('dashboard.total_specs')}</h3>
          <div class="space-y-3">
            ${specsData
              .map(
                (s, i) => `
              <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full" style="background: var(--chart-${(i % 4) + 1})"></span>
                <span class="flex-1 text-sm">${escapeHtml(s.name)}</span>
                <span class="text-xs text-[var(--text-muted)] font-mono">${escapeHtml(s.code)}</span>
              </div>
            `
              )
              .join('')}
          </div>
        </section>
      </div>
    `;
  }

  function overviewCard(iconHtml, label, value, color, i18nKey) {
    return `
      <div class="bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background: ${color}15; color: ${color}">
          ${iconHtml}
        </div>
        <div>
          <p class="text-2xl font-bold font-mono">${value}</p>
          <p class="text-sm text-[var(--text-muted)]" data-i18n="${i18nKey}">${escapeHtml(label)}</p>
        </div>
      </div>
    `;
  }

  /* ----------------------------------------------------------
     Students Tab
  ---------------------------------------------------------- */
  function renderStudentsTab() {
    const panel = document.getElementById('panel-students');
    if (!panel) return;

    const isAdmin = window.auth.isAdmin();

    if (!isAdmin) {
      // Student view: show own profile
      const user = window.auth.getUser();
      panel.innerHTML = `
        <div class="mb-6">
          <h2 class="text-2xl font-semibold" data-i18n="students.my_profile">${i18n.t('students.my_profile')}</h2>
        </div>
        <div class="bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-xl p-6 max-w-lg">
          <div class="space-y-4">
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-full bg-[var(--accent-primary)]/20 flex items-center justify-center text-[var(--accent-primary)]">
                ${icon('user', 28)}
              </div>
              <div>
                <p class="text-lg font-semibold">${escapeHtml(user?.fullname || '')}</p>
                <p class="text-sm text-[var(--text-muted)]">@${escapeHtml(user?.username || '')}</p>
              </div>
            </div>
            <div class="border-t border-[var(--border-default)] pt-4 space-y-3">
              <div class="flex items-center gap-3">
                ${icon('email', 16)}
                <span class="text-sm text-[var(--text-secondary)]">${escapeHtml(user?.email || '')}</span>
              </div>
            </div>
          </div>
        </div>
      `;
      return;
    }

    panel.innerHTML = `
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-semibold" data-i18n="students.title">${i18n.t('students.title')}</h2>
        <button data-action="add-student" data-role="admin" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--accent-primary)] hover:bg-[var(--accent-primary-hover)] text-white rounded-lg text-sm font-medium transition-colors">
          ${icon('plus', 16)}
          <span data-i18n="students.add">${i18n.t('students.add')}</span>
        </button>
      </div>

      ${
        studentsData.length === 0
          ? emptyState(i18n.t('common.no_data'))
          : `
        <div class="data-table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th data-i18n="students.id">${i18n.t('students.id')}</th>
                <th data-i18n="students.username">${i18n.t('students.username')}</th>
                <th data-i18n="students.fullname">${i18n.t('students.fullname')}</th>
                <th data-i18n="students.email">${i18n.t('students.email')}</th>
                <th data-i18n="students.role">${i18n.t('students.role')}</th>
                <th data-i18n="students.actions">${i18n.t('students.actions')}</th>
              </tr>
            </thead>
            <tbody>
              ${studentsData
                .map(
                  (s) => `
                <tr>
                  <td class="font-mono text-xs">${s.id}</td>
                  <td class="font-medium">${escapeHtml(s.username)}</td>
                  <td>${escapeHtml(s.fullname)}</td>
                  <td class="text-[var(--text-secondary)]">${escapeHtml(s.email)}</td>
                  <td>
                    <span class="text-xs px-2 py-0.5 rounded-full ${
                      s.role === 'admin'
                        ? 'bg-[var(--accent-primary)]/20 text-[var(--accent-primary)]'
                        : 'bg-[var(--accent-success)]/20 text-[var(--accent-success)]'
                    }">${escapeHtml(s.role)}</span>
                  </td>
                  <td>
                    <div class="flex items-center gap-1">
                      <button data-action="edit-student" data-id="${s.id}" class="p-1.5 rounded-md hover:bg-[var(--bg-tertiary)] text-[var(--text-muted)] hover:text-[var(--accent-primary)] transition-colors" title="${i18n.t('common.edit')}">
                        ${icon('edit', 14)}
                      </button>
                      <button data-action="delete-student" data-id="${s.id}" class="p-1.5 rounded-md hover:bg-[var(--bg-tertiary)] text-[var(--text-muted)] hover:text-[var(--accent-danger)] transition-colors" title="${i18n.t('common.delete')}">
                        ${icon('trash', 14)}
                      </button>
                    </div>
                  </td>
                </tr>
              `
                )
                .join('')}
            </tbody>
          </table>
        </div>
      `
      }
    `;
  }

  /* ----------------------------------------------------------
     Specializations Tab
  ---------------------------------------------------------- */
  function renderSpecsTab() {
    const panel = document.getElementById('panel-specializations');
    if (!panel) return;

    const isAdmin = window.auth.isAdmin();

    panel.innerHTML = `
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-semibold" data-i18n="specs.title">${i18n.t('specs.title')}</h2>
        ${
          isAdmin
            ? `<button data-action="add-spec" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--accent-primary)] hover:bg-[var(--accent-primary-hover)] text-white rounded-lg text-sm font-medium transition-colors">
            ${icon('plus', 16)}
            <span data-i18n="specs.add">${i18n.t('specs.add')}</span>
          </button>`
            : ''
        }
      </div>

      ${
        specsData.length === 0
          ? emptyState(i18n.t('common.no_data'))
          : `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          ${specsData
            .map(
              (s, i) => `
            <div class="bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-xl p-5 hover:border-[var(--border-active)] transition-colors">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                  <span class="w-3 h-3 rounded-full" style="background: var(--chart-${(i % 4) + 1})"></span>
                  <h3 class="font-semibold">${escapeHtml(s.name)}</h3>
                </div>
                <span class="text-xs font-mono text-[var(--text-muted)] bg-[var(--bg-tertiary)] px-2 py-0.5 rounded">${escapeHtml(s.code)}</span>
              </div>
              <p class="text-sm text-[var(--text-secondary)] mb-4">${escapeHtml(s.description || '')}</p>
              ${
                isAdmin
                  ? `
                <div class="flex items-center gap-2 pt-3 border-t border-[var(--border-default)]">
                  <button data-action="edit-spec" data-id="${s.id}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-md hover:bg-[var(--bg-tertiary)] text-[var(--text-muted)] hover:text-[var(--accent-primary)] transition-colors">
                    ${icon('edit', 12)} <span data-i18n="common.edit">${i18n.t('common.edit')}</span>
                  </button>
                  <button data-action="delete-spec" data-id="${s.id}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-md hover:bg-[var(--bg-tertiary)] text-[var(--text-muted)] hover:text-[var(--accent-danger)] transition-colors">
                    ${icon('trash', 12)} <span data-i18n="common.delete">${i18n.t('common.delete')}</span>
                  </button>
                </div>
              `
                  : ''
              }
            </div>
          `
            )
            .join('')}
        </div>
      `
      }
    `;
  }

  /* ----------------------------------------------------------
     Criteria Tab
  ---------------------------------------------------------- */
  function renderCriteriaTab() {
    const panel = document.getElementById('panel-criteria');
    if (!panel) return;

    const isAdmin = window.auth.isAdmin();

    panel.innerHTML = `
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-semibold" data-i18n="criteria.title">${i18n.t('criteria.title')}</h2>
        ${
          isAdmin
            ? `<button data-action="add-criterion" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--accent-primary)] hover:bg-[var(--accent-primary-hover)] text-white rounded-lg text-sm font-medium transition-colors">
            ${icon('plus', 16)}
            <span data-i18n="criteria.add">${i18n.t('criteria.add')}</span>
          </button>`
            : ''
        }
      </div>

      ${
        criteriaData.length === 0
          ? emptyState(i18n.t('common.no_data'))
          : `
        <div class="data-table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th data-i18n="criteria.id">${i18n.t('criteria.id')}</th>
                <th data-i18n="criteria.name">${i18n.t('criteria.name')}</th>
                <th data-i18n="criteria.type">${i18n.t('criteria.type')}</th>
                <th data-i18n="criteria.weight">${i18n.t('criteria.weight')}</th>
                ${isAdmin ? `<th data-i18n="criteria.actions">${i18n.t('criteria.actions')}</th>` : ''}
              </tr>
            </thead>
            <tbody>
              ${criteriaData
                .map(
                  (c) => `
                <tr>
                  <td class="font-mono text-xs">${c.id}</td>
                  <td class="font-medium">${escapeHtml(c.name)}</td>
                  <td>
                    <span class="text-xs px-2 py-0.5 rounded-full ${
                      c.type === 'maximize'
                        ? 'bg-[var(--accent-success)]/20 text-[var(--accent-success)]'
                        : 'bg-[var(--accent-warning)]/20 text-[var(--accent-warning)]'
                    }">
                      ${c.type === 'maximize' ? '↑' : '↓'} ${i18n.t(`criteria.${c.type}`)}
                    </span>
                  </td>
                  <td class="font-mono">${Number(c.weight).toFixed(2)}</td>
                  ${
                    isAdmin
                      ? `
                    <td>
                      <div class="flex items-center gap-1">
                        <button data-action="edit-criterion" data-id="${c.id}" class="p-1.5 rounded-md hover:bg-[var(--bg-tertiary)] text-[var(--text-muted)] hover:text-[var(--accent-primary)] transition-colors">
                          ${icon('edit', 14)}
                        </button>
                        <button data-action="delete-criterion" data-id="${c.id}" class="p-1.5 rounded-md hover:bg-[var(--bg-tertiary)] text-[var(--text-muted)] hover:text-[var(--accent-danger)] transition-colors">
                          ${icon('trash', 14)}
                        </button>
                      </div>
                    </td>
                  `
                      : ''
                  }
                </tr>
              `
                )
                .join('')}
            </tbody>
          </table>
        </div>
      `
      }
    `;
  }

  /* ----------------------------------------------------------
     Evaluations Tab
  ---------------------------------------------------------- */
  function renderEvaluationsTab() {
    const panel = document.getElementById('panel-evaluations');
    if (!panel) return;

    const isAdmin = window.auth.isAdmin();
    const user = window.auth.getUser();
    const studentsList = isAdmin ? studentsData.filter((s) => s.role === 'student') : [user];

    panel.innerHTML = `
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-semibold" data-i18n="eval.title">${i18n.t('eval.title')}</h2>
        <p class="text-sm text-[var(--text-muted)]" data-i18n="eval.score_hint">${i18n.t('eval.score_hint')}</p>
      </div>

      ${
        isAdmin
          ? `
        <div class="mb-6">
          <label class="block text-sm font-medium mb-2 text-[var(--text-secondary)]" data-i18n="eval.select_student">${i18n.t('eval.select_student')}</label>
          <select id="eval-student-select" class="w-full max-w-xs px-3 py-2 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-[var(--text-primary)] text-sm focus:border-[var(--accent-primary)] focus:outline-none">
            <option value="">— ${i18n.t('eval.select_student')} —</option>
            ${studentsList.map((s) => `<option value="${s.id}">${escapeHtml(s.fullname || s.username)}</option>`).join('')}
          </select>
        </div>
      `
          : ''
      }

      <div id="eval-matrix-container">
        ${
          !isAdmin
            ? buildEvaluationMatrix(user?.id)
            : `<div class="empty-state">
              ${icons.clipboard}
              <p data-i18n="eval.no_student">${i18n.t('eval.no_student')}</p>
            </div>`
        }
      </div>
    `;

    // Student selector change handler
    const select = document.getElementById('eval-student-select');
    if (select) {
      select.addEventListener('change', () => {
        const container = document.getElementById('eval-matrix-container');
        if (select.value) {
          container.innerHTML = buildEvaluationMatrix(Number(select.value));
          attachScoreInputListeners();
        } else {
          container.innerHTML = `<div class="empty-state">${icons.clipboard}<p data-i18n="eval.no_student">${i18n.t('eval.no_student')}</p></div>`;
        }
      });
    }

    if (!isAdmin) {
      attachScoreInputListeners();
    }
  }

  function buildEvaluationMatrix(studentId) {
    if (!specsData.length || !criteriaData.length) {
      return emptyState(i18n.t('common.no_data'));
    }

    // Build lookup for existing evaluations
    const evalLookup = {};
    evaluationsData
      .filter((e) => Number(e.student_id) === Number(studentId))
      .forEach((e) => {
        evalLookup[`${e.specialization_id}_${e.criterion_id}`] = e.score;
      });

    return `
      <div class="data-table-wrapper eval-matrix">
        <table class="data-table">
          <thead>
            <tr>
              <th data-i18n="eval.specialization">${i18n.t('eval.specialization')}</th>
              ${criteriaData.map((c) => `<th class="text-center">${escapeHtml(c.name)}</th>`).join('')}
            </tr>
          </thead>
          <tbody>
            ${specsData
              .map(
                (s) => `
              <tr>
                <td class="font-medium whitespace-nowrap">${escapeHtml(s.name)}</td>
                ${criteriaData
                  .map((c) => {
                    const key = `${s.id}_${c.id}`;
                    const value = evalLookup[key] || '';
                    return `
                    <td class="text-center">
                      <input type="number" min="1" max="10" step="1"
                        data-spec-id="${s.id}" data-crit-id="${c.id}"
                        value="${value}"
                        class="eval-score-input"
                        aria-label="${s.name} - ${c.name}">
                    </td>
                  `;
                  })
                  .join('')}
              </tr>
            `
              )
              .join('')}
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-end gap-3 mt-6">
        <button data-action="preview-results" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--bg-tertiary)] hover:bg-[var(--border-default)] text-[var(--text-primary)] rounded-lg text-sm font-medium transition-colors border border-[var(--border-default)]">
          ${icon('chart', 16)}
          <span data-i18n="eval.preview">${i18n.t('eval.preview')}</span>
        </button>
        <button data-action="save-evaluations" data-student-id="${studentId}" class="inline-flex items-center gap-2 px-5 py-2 bg-[var(--accent-primary)] hover:bg-[var(--accent-primary-hover)] text-white rounded-lg text-sm font-medium transition-colors">
          ${icon('check', 16)}
          <span data-i18n="eval.save">${i18n.t('eval.save')}</span>
        </button>
      </div>
    `;
  }

  function attachScoreInputListeners() {
    document.querySelectorAll('.eval-score-input').forEach((input) => {
      input.addEventListener('input', () => {
        const val = parseInt(input.value);
        input.classList.remove('score-low-input', 'score-mid-input', 'score-high-input');
        if (val >= 1 && val <= 3) input.classList.add('score-low-input');
        else if (val >= 4 && val <= 6) input.classList.add('score-mid-input');
        else if (val >= 7 && val <= 10) input.classList.add('score-high-input');
      });
      // Trigger initial color
      input.dispatchEvent(new Event('input'));
    });
  }

  function collectEvaluations(studentId) {
    const inputs = document.querySelectorAll('.eval-score-input');
    const evals = [];
    let valid = true;

    inputs.forEach((input) => {
      const val = parseInt(input.value);
      if (!val || val < 1 || val > 10) {
        valid = false;
        input.classList.add('score-low-input');
      } else {
        evals.push({
          student_id: studentId,
          specialization_id: Number(input.dataset.specId),
          criterion_id: Number(input.dataset.critId),
          score: val,
        });
      }
    });

    return { valid, evaluations: evals };
  }

  /* ----------------------------------------------------------
     Results Tab
  ---------------------------------------------------------- */
  function renderResultsTab() {
    const panel = document.getElementById('panel-results');
    if (!panel) return;

    const isAdmin = window.auth.isAdmin();
    const user = window.auth.getUser();
    const studentsList = isAdmin ? studentsData.filter((s) => s.role === 'student') : [user];

    panel.innerHTML = `
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-semibold" data-i18n="results.title">${i18n.t('results.title')}</h2>
      </div>

      ${
        isAdmin
          ? `
        <div class="mb-6">
          <label class="block text-sm font-medium mb-2 text-[var(--text-secondary)]" data-i18n="results.select_student">${i18n.t('results.select_student')}</label>
          <select id="results-student-select" class="w-full max-w-xs px-3 py-2 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-[var(--text-primary)] text-sm focus:border-[var(--accent-primary)] focus:outline-none">
            <option value="">— ${i18n.t('results.select_student')} —</option>
            ${studentsList.map((s) => `<option value="${s.id}">${escapeHtml(s.fullname || s.username)}</option>`).join('')}
          </select>
        </div>
      `
          : ''
      }

      <div id="results-content">
        ${!isAdmin ? buildResults(user?.id) : `<div class="empty-state">${icons.chart}<p data-i18n="results.no_data">${i18n.t('results.no_data')}</p></div>`}
      </div>
    `;

    const select = document.getElementById('results-student-select');
    if (select) {
      select.addEventListener('change', () => {
        const container = document.getElementById('results-content');
        if (select.value) {
          container.innerHTML = buildResults(Number(select.value));
          renderResultCharts(Number(select.value));
        } else {
          container.innerHTML = `<div class="empty-state">${icons.chart}<p data-i18n="results.no_data">${i18n.t('results.no_data')}</p></div>`;
        }
      });
    }

    if (!isAdmin && user?.id) {
      setTimeout(() => renderResultCharts(user.id), 100);
    }
  }

  function buildResults(studentId) {
    const studentEvals = evaluationsData.filter((e) => Number(e.student_id) === Number(studentId));

    if (studentEvals.length === 0) {
      return `<div class="empty-state">${icons.chart}<p data-i18n="results.no_data">${i18n.t('results.no_data')}</p></div>`;
    }

    const result = window.wsm.calculateWSM(studentEvals, criteriaData, specsData);

    if (!result.ranked.length) {
      return `<div class="empty-state">${icons.chart}<p data-i18n="results.no_data">${i18n.t('results.no_data')}</p></div>`;
    }

    const best = result.ranked[0];
    const tiedItems = result.ranked.filter((r) => r.isTied);
    const tieBanner = tiedItems.length > 0 ? `
      <div class="mb-6 p-4 rounded-xl border border-[var(--accent-warning)]/30 bg-[var(--accent-warning)]/10 text-[var(--accent-warning)] flex items-start gap-3">
        <div class="mt-0.5">${icons.alert}</div>
        <div>
          <h4 class="font-semibold text-sm">Tie Detected / Égalité détectée</h4>
          <p class="text-xs mt-1 text-[var(--text-secondary)]">
            Multiple specializations received equal overall scores: ${tiedItems.map(t => `<span class="font-semibold text-[var(--text-primary)]">${escapeHtml(t.specialization_name)}</span> (${(t.score * 10).toFixed(2)})`).join(', ')}.
          </p>
        </div>
      </div>
    ` : '';

    return `
      ${tieBanner}
      <!-- Recommendation Card -->
      <div class="recommendation-card bg-[var(--bg-secondary)] p-6 mb-8">
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
          <div class="w-20 h-20 rounded-2xl bg-[var(--accent-success)]/10 flex items-center justify-center text-[var(--accent-success)]">
            ${icon('trophy', 36)}
          </div>
          <div class="text-center sm:text-left flex-1">
            <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
              <span class="text-xs font-semibold uppercase tracking-wider text-[var(--accent-success)]" data-i18n="results.recommended_label">${i18n.t('results.recommended_label')}</span>
            </div>
            <h3 class="text-2xl font-bold mb-1">${escapeHtml(best.specialization_name)}</h3>
            <p class="text-[var(--text-secondary)]" data-i18n="results.recommended_spec">${i18n.t('results.recommended_spec')}</p>
          </div>
          <div class="text-center">
            <p class="text-4xl font-bold font-mono text-[var(--accent-success)]">${(best.score * 10).toFixed(1)}</p>
            <p class="text-xs text-[var(--text-muted)] mt-1" data-i18n="results.score">${i18n.t('results.score')}</p>
          </div>
        </div>
      </div>

      <!-- Ranking Table -->
      <div class="data-table-wrapper mb-8">
        <table class="data-table">
          <thead>
            <tr>
              <th data-i18n="results.rank">${i18n.t('results.rank')}</th>
              <th data-i18n="results.specialization">${i18n.t('results.specialization')}</th>
              ${criteriaData.map((c) => `<th class="text-center">${escapeHtml(c.name)}</th>`).join('')}
              <th class="text-center" data-i18n="results.weighted_score">${i18n.t('results.weighted_score')}</th>
            </tr>
          </thead>
          <tbody>
            ${result.ranked
              .map(
                (r) => `
              <tr${r.rank === 1 ? ' class="!bg-[var(--accent-success)]/5"' : ''}>
                <td>${rankBadge(r.rank)}${r.isTied ? ' <span class="text-xs text-[var(--text-muted)]">T</span>' : ''}</td>
                <td class="font-medium">${escapeHtml(r.specialization_name)}</td>
                ${criteriaData
                  .map((c) => {
                    const raw = r.rawScores[c.id] || 0;
                    const cls = raw <= 3 ? 'score-low' : raw <= 6 ? 'score-mid' : 'score-high';
                    return `<td class="text-center"><span class="${cls} px-2 py-0.5 rounded text-xs font-mono font-semibold">${raw}</span></td>`;
                  })
                  .join('')}
                <td class="text-center font-mono font-bold">${(r.score * 10).toFixed(2)}</td>
              </tr>
            `
              )
              .join('')}
          </tbody>
        </table>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-xl p-6">
          <h3 class="text-lg font-semibold mb-4" data-i18n="results.bar_chart_title">${i18n.t('results.bar_chart_title')}</h3>
          <div class="chart-container">
            <canvas id="bar-chart"></canvas>
          </div>
        </section>
        <section class="bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-xl p-6">
          <h3 class="text-lg font-semibold mb-4" data-i18n="results.radar_chart_title">${i18n.t('results.radar_chart_title')}</h3>
          <div class="chart-container">
            <canvas id="radar-chart"></canvas>
          </div>
        </section>
      </div>
    `;
  }

  function renderResultCharts(studentId) {
    const studentEvals = evaluationsData.filter((e) => Number(e.student_id) === Number(studentId));
    if (studentEvals.length === 0) return;

    const result = window.wsm.calculateWSM(studentEvals, criteriaData, specsData);
    if (!result.ranked.length) return;

    // Bar chart
    window.charts.renderBarChart('bar-chart', {
      labels: result.ranked.map((r) => r.specialization_name),
      scores: result.ranked.map((r) => r.score),
    });

    // Radar chart
    window.charts.renderRadarChart('radar-chart', {
      criteriaLabels: criteriaData.map((c) => c.name),
      datasets: specsData.map((s) => ({
        label: s.name,
        values: criteriaData.map((c) => result.matrix[s.id]?.[c.id] || 0),
      })),
    });
  }

  function rankBadge(rank) {
    const cls =
      rank === 1 ? 'rank-badge--gold' : rank === 2 ? 'rank-badge--silver' : rank === 3 ? 'rank-badge--bronze' : 'rank-badge--default';
    return `<span class="rank-badge ${cls}">${rank}</span>`;
  }

  /* ----------------------------------------------------------
     Modal Management
  ---------------------------------------------------------- */
  function openModal(title, bodyHtml, onSave) {
    let overlay = document.getElementById('modal-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'modal-overlay';
      overlay.className = 'modal-overlay';
      document.body.appendChild(overlay);
    }

    overlay.innerHTML = `
      <div class="modal-content p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold">${escapeHtml(title)}</h3>
          <button id="modal-close" class="p-1.5 rounded-md hover:bg-[var(--bg-tertiary)] text-[var(--text-muted)] hover:text-[var(--text-primary)] transition-colors">
            ${icon('close', 18)}
          </button>
        </div>
        <form id="modal-form">
          ${bodyHtml}
          <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-[var(--border-default)]">
            <button type="button" id="modal-cancel" class="px-4 py-2 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] rounded-lg hover:bg-[var(--bg-tertiary)] transition-colors" data-i18n="common.cancel">${i18n.t('common.cancel')}</button>
            <button type="submit" class="px-5 py-2 text-sm font-medium bg-[var(--accent-primary)] hover:bg-[var(--accent-primary-hover)] text-white rounded-lg transition-colors" data-i18n="common.save">${i18n.t('common.save')}</button>
          </div>
        </form>
      </div>
    `;

    // Show modal
    requestAnimationFrame(() => overlay.classList.add('active'));

    // Close handlers
    const close = () => {
      overlay.classList.remove('active');
      setTimeout(() => (overlay.innerHTML = ''), 300);
    };

    overlay.querySelector('#modal-close').addEventListener('click', close);
    overlay.querySelector('#modal-cancel').addEventListener('click', close);
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) close();
    });

    // Escape key
    const escHandler = (e) => {
      if (e.key === 'Escape') {
        close();
        document.removeEventListener('keydown', escHandler);
      }
    };
    document.addEventListener('keydown', escHandler);

    // Save handler
    overlay.querySelector('#modal-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      if (onSave) {
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        await onSave(data);
      }
      close();
    });
  }

  function modalField(label, name, type = 'text', value = '', required = true, options = null) {
    if (options) {
      return `
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1.5 text-[var(--text-secondary)]">${escapeHtml(label)}</label>
          <select name="${name}" class="w-full px-3 py-2 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-[var(--text-primary)] text-sm focus:border-[var(--accent-primary)] focus:outline-none" ${required ? 'required' : ''}>
            ${options.map((o) => `<option value="${o.value}" ${o.value === value ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('')}
          </select>
        </div>
      `;
    }

    return `
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1.5 text-[var(--text-secondary)]">${escapeHtml(label)}</label>
        <input type="${type}" name="${name}" value="${escapeHtml(String(value))}"
          class="w-full px-3 py-2 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-[var(--text-primary)] text-sm focus:border-[var(--accent-primary)] focus:outline-none"
          ${required ? 'required' : ''}
          ${type === 'number' ? 'step="0.01" min="0" max="1"' : ''}>
      </div>
    `;
  }

  /* ----------------------------------------------------------
     CRUD Operations (Modals)
  ---------------------------------------------------------- */
  function openStudentModal(student = null) {
    const isEdit = !!student;
    const title = isEdit ? i18n.t('students.edit') : i18n.t('students.add');
    const body = `
      ${student?.id ? `<input type="hidden" name="id" value="${student.id}">` : ''}
      ${modalField(i18n.t('students.username'), 'username', 'text', student?.username || '')}
      ${modalField(i18n.t('students.fullname'), 'fullname', 'text', student?.fullname || '')}
      ${modalField(i18n.t('students.email'), 'email', 'email', student?.email || '')}
      ${!isEdit ? modalField(i18n.t('auth.password'), 'password', 'password') : ''}
      ${modalField(
        i18n.t('students.role'),
        'role',
        'text',
        student?.role || 'student',
        true,
        [
          { value: 'student', label: i18n.t('header.role_student') },
          { value: 'admin', label: i18n.t('header.role_admin') },
        ]
      )}
    `;

    openModal(title, body, async (data) => {
      const nameParts = (data.fullname || '').trim().split(/\s+/);
      const firstName = nameParts[0] || data.username || 'Student';
      const lastName = nameParts.slice(1).join(' ') || 'User';

      const payload = {
        action: isEdit ? 'update' : 'create',
        first_name: firstName,
        last_name: lastName,
        email: data.email,
        username: data.username,
        role: data.role || 'student',
      };
      if (isEdit) payload.id = Number(data.id);
      if (data.password) payload.password = data.password;

      try {
        await api('students.php', 'POST', payload);
        showToast(i18n.t('common.success'), 'success');
        await loadAllData();
        renderStudentsTab();
      } catch (err) {
        // Fallback for demo / offline
        if (isEdit) {
          const idx = studentsData.findIndex((s) => s.id == data.id);
          if (idx >= 0) Object.assign(studentsData[idx], data);
        } else {
          data.id = studentsData.length + 1;
          studentsData.push(data);
        }
        showToast(i18n.t('common.success'), 'success');
        renderStudentsTab();
      }
    });
  }

  function openSpecModal(spec = null) {
    const isEdit = !!spec;
    const title = isEdit ? i18n.t('specs.edit') : i18n.t('specs.add');
    const body = `
      ${spec?.id ? `<input type="hidden" name="id" value="${spec.id}">` : ''}
      ${modalField(i18n.t('specs.code'), 'code', 'text', spec?.code || '')}
      ${modalField('Name (English)', 'name_en', 'text', spec?.name_en || spec?.name || '')}
      ${modalField('Name (French)', 'name_fr', 'text', spec?.name_fr || spec?.name || '')}
      ${modalField(i18n.t('specs.description'), 'description_en', 'text', spec?.description_en || spec?.description || '', false)}
    `;

    openModal(title, body, async (data) => {
      const payload = {
        action: isEdit ? 'update' : 'create',
        code: (data.code || '').toUpperCase(),
        name_en: data.name_en || data.code,
        name_fr: data.name_fr || data.name_en || data.code,
        description_en: data.description_en || '',
        description_fr: data.description_en || '',
      };
      if (isEdit) payload.id = Number(data.id);

      try {
        await api('specializations.php', 'POST', payload);
        showToast(i18n.t('common.success'), 'success');
        await loadAllData();
        renderSpecsTab();
      } catch (err) {
        if (isEdit) {
          const idx = specsData.findIndex((s) => s.id == data.id);
          if (idx >= 0) Object.assign(specsData[idx], data, { name: data.name_en });
        } else {
          data.id = specsData.length + 1;
          data.name = data.name_en;
          specsData.push(data);
        }
        showToast(i18n.t('common.success'), 'success');
        renderSpecsTab();
      }
    });
  }

  function openCriterionModal(criterion = null) {
    const isEdit = !!criterion;
    const title = isEdit ? i18n.t('criteria.edit') : i18n.t('criteria.add');
    const body = `
      ${criterion?.id ? `<input type="hidden" name="id" value="${criterion.id}">` : ''}
      ${modalField('Name (English)', 'name_en', 'text', criterion?.name_en || criterion?.name || '')}
      ${modalField('Name (French)', 'name_fr', 'text', criterion?.name_fr || criterion?.name || '')}
      ${modalField(
        i18n.t('criteria.type'),
        'type',
        'text',
        criterion?.type || 'maximize',
        true,
        [
          { value: 'maximize', label: i18n.t('criteria.maximize') },
          { value: 'minimize', label: i18n.t('criteria.minimize') },
        ]
      )}
      ${modalField(i18n.t('criteria.weight'), 'weight', 'number', criterion?.weight || '0.15')}
    `;

    openModal(title, body, async (data) => {
      const payload = {
        action: isEdit ? 'update' : 'create',
        name_en: data.name_en,
        name_fr: data.name_fr || data.name_en,
        type: data.type,
        weight: parseFloat(data.weight),
      };
      if (isEdit) payload.id = Number(data.id);

      try {
        await api('criteria.php', 'POST', payload);
        showToast(i18n.t('common.success'), 'success');
        await loadAllData();
        renderCriteriaTab();
      } catch (err) {
        if (isEdit) {
          const idx = criteriaData.findIndex((c) => c.id == data.id);
          if (idx >= 0) Object.assign(criteriaData[idx], data, { name: data.name_en });
        } else {
          data.id = criteriaData.length + 1;
          data.name = data.name_en;
          criteriaData.push(data);
        }
        showToast(i18n.t('common.success'), 'success');
        renderCriteriaTab();
      }
    });
  }

  async function deleteItem(type, id) {
    const confirmKeys = {
      student: 'students.delete_confirm',
      spec: 'specs.delete_confirm',
      criterion: 'criteria.delete_confirm',
    };

    if (!confirm(i18n.t(confirmKeys[type]))) return;

    const endpoints = {
      student: 'students.php',
      spec: 'specializations.php',
      criterion: 'criteria.php',
    };

    try {
      await api(endpoints[type], 'POST', { action: 'delete', id: Number(id) });
      showToast(i18n.t('common.success'), 'success');
      await loadAllData();
    } catch {
      // Demo mode: local removal
      if (type === 'student') studentsData = studentsData.filter((s) => s.id != id);
      else if (type === 'spec') specsData = specsData.filter((s) => s.id != id);
      else if (type === 'criterion') criteriaData = criteriaData.filter((c) => c.id != id);
      showToast(i18n.t('common.success'), 'success');
    }

    renderActiveTab();
  }

  /* ----------------------------------------------------------
     Event Delegation
  ---------------------------------------------------------- */
  function initEventDelegation() {
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;

      const action = btn.dataset.action;
      const id = btn.dataset.id ? Number(btn.dataset.id) : null;

      switch (action) {
        // Students
        case 'add-student':
          openStudentModal();
          break;
        case 'edit-student':
          openStudentModal(studentsData.find((s) => s.id === id));
          break;
        case 'delete-student':
          deleteItem('student', id);
          break;

        // Specializations
        case 'add-spec':
          openSpecModal();
          break;
        case 'edit-spec':
          openSpecModal(specsData.find((s) => s.id === id));
          break;
        case 'delete-spec':
          deleteItem('spec', id);
          break;

        // Criteria
        case 'add-criterion':
          openCriterionModal();
          break;
        case 'edit-criterion':
          openCriterionModal(criteriaData.find((c) => c.id === id));
          break;
        case 'delete-criterion':
          deleteItem('criterion', id);
          break;

        // Evaluations
        case 'save-evaluations': {
          const studentId = btn.dataset.studentId || document.getElementById('eval-student-select')?.value;
          if (!studentId) {
            showToast(i18n.t('eval.no_student'), 'warning');
            break;
          }
          const { valid, evaluations: evals } = collectEvaluations(studentId);
          if (!valid) {
            showToast(i18n.t('eval.error_range'), 'error');
            break;
          }
          saveEvaluations(studentId, evals);
          break;
        }

        case 'preview-results': {
          const sid = document.getElementById('eval-student-select')?.value || window.auth.getUser()?.id;
          if (!sid) break;
          const { valid: v, evaluations: previewEvals } = collectEvaluations(sid);
          if (!v) {
            showToast(i18n.t('eval.error_incomplete'), 'warning');
            break;
          }
          // Temporarily set evaluations for preview
          evaluationsData = evaluationsData.filter((e) => Number(e.student_id) !== Number(sid));
          evaluationsData.push(...previewEvals);
          switchTab('results');
          // Select student in results dropdown
          setTimeout(() => {
            const sel = document.getElementById('results-student-select');
            if (sel) {
              sel.value = sid;
              sel.dispatchEvent(new Event('change'));
            }
          }, 100);
          break;
        }
      }
    });
  }

  async function saveEvaluations(studentId, evals) {
    try {
      await api('evaluations.php', 'POST', {
        action: 'save',
        student_id: Number(studentId),
        evaluations: evals,
      });
      showToast(i18n.t('eval.saved'), 'success');
      await loadAllData();
    } catch {
      // Demo mode: merge into local data
      evals.forEach((ev) => {
        const idx = evaluationsData.findIndex(
          (e) =>
            Number(e.student_id) === Number(ev.student_id) &&
            Number(e.specialization_id) === Number(ev.specialization_id) &&
            Number(e.criterion_id) === Number(ev.criterion_id)
        );
        if (idx >= 0) {
          evaluationsData[idx].score = ev.score;
        } else {
          evaluationsData.push(ev);
        }
      });
      showToast(i18n.t('eval.saved'), 'success');
    }
  }

  /* ----------------------------------------------------------
     Helpers
  ---------------------------------------------------------- */
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
  }

  function emptyState(message) {
    return `
      <div class="empty-state">
        ${icons.chart}
        <p>${escapeHtml(message)}</p>
      </div>
    `;
  }

  /* ----------------------------------------------------------
     Loading Spinner helper
  ---------------------------------------------------------- */
  function showLoading(container) {
    const el = typeof container === 'string' ? document.getElementById(container) : container;
    if (el) {
      el.innerHTML = `<div class="flex items-center justify-center py-12"><span class="spinner spinner--lg"></span></div>`;
    }
  }

  /* ----------------------------------------------------------
     On Login
  ---------------------------------------------------------- */
  async function onLogin() {
    applyRoleView();
    showLoading('panel-dashboard');
    await loadAllData();
    switchTab('dashboard');
  }

  /* ----------------------------------------------------------
     Initialization
  ---------------------------------------------------------- */
  function init() {
    initTheme();
    initLanguage();
    initEventDelegation();
    window.auth.init();

    // Tab navigation
    document.querySelectorAll('.tab-btn').forEach((btn) => {
      btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });

    // Theme toggle
    document.getElementById('btn-theme')?.addEventListener('click', toggleTheme);

    // Language toggle
    document.getElementById('lang-en')?.addEventListener('click', () => {
      i18n.setLanguage('en');
      updateLangButtons();
      renderActiveTab();
    });
    document.getElementById('lang-fr')?.addEventListener('click', () => {
      i18n.setLanguage('fr');
      updateLangButtons();
      renderActiveTab();
    });

    // Check session
    window.auth.checkSession();
  }

  // Public API
  return { init, api, showToast, onLogin, icon };
})();

window.app = app;

// Bootstrap on DOM ready
document.addEventListener('DOMContentLoaded', () => app.init());
