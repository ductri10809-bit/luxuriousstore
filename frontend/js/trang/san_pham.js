/**
 * san_pham.js
 */
document.addEventListener('DOMContentLoaded', async () => {
  const grid = document.getElementById('danh-sach-san-pham');
  const countEl = document.getElementById('products-count');
  if (!grid) return;

  let allProducts = [];

  async function taiBoLoc() {
    try {
      const [dm, mau] = await Promise.all([
        Chung.goiApi('danh_muc.php'),
        Chung.goiApi('san_pham.php?mau_sac_list=1'),
      ]);
      const selCat = document.getElementById('danh-muc');
      const selMau = document.getElementById('mau-sac');
      if (selCat && dm.success) {
        dm.data.forEach(c => {
          selCat.innerHTML += `<option value="${c.id}">${c.ten}</option>`;
        });
      }
      if (selMau && mau.success) {
        mau.data.forEach(c => {
          selMau.innerHTML += `<option value="${c.color_name}">${c.color_name}</option>`;
        });
      }
    } catch (e) {
      console.error(e);
    }
  }

  function sapXepClient(data, sort) {
    const list = [...data];
    if (sort === 'gia_tang') list.sort((a, b) => a.gia - b.gia);
    else if (sort === 'gia_giam') list.sort((a, b) => b.gia - a.gia);
    return list;
  }

  async function taiSanPham(query = '') {
    try {
      const result = await Chung.goiApi(`san_pham.php${query}`);
      if (!result.success || !result.data) {
        grid.innerHTML = '<p class="products-empty">Không có sản phẩm.</p>';
        return;
      }
      const sort = document.getElementById('sap-xep')?.value || 'moi_nhat';
      allProducts = sapXepClient(result.data, sort);
      grid.innerHTML = allProducts.map(renderTheSanPham).join('');
      grid.querySelectorAll('.product-card').forEach(card => {
        const sp = allProducts.find(p => p.id === parseInt(card.dataset.id, 10));
        if (sp) {
          card.dataset.gia = sp.gia;
          card.__sp = sp;
        }
      });
      if (countEl) countEl.textContent = `${allProducts.length} sản phẩm`;
    } catch (error) {
      console.error('Loi tai san pham:', error);
    }
  }

  function buildQuery() {
    const cat = document.getElementById('danh-muc')?.value;
    const mau = document.getElementById('mau-sac')?.value;
    const params = new URLSearchParams();
    if (cat) params.set('category_id', cat);
    if (mau) params.set('mau_sac', mau);
    const q = params.toString();
    return q ? `?${q}` : '';
  }

  await taiBoLoc();
  await taiSanPham();

  document.getElementById('form-bo-loc')?.addEventListener('submit', (e) => {
    e.preventDefault();
    taiSanPham(buildQuery());
  });

  document.getElementById('bo-loc-reset')?.addEventListener('click', () => {
    document.getElementById('form-bo-loc')?.reset();
    taiSanPham();
  });

  document.addEventListener('searchQuery', (e) => {
    taiSanPham(`?tim=${encodeURIComponent(e.detail.q)}`);
  });
});
