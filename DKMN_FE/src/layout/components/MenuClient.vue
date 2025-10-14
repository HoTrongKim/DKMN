<template>
	<aside class="sidebar-wrapper" aria-label="Client Menu">
		<div class="sidebar-header">
			<div>
				<img src="/src/assets/images/logo-icon.png" class="logo-icon" alt="logo" />
			</div>
			<div>
				<h4 class="logo-text">DKMN</h4>
			</div>
		</div>
		<ul class="metismenu" id="menu">
			<li>
				<a href="/">
					<div class="parent-icon"><i class='bx bx-home-circle'></i></div>
					<div class="menu-title">Trang chủ</div>
				</a>
			</li>
			<li>
				<a href="#">
					<div class="parent-icon"><i class='bx bx-cart'></i></div>
					<div class="menu-title">Sản phẩm</div>
				</a>
			</li>
			<li>
				<a href="#">
					<div class="parent-icon"><i class='bx bx-user'></i></div>
					<div class="menu-title">Tài khoản</div>
				</a>
			</li>
		</ul>
	</aside>
</template>
<script>
export default {
	name: "MenuClient",
};
</script>
<style scoped>
.sidebar-wrapper {
	width: 260px;
	background: #ffffff;
	border-right: 1px solid #e9ecef;
	min-height: 100vh;
}
.sidebar-header {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 16px;
}
.logo-icon {
	height: 32px;
}
.logo-text {
	margin: 0;
}
#menu {
	list-style: none;
	margin: 0;
	padding: 8px 0;
}
#menu > li > a {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 12px 16px;
	color: #212529;
	text-decoration: none;
	border-radius: 8px;
	margin: 4px 8px;
}
#menu > li > a:hover {
	background: #f8f9fa;
}
.parent-icon {
	font-size: 20px;
	width: 24px;
	text-align: center;
}
.menu-title {
	font-weight: 500;
}
</style>
