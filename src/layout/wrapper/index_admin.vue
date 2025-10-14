<template>
	<div class="wrapper">
		<Top />
		<div class="page-wrapper d-flex">
			<MenuAdmin />
			<main class="page-content flex-grow-1">
				<router-view />
			</main>
		</div>
		<Bot />
	</div>
</template>
<script>
import Top from "../components/Top.vue";
import MenuAdmin from "../components/MenuAdmin.vue";
import Bot from "../components/Bot.vue";
export default {
	name: "AdminLayout",
	components: { Top, MenuAdmin, Bot },
};
</script>
<style scoped>
.wrapper {
	background: #f5f7fb;
	min-height: 100vh;
	display: flex;
	flex-direction: column;
}
.page-wrapper {
	flex: 1;
}
.page-content {
	padding: 16px;
}
</style>
