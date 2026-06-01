/**
 * tim_kiem.js
 */
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('tim-kiem');
  if (!input) return;

  let timeout;
  input.addEventListener('input', () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      const q = input.value.trim();
      if (q.length >= 2) {
        document.dispatchEvent(new CustomEvent('searchQuery', { detail: { q } }));
      }
    }, 300);
  });
});
