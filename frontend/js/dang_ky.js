/**
 * dang_ky.js
 */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-dang-ky');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    if (data.password !== data.confirm_password) {
      alert('Mat khau xac nhan khong khop');
      return;
    }

    try {
      const result = await Chung.goiApi('dang_ky.php', {
        method: 'POST',
        body: JSON.stringify(data)
      });

      if (result.success) {
        window.location.href = '../dang_nhap/dang_nhap.html';
      } else {
        alert(result.message || 'Dang ky that bai');
      }
    } catch (error) {
      console.error('dang_ky error', error);
      // If the error contains a message from the API wrapper, show it
      const msg = error && error.message ? error.message : 'Lỗi kết nối server';
      alert(msg);
    }
  });
});
