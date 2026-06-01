/**
 * dang_nhap.js
 */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-dang-nhap');
  if (!form) return;

  const params = new URLSearchParams(window.location.search);
  const returnUrl = params.get('returnUrl');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
      const result = await Chung.goiApi('dang_nhap.php', {
        method: 'POST',
        body: JSON.stringify(data)
      });

      if (result.success) {
        if (result.data?.role === 'admin') {
          window.location.href = '../../admin/dashboard.html';
          return;
        }
        window.location.href = returnUrl || '../trang_chu/trang_chu.html';
      } else {
        alert(result.message || 'Dang nhap that bai');
      }
    } catch (error) {
      console.error('dang_nhap error', error);
      alert(error && error.message ? error.message : 'Loi ket noi server');
    }
  });
});
