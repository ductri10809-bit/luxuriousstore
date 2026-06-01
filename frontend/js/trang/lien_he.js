/**
 * lien_he.js
 */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-lien-he');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form).entries());

    try {
      const result = await Chung.goiApi('lien_he.php', {
        method: 'POST',
        body: JSON.stringify(data)
      });
      alert(result.message || (result.success ? 'Gui thanh cong' : 'Gui that bai'));
      if (result.success) form.reset();
    } catch (error) {
      alert('Loi ket noi server');
    }
  });
});
