<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Multi-Criteria Decision Support System for University Specialization Selection - University 8 Mai 1945 Guelma">
  <title>Specialization Decision Support System — Univ. Guelma</title>

  <!-- Google Fonts: Inter & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Tailwind CSS via CDN with custom configuration -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            'bg-primary': 'var(--bg-primary)',
            'bg-secondary': 'var(--bg-secondary)',
            'bg-tertiary': 'var(--bg-tertiary)',
            'bg-input': 'var(--bg-input)',
            'border-default': 'var(--border-default)',
            'border-active': 'var(--border-active)',
            'text-primary': 'var(--text-primary)',
            'text-secondary': 'var(--text-secondary)',
            'text-muted': 'var(--text-muted)',
            'accent-primary': 'var(--accent-primary)',
            'accent-primary-hover': 'var(--accent-primary-hover)',
            'accent-success': 'var(--accent-success)',
            'accent-warning': 'var(--accent-warning)',
            'accent-danger': 'var(--accent-danger)',
            'accent-info': 'var(--accent-info)',
          },
          fontFamily: {
            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
          }
        }
      }
    };
  </script>

  <!-- Chart.js via CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)] font-sans antialiased min-h-screen flex flex-col transition-colors duration-200">

  <!-- ============================================================
       1. Authentication Screen (Login / Register)
       ============================================================ -->
  <div id="auth-screen" class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 relative overflow-hidden">
    <!-- Subtle gradient glow backdrop -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-[var(--accent-primary)]/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-[var(--chart-2)]/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
      <!-- University & System Branding Card -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[var(--accent-primary)]/10 text-[var(--accent-primary)] border border-[var(--accent-primary)]/20 mb-4 shadow-lg shadow-[var(--accent-primary)]/5">
          <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
          </svg>
        </div>
        <div class="text-xs uppercase tracking-widest text-[var(--accent-primary)] font-semibold mb-1" data-i18n="auth.badge">
          University 8 Mai 1945 Guelma · Computer Science Dept
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-[var(--text-primary)]" data-i18n="auth.title">
          Specialization Decision Support System
        </h1>
        <p class="text-sm text-[var(--text-secondary)] mt-2 leading-relaxed" data-i18n="auth.subtitle">
          Multi-Criteria Academic Orientation & Specialization Selection powered by the Weighted Sum Model (WSM)
        </p>
      </div>

      <!-- Main Auth Box -->
      <div class="bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-2xl p-6 sm:p-8 shadow-xl backdrop-blur-sm">
        <!-- Auth Tabs (Login / Register) -->
        <div class="flex border-b border-[var(--border-default)] mb-6">
          <button id="auth-tab-login" type="button" class="auth-tab active">
            <span data-i18n="auth.login">Login</span>
          </button>
          <button id="auth-tab-register" type="button" class="auth-tab">
            <span data-i18n="auth.register">Register</span>
          </button>
        </div>

        <!-- Login Form -->
        <form id="login-form" class="space-y-4">
          <div>
            <label for="login-username" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-secondary)] mb-1.5" data-i18n="auth.username">
              Username
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[var(--text-muted)]">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </span>
              <input type="text" id="login-username" required autocomplete="username"
                class="w-full pl-10 pr-3.5 py-2.5 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-sm text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:border-[var(--accent-primary)] focus:ring-1 focus:ring-[var(--accent-primary)] focus:outline-none transition-colors"
                placeholder="Enter username">
            </div>
          </div>

          <div>
            <label for="login-password" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-secondary)] mb-1.5" data-i18n="auth.password">
              Password
            </label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[var(--text-muted)]">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input type="password" id="login-password" required autocomplete="current-password"
                class="w-full pl-10 pr-3.5 py-2.5 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-sm text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:border-[var(--accent-primary)] focus:ring-1 focus:ring-[var(--accent-primary)] focus:outline-none transition-colors"
                placeholder="••••••••">
            </div>
          </div>

          <button type="submit" class="w-full mt-2 py-2.5 px-4 bg-[var(--accent-primary)] hover:bg-[var(--accent-primary-hover)] text-white font-medium rounded-lg text-sm shadow-md hover:shadow-lg shadow-[var(--accent-primary)]/20 transition-all flex items-center justify-center gap-2">
            <span data-i18n="auth.login_btn">Sign In</span>
          </button>

          <p class="text-center text-xs text-[var(--text-secondary)] mt-4">
            <span data-i18n="auth.no_account">Don't have an account?</span>
            <a href="#" id="switch-to-register" class="text-[var(--accent-primary)] hover:underline ml-1 font-medium" data-i18n="auth.switch_register">Register here</a>
          </p>
        </form>

        <!-- Register Form -->
        <form id="register-form" class="space-y-4 hidden">
          <div>
            <label for="register-username" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-secondary)] mb-1.5" data-i18n="auth.username">
              Username
            </label>
            <input type="text" id="register-username" required
              class="w-full px-3.5 py-2.5 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-sm text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:border-[var(--accent-primary)] focus:outline-none transition-colors"
              placeholder="e.g. jdoe">
          </div>

          <div>
            <label for="register-fullname" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-secondary)] mb-1.5" data-i18n="auth.fullname">
              Full Name
            </label>
            <input type="text" id="register-fullname" required
              class="w-full px-3.5 py-2.5 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-sm text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:border-[var(--accent-primary)] focus:outline-none transition-colors"
              placeholder="First and Last Name">
          </div>

          <div>
            <label for="register-email" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-secondary)] mb-1.5" data-i18n="auth.email">
              Email Address
            </label>
            <input type="email" id="register-email" required
              class="w-full px-3.5 py-2.5 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-sm text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:border-[var(--accent-primary)] focus:outline-none transition-colors"
              placeholder="student@univ-guelma.dz">
          </div>

          <div>
            <label for="register-password" class="block text-xs font-semibold uppercase tracking-wider text-[var(--text-secondary)] mb-1.5" data-i18n="auth.password">
              Password
            </label>
            <input type="password" id="register-password" required minlength="6"
              class="w-full px-3.5 py-2.5 bg-[var(--bg-input)] border border-[var(--border-default)] rounded-lg text-sm text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:border-[var(--accent-primary)] focus:outline-none transition-colors"
              placeholder="Min. 6 characters">
          </div>

          <button type="submit" class="w-full mt-2 py-2.5 px-4 bg-[var(--accent-primary)] hover:bg-[var(--accent-primary-hover)] text-white font-medium rounded-lg text-sm shadow-md hover:shadow-lg shadow-[var(--accent-primary)]/20 transition-all flex items-center justify-center gap-2">
            <span data-i18n="auth.register_btn">Create Account</span>
          </button>

          <p class="text-center text-xs text-[var(--text-secondary)] mt-4">
            <span data-i18n="auth.has_account">Already have an account?</span>
            <a href="#" id="switch-to-login" class="text-[var(--accent-primary)] hover:underline ml-1 font-medium" data-i18n="auth.switch_login">Login here</a>
          </p>
        </form>
      </div>

      <!-- Language & Project Information Bar on Auth Screen -->
      <div class="flex items-center justify-between text-xs text-[var(--text-muted)] mt-6 px-2">
        <div class="flex items-center bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-lg p-0.5 text-xs font-mono">
          <button id="auth-lang-en" type="button" class="px-2 py-0.5 rounded transition-colors text-[var(--text-secondary)] hover:text-[var(--text-primary)]">EN</button>
          <span class="text-[var(--border-default)]">|</span>
          <button id="auth-lang-fr" type="button" class="px-2 py-0.5 rounded transition-colors text-[var(--text-secondary)] hover:text-[var(--text-primary)]">FR</button>
        </div>
        <span data-i18n="auth.footer_note">Weighted Sum Model (WSM) · Academic Year 2025/2026</span>
      </div>
    </div>
  </div>

  <!-- ============================================================
       2. Main Dashboard Application (Hidden until authenticated)
       ============================================================ -->
  <div id="dashboard" class="hidden flex-1 flex flex-col">
    <!-- Top Header Bar -->
    <header class="h-16 border-b border-[var(--border-default)] bg-[var(--bg-primary)]/90 backdrop-blur-md sticky top-0 z-30 transition-colors">
      <div class="max-w-7xl mx-auto h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
        <!-- Brand / University Identity -->
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-[var(--accent-primary)]/10 text-[var(--accent-primary)] border border-[var(--accent-primary)]/20 flex items-center justify-center shadow-sm">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-bold text-base tracking-tight" data-i18n="header.title">DSS University</span>
              <span class="hidden sm:inline-block text-[10px] font-mono uppercase bg-[var(--bg-tertiary)] text-[var(--text-muted)] border border-[var(--border-default)] px-1.5 py-0.5 rounded">WSM</span>
            </div>
            <p class="hidden sm:block text-[11px] text-[var(--text-muted)] leading-tight">Univ. Guelma · ISIL 3rd Year</p>
          </div>
        </div>

        <!-- Controls: Language, Theme, User Profile & Logout -->
        <div class="flex items-center gap-2 sm:gap-3">
          <!-- Language Pill Switcher -->
          <div class="flex items-center bg-[var(--bg-secondary)] border border-[var(--border-default)] rounded-lg p-0.5 text-xs font-mono">
            <button id="lang-en" class="px-2 py-1 rounded transition-colors text-[var(--text-secondary)] hover:text-[var(--text-primary)]">EN</button>
            <span class="text-[var(--border-default)]">|</span>
            <button id="lang-fr" class="px-2 py-1 rounded transition-colors text-[var(--text-secondary)] hover:text-[var(--text-primary)]">FR</button>
          </div>

          <!-- Dark/Light Theme Toggle -->
          <button id="btn-theme" type="button" class="p-2 rounded-lg bg-[var(--bg-secondary)] border border-[var(--border-default)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors" title="Toggle theme" aria-label="Toggle theme">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
          </button>

          <!-- User Info & Role Badge -->
          <div class="flex items-center gap-2.5 pl-2 border-l border-[var(--border-default)]">
            <div class="w-8 h-8 rounded-full bg-[var(--bg-tertiary)] border border-[var(--border-default)] flex items-center justify-center text-xs font-bold text-[var(--accent-primary)]">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="hidden md:block text-left">
              <div id="header-username" class="text-xs font-semibold text-[var(--text-primary)] truncate max-w-[120px]">User</div>
              <span id="header-role-badge" class="text-[10px] font-medium px-1.5 py-0.2 rounded-full bg-[var(--accent-primary)]/15 text-[var(--accent-primary)]">Student</span>
            </div>
          </div>

          <!-- Logout Button -->
          <button id="btn-logout" type="button" class="p-2 rounded-lg bg-[var(--bg-secondary)] border border-[var(--border-default)] text-[var(--text-muted)] hover:text-[var(--accent-danger)] hover:border-[var(--accent-danger)]/30 transition-colors" title="Logout" aria-label="Logout">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </div>
      </div>
    </header>

    <!-- Navigation Tabs Bar -->
    <nav class="border-b border-[var(--border-default)] bg-[var(--bg-secondary)]/70 backdrop-blur-sm sticky top-16 z-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex space-x-1 sm:space-x-4 overflow-x-auto no-scrollbar">
        <button class="tab-btn active inline-flex items-center gap-2 py-3.5 px-3 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap transition-colors" data-tab="dashboard">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          <span data-i18n="nav.dashboard">Dashboard</span>
        </button>

        <button class="tab-btn inline-flex items-center gap-2 py-3.5 px-3 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap transition-colors" data-tab="students" data-role="admin">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span data-i18n="nav.students">Students</span>
        </button>

        <button class="tab-btn inline-flex items-center gap-2 py-3.5 px-3 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap transition-colors" data-tab="specializations">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10M6 10h10"/></svg>
          <span data-i18n="nav.specializations">Specializations</span>
        </button>

        <button class="tab-btn inline-flex items-center gap-2 py-3.5 px-3 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap transition-colors" data-tab="criteria">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          <span data-i18n="nav.criteria">Criteria</span>
        </button>

        <button class="tab-btn inline-flex items-center gap-2 py-3.5 px-3 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap transition-colors" data-tab="evaluations">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
          <span data-i18n="nav.evaluations">Evaluations</span>
        </button>

        <button class="tab-btn inline-flex items-center gap-2 py-3.5 px-3 border-b-2 font-medium text-xs sm:text-sm whitespace-nowrap transition-colors" data-tab="results">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          <span data-i18n="nav.results">Results</span>
        </button>
      </div>
    </nav>

    <!-- Main Content Panels Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- 1. Dashboard Tab Panel -->
      <section id="panel-dashboard" class="tab-panel">
        <div class="flex items-center justify-center py-16">
          <div class="spinner spinner--lg"></div>
        </div>
      </section>

      <!-- 2. Students Tab Panel -->
      <section id="panel-students" class="tab-panel hidden"></section>

      <!-- 3. Specializations Tab Panel -->
      <section id="panel-specializations" class="tab-panel hidden"></section>

      <!-- 4. Criteria Tab Panel -->
      <section id="panel-criteria" class="tab-panel hidden"></section>

      <!-- 5. Evaluations Tab Panel -->
      <section id="panel-evaluations" class="tab-panel hidden"></section>

      <!-- 6. Results Tab Panel -->
      <section id="panel-results" class="tab-panel hidden"></section>
    </main>

    <!-- Application Footer -->
    <footer class="border-t border-[var(--border-default)] bg-[var(--bg-secondary)]/40 py-6 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[var(--text-muted)]">
        <div>
          <span class="font-semibold text-[var(--text-secondary)]">University of Guelma</span> · Department of Computer Science · Module DSS (3rd Year ISIL)
        </div>
        <div class="flex items-center gap-4">
          <span>Weighted Sum Model (WSM)</span>
          <span>·</span>
          <span>© 2026 Academic Practical Project</span>
        </div>
      </div>
    </footer>
  </div>

  <!-- Modal Overlay Container -->
  <div id="modal-overlay" class="modal-overlay"></div>

  <!-- AI Support Chatbot Widget -->
  <div id="chatbot-widget" class="chatbot-widget no-print">
    <!-- Chat Window -->
    <section id="chatbot-window" class="chatbot-window" role="dialog" aria-label="DSS AI Assistant" aria-hidden="true">
      <header class="chatbot-header">
        <div class="chatbot-header__info">
          <div class="chatbot-avatar" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 8V4H8"/>
              <rect width="16" height="12" x="4" y="8" rx="2"/>
              <path d="M2 14h2"/>
              <path d="M20 14h2"/>
              <path d="M15 13v2"/>
              <path d="M9 13v2"/>
            </svg>
          </div>
          <div class="chatbot-header__titles">
            <h2 class="chatbot-header__title" data-i18n="chatbot.title">DSS Assistant</h2>
            <p class="chatbot-header__subtitle" data-i18n="chatbot.subtitle">Orientation &amp; Navigation Help</p>
          </div>
        </div>
        <div class="chatbot-header__actions">
          <button id="chatbot-clear" type="button" class="chatbot-icon-btn" data-i18n-title="chatbot.clear" title="Clear chat" aria-label="Clear chat">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
              <path d="M3 3v5h5"/>
            </svg>
          </button>
          <button id="chatbot-close" type="button" class="chatbot-icon-btn" data-i18n-title="chatbot.close" title="Close" aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </header>

      <div id="chatbot-messages" class="chatbot-messages" aria-live="polite"></div>

      <form id="chatbot-form" class="chatbot-input-bar" novalidate>
        <div class="chatbot-input-wrap">
          <textarea
            id="chatbot-input"
            class="chatbot-input"
            rows="1"
            maxlength="1000"
            data-i18n-placeholder="chatbot.placeholder"
            placeholder="Ask about specializations, WSM, or navigation..."
            aria-label="Chat message"
          ></textarea>
          <span id="chatbot-char-count" class="chatbot-char-count hidden">0/1000</span>
        </div>
        <button id="chatbot-send" type="submit" class="chatbot-send-btn" data-i18n-title="chatbot.send" title="Send message" aria-label="Send message" disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"/>
            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
          </svg>
        </button>
      </form>
    </section>

    <!-- Floating Chat Toggle Button -->
    <button id="chatbot-toggle" type="button" class="chatbot-toggle" aria-expanded="false" aria-controls="chatbot-window" data-i18n-title="chatbot.toggle_open" title="Open AI assistant" aria-label="Open AI assistant">
      <svg class="chatbot-toggle__icon chatbot-toggle__icon--chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
      <svg class="chatbot-toggle__icon chatbot-toggle__icon--close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
  </div>

  <!-- Toast Notification Container -->
  <div id="toast-container" class="toast-container"></div>

  <!-- Scripts -->
  <script src="assets/js/i18n.js"></script>
  <script src="assets/js/wsm.js"></script>
  <script src="assets/js/charts.js"></script>
  <script src="assets/js/auth.js"></script>
  <script src="assets/js/app.js"></script>
  <script src="assets/js/chatbot.js"></script>
</body>
</html>
