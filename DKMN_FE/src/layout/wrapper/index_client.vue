<template>
	<div class="wrapper">
		<Top />
		<div>
			<MenuClient />
			<main>
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
	name: "AdminLayout",
	components: { Top, MenuClient, Bot },
};
</script>
<style scoped>
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css");
</style>
