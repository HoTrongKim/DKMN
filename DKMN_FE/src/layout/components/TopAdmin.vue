<template>
  <header class="topbar">
    <div class="left">
      <button class="menu-toggle" @click="$emit('toggle-sidebar')" aria-label="Thu gọn/mở rộng sidebar">
        <i class="fas fa-bars"></i>
      </button>
      <div class="brand" @click="$router.push('/view/home')">
        <img class="logo" src="/src/assets/image/logo.png" alt="DKMN" />
        <h2>DKMN Admin</h2>
      </div>
    </div>
    <div class="right">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Tìm kiếm nhanh..." />
      </div>
      <div class="topbar-icons">
        <i class="fas fa-bell"></i>
        <i class="fas fa-cog"></i>
      </div>
      <div class="avatar">
        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="admin avatar" />
      </div>
    </div>
  </header>
  
</template>

<script>
export default {
  name: 'TopAdmin'
}
</script>

<style scoped>
.topbar {
  background-color: #0b3b6e; color: white;
  padding: 12px 24px; display: flex; justify-content: space-between; align-items: center;
  position: sticky; top: 0; z-index: 10;
}
.left { display: flex; align-items: center; gap: 12px; }
.menu-toggle { background: transparent; border: none; color: #fff; font-size: 20px; cursor: pointer; }
.brand { display: flex; align-items: center; gap: 10px; cursor: pointer; }
.brand .logo { width: 28px; height: 28px; border-radius: 6px; }
.brand h2 { font-size: 1.1rem; margin: 0; }
.topbar .right { display: flex; align-items: center; gap: 18px; }
.search-box { position: relative; }
.search-box input {
  padding: 6px 28px 6px 10px;
  border: 1px solid rgba(255,255,255,.35);
  border-radius: 20px; outline: none; transition: .2s;
  font-size: .9rem; background: rgba(255,255,255,.15); color: #fff;
}
.search-box input::placeholder { color: rgba(255,255,255,.8); }
.search-box i { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #fff; font-size: .9rem; }
.topbar-icons i { font-size: 1.1rem; color: #fff; margin: 0 6px; cursor: pointer; transition: .2s; }
.topbar-icons i:hover { color: #e6f0ff; }
.avatar img { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #e6f0ff; cursor: pointer; transition: .2s; }
.avatar img:hover { transform: scale(1.05); }
</style>