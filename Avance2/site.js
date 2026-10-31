// Muestra la sesión y activa formularios protegidos con CSRF.
(async function () {
  const login = document.getElementById('siteLogin');
  const signed = document.getElementById('siteSigned');
  const user = document.getElementById('siteUser');
  const csrf = document.getElementById('siteLogoutCsrf');
  const forms = document.querySelectorAll('form[data-csrf-write]');

  function report(form, message) {
    const status = form.querySelector('[data-security-message]');
    if (status) status.textContent = message;
  }

  try {
    const response = await fetch('session_status.php', {
      method: 'GET',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { 'Accept': 'application/json' }
    });
    if (!response.ok) throw new Error('Sin acceso a la sesión');
    const current = await response.json();

    if (current.authenticated === true
      && typeof current.logout_csrf === 'string' && current.logout_csrf) {
      if (login && signed && user && csrf) {
        user.textContent = current.display_name
          ? 'Hola, ' + current.display_name : 'Sesión iniciada';
        csrf.value = current.logout_csrf;
        signed.hidden = false;
        login.hidden = true;
      }
    }

    // Los formularios privados permanecen ocultos hasta comprobar sesión y token.
    const authenticated = current.authenticated === true;
    const tokenValid = typeof current.write_csrf === 'string'
      && /^[a-f0-9]{64}$/i.test(current.write_csrf);
    const guardedAreas = [
      { editorId: 'recipeEditor', noticeId: 'recipeAuthNotice',
        loginMessage: 'Inicia sesión para publicar recetas' },
      { editorId: 'donationEditor', noticeId: 'donationAuthNotice',
        loginMessage: 'Inicia sesión para registrar donaciones' }
    ];
    guardedAreas.forEach(area => {
      const editor = document.getElementById(area.editorId);
      const notice = document.getElementById(area.noticeId);
      if (!editor || !notice) return;

      editor.hidden = !(authenticated && tokenValid);
      notice.hidden = authenticated && tokenValid;
      if (!editor.hidden) return;

      const heading = notice.querySelector('h2');
      if (heading) heading.textContent = authenticated
        ? 'No se pudo activar el formulario seguro'
        : area.loginMessage;
    });

    forms.forEach(form => {
      const field = form.querySelector('input[name="csrf_token"]');
      const submit = form.querySelector('button[type="submit"]');
      const requiresAuth = form.dataset.requireAuth === 'true';
      if (!field || !submit) return;

      if (requiresAuth && current.authenticated !== true) {
        report(form, 'Inicia sesión para utilizar este formulario.');
        return;
      }
      if (typeof current.write_csrf !== 'string'
          || !/^[a-f0-9]{64}$/i.test(current.write_csrf)) {
        report(form, 'No se pudo activar el formulario seguro. Actualiza la página.');
        return;
      }

      field.value = current.write_csrf;
      submit.disabled = false;
      report(form, '');
    });
  } catch (_) {
    ['recipeAuthNotice', 'donationAuthNotice'].forEach(id => {
      const notice = document.getElementById(id);
      if (notice) {
        const heading = notice.querySelector('h2');
        if (heading) heading.textContent = 'No se pudo verificar la sesión';
      }
    });
    forms.forEach(form => {
      report(form, 'No se pudo verificar la sesión. Actualiza la página.');
    });
  }
})();
