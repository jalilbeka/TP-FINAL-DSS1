/* ============================================================
   DSS University — Authentication Module
   ============================================================ */

const auth = (() => {
  let currentUser = null;

  /**
   * Get the current authenticated user (in-memory only).
   */
  function getUser() {
    return currentUser;
  }

  function isLoggedIn() {
    return currentUser !== null;
  }

  function isAdmin() {
    return currentUser?.role === 'admin';
  }

  /**
   * Attempt login via API.
   */
  async function login(username, password) {
    try {
      const res = await window.app.api('auth.php', 'POST', {
        action: 'login',
        username,
        password,
      });

      if (res && res.success) {
        currentUser = res.data || res.user;
        if (currentUser && !currentUser.id && currentUser.user_id) {
          currentUser.id = currentUser.student_id || currentUser.user_id;
        }
        onAuthStateChanged(true);
        return { success: true };
      }

      return { success: false, message: res?.message || i18n.t('auth.error_credentials') };
    } catch (err) {
      return { success: false, message: i18n.t('auth.error_credentials') };
    }
  }

  /**
   * Attempt registration via API.
   */
  async function register(username, password, email, fullname) {
    try {
      const nameParts = (fullname || '').trim().split(/\s+/);
      const firstName = nameParts[0] || username;
      const lastName = nameParts.slice(1).join(' ') || 'Student';

      const res = await window.app.api('auth.php', 'POST', {
        action: 'register',
        username,
        password,
        email,
        fullname,
        first_name: firstName,
        last_name: lastName,
      });

      if (res && res.success) {
        return { success: true, message: i18n.t('auth.register_success') };
      }

      return { success: false, message: res?.message || i18n.t('auth.error_exists') };
    } catch (err) {
      return { success: false, message: i18n.t('auth.error_exists') };
    }
  }

  /**
   * Check session with backend.
   */
  async function checkSession() {
    try {
      const res = await window.app.api('auth.php', 'POST', { action: 'check' });
      if (res && res.success && res.data) {
        currentUser = res.data;
        if (currentUser && !currentUser.id && currentUser.user_id) {
          currentUser.id = currentUser.student_id || currentUser.user_id;
        }
        onAuthStateChanged(true);
        return true;
      }
    } catch {
      // not logged in
    }
    onAuthStateChanged(false);
    return false;
  }

  /**
   * Logout.
   */
  async function logout() {
    try {
      await window.app.api('auth.php', 'POST', { action: 'logout' });
    } catch {
      // ignore
    }
    currentUser = null;
    onAuthStateChanged(false);
  }

  /**
   * Toggle UI based on authentication state.
   */
  function onAuthStateChanged(loggedIn) {
    const authScreen = document.getElementById('auth-screen');
    const dashboard = document.getElementById('dashboard');

    if (loggedIn) {
      authScreen?.classList.add('hidden');
      dashboard?.classList.remove('hidden');
      window.app?.onLogin?.();
    } else {
      authScreen?.classList.remove('hidden');
      dashboard?.classList.add('hidden');
    }
  }

  /**
   * Initialize auth UI event listeners.
   */
  function init() {
    // Tab toggle between login and register
    const loginTab = document.getElementById('auth-tab-login');
    const registerTab = document.getElementById('auth-tab-register');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    loginTab?.addEventListener('click', () => {
      loginTab.classList.add('active');
      registerTab.classList.remove('active');
      loginForm.classList.remove('hidden');
      registerForm.classList.add('hidden');
    });

    registerTab?.addEventListener('click', () => {
      registerTab.classList.add('active');
      loginTab.classList.remove('active');
      registerForm.classList.remove('hidden');
      loginForm.classList.add('hidden');
    });

    // Switch links
    document.getElementById('switch-to-register')?.addEventListener('click', (e) => {
      e.preventDefault();
      registerTab?.click();
    });

    document.getElementById('switch-to-login')?.addEventListener('click', (e) => {
      e.preventDefault();
      loginTab?.click();
    });

    // Login form submission
    loginForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const username = document.getElementById('login-username')?.value.trim();
      const password = document.getElementById('login-password')?.value;

      if (!username || !password) {
        window.app.showToast(i18n.t('auth.error_empty'), 'error');
        return;
      }

      const btn = loginForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.innerHTML = `<span class="spinner"></span>`;

      const result = await login(username, password);

      btn.disabled = false;
      btn.innerHTML = `<span data-i18n="auth.login_btn">${i18n.t('auth.login_btn')}</span>`;

      if (!result.success) {
        window.app.showToast(result.message, 'error');
      }
    });

    // Register form submission
    registerForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const username = document.getElementById('register-username')?.value.trim();
      const password = document.getElementById('register-password')?.value;
      const email = document.getElementById('register-email')?.value.trim();
      const fullname = document.getElementById('register-fullname')?.value.trim();

      if (!username || !password || !email || !fullname) {
        window.app.showToast(i18n.t('auth.error_empty'), 'error');
        return;
      }

      const btn = registerForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.innerHTML = `<span class="spinner"></span>`;

      const result = await register(username, password, email, fullname);

      btn.disabled = false;
      btn.innerHTML = `<span data-i18n="auth.register_btn">${i18n.t('auth.register_btn')}</span>`;

      if (result.success) {
        window.app.showToast(result.message, 'success');
        loginTab?.click();
        if (document.getElementById('login-username')) {
          document.getElementById('login-username').value = username;
        }
      } else {
        window.app.showToast(result.message, 'error');
      }
    });

    // Logout button
    document.getElementById('btn-logout')?.addEventListener('click', async () => {
      await logout();
      window.app.showToast(i18n.t('header.logout'), 'info');
    });
  }

  return { init, getUser, isLoggedIn, isAdmin, login, register, logout, checkSession };
})();

window.auth = auth;
