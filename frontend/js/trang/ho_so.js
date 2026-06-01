/**
 * ho_so.js
 */
document.addEventListener('DOMContentLoaded', async () => {
  try {
    const result = await Chung.goiApi('nguoi_dung.php');
    if (!result.success || !result.data) {
      window.location.href = '../dang_nhap/dang_nhap.html';
      return;
    }

    const user = result.data;
    const form = document.getElementById('form-ho-so');
    if (form) {
      form.querySelector('[name="ho_ten"]').value = user.ho_ten || '';
      form.querySelector('[name="email"]').value = user.email || '';
      form.querySelector('[name="sdt"]').value = user.sdt || '';
    }

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(form).entries());
      const update = await Chung.goiApi('nguoi_dung.php', {
        method: 'PUT',
        body: JSON.stringify(data)
      });
      alert(update.message || (update.success ? 'Cap nhat thanh cong' : 'Cap nhat that bai'));
    });
  } catch (error) {
    window.location.href = '../dang_nhap/dang_nhap.html';
  }
});
