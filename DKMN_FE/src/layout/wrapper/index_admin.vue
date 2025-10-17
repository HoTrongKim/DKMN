<template>
	<div class="wrapper">
		<TopAdmin />
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
import MenuAdmin from "../components/MenuAdmin.vue";
import Bot from "../components/Bot.vue";
import TopAdmin from "../components/TopAdmin.vue";

export default {
	name: "AdminLayout",
	components: { TopAdmin, MenuAdmin, Bot },
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
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css");
</style>
