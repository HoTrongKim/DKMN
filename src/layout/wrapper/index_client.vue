<template>
	<div class="wrapper">
		<Top />
		<div class="page-wrapper d-flex">
			<MenuClient />
			<main class="page-content flex-grow-1">
				<router-view />
			</main>
		</div>
		<Bot />
	</div>
</template>
<script>
import Top from "../components/Top.vue";
import MenuClient from "../components/MenuClient.vue";
import Bot from "../components/Bot.vue";
export default {
	name: "ClientLayout",
	components: { Top, MenuClient, Bot },
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
