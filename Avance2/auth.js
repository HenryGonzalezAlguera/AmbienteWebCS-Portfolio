/* Interacciones visuales del acceso.
 * No contiene autenticación: los permisos y contraseñas los valida PHP.
 */
(() => {
  const status = document.getElementById('estado');
  const params = new URLSearchParams(window.location.search);
  if (status) {
    const messages = {
      registered: 'Registro completado. Ya puedes iniciar sesión.',
      logout: 'Cerraste sesión correctamente.',
      credentials: 'Correo electrónico o contraseña incorrectos.',
      server: 'No fue posible completar la operación. Inténtalo nuevamente.',
      invalid: 'Revisa los datos del formulario y confirma tu contraseña.',
      duplicate: 'Ese correo electrónico ya está registrado.'
    };
    const key = params.get('registered') === '1' ? 'registered'
      : params.get('logout') === '1' ? 'logout'
      : params.get('error');
    status.textContent = Object.prototype.hasOwnProperty.call(messages, key)
      ? messages[key] : '';
    if (['credentials', 'server', 'invalid', 'duplicate'].includes(key)) {
      status.classList.add('error');
    }
  }

  document.querySelectorAll('[data-toggle-password]').forEach(button => {
    const input = document.getElementById(button.dataset.togglePassword);
    if (!input || input.tagName !== 'INPUT') return;
    button.addEventListener('click', () => {
      const reveal = input.type === 'password';
      input.type = reveal ? 'text' : 'password';
      button.textContent = reveal ? 'Ocultar' : 'Mostrar';
      button.setAttribute('aria-pressed', String(reveal));
      button.setAttribute('aria-label', reveal ? 'Ocultar contraseña' : 'Mostrar contraseña');
      input.focus({ preventScroll: true });
    });
  });
})();
