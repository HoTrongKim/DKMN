<template>
	<div class="wrapper client-wrapper">
		<Top />
		<div class="client-shell">
			<MenuClient />
			<main class="client-main">
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

.client-wrapper {
	min-height: 100vh;
	display: flex;
	flex-direction: column;
	background: transparent;
}

.client-shell {
	flex: 1;
	display: flex;
	flex-direction: column;
}

.client-main {
	flex: 1;
	display: flex;
	flex-direction: column;
	padding: 0 12px;
	background: none;
}

.client-main > * {
	flex: 1;
}

@media (max-width: 575.98px) {
	.client-main {
		padding: 0 8px;
	}
}
</style>

<style>
/* Force chat widget to stay in bottom-right on client pages */
#tawkchat-minified-wrapper,
#tawkchat-maximized-wrapper,
#tawkchat-container,
#tawkchat-iframe-container,
#tawkchat-prompt-wrapper,
#tawkchat-prompt-container {
  position: fixed !important;
  bottom: 8px !important;
  right: 8px !important;
  left: auto !important;
  transform: none !important;
  z-index: 2147483641 !important;
}

[id^="tawkchat-"][style*="bottom"],
[class^="tawk-"][style*="bottom"],
#tawkchat-minified-header,
#tawkchat-minified-icon,
#tawkchat-status-text-container {
  bottom: 8px !important;
  right: 8px !important;
  left: auto !important;
  transform: none !important;
}

@media (max-width: 575.98px) {
  #tawkchat-minified-wrapper,
  #tawkchat-maximized-wrapper,
  #tawkchat-container,
  #tawkchat-iframe-container,
  #tawkchat-prompt-wrapper,
  #tawkchat-prompt-container {
    bottom: 6px !important;
    right: 6px !important;
  }

  [id^="tawkchat-"][style*="bottom"],
  [class^="tawk-"][style*="bottom"],
  #tawkchat-minified-header,
  #tawkchat-minified-icon,
  #tawkchat-status-text-container {
    bottom: 6px !important;
    right: 6px !important;
  }
}
</style>
