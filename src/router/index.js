import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save

const routes = [
	{
		path: '/',
		component: () => import('../components/Client/TrangChu/TrangChu.vue'),
		meta: { layout: 'client' }
	}
]

const router = createRouter({
	history: createWebHistory(),
	routes: routes
})

export default router