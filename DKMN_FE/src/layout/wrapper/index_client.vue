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
<style>
@import "../../assets/plugins/simplebar/css/simplebar.css";
@import "../../assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css";
@import "../../assets/plugins/metismenu/css/metisMenu.min.css";
@import "../../assets/css/pace.min.css";
@import "../../assets/css/bootstrap.min.css";
@import "../../assets/css/bootstrap-extended.css";
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap");
@import "../../assets/css/app.css";
@import "../../assets/css/icons.css";
@import "../../assets/css/dark-theme.css";
@import "../../assets/css/semi-dark.css";
@import "../../assets/css/header-colors.css";
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css");
</style>

