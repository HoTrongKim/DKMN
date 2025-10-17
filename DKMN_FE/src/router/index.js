import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save

const routes = [
	{
		path: '/',
		component: () => import('../components/Client/TrangChu/TrangChu.vue'),
		meta: { layout: 'client' }
	},
	{
		path: '/TrangChu',
		component: () => import('../components/Client/TrangChu/TrangChu.vue'),
		meta: { layout: 'client' }
	},
	{
		path: '/client-login',
		component: () => import('../components/Client/Login/index.vue'),
		meta: { layout: 'client' }
	},
	{
		path: '/client-register',
		component: () => import('../components/Client/Register/index.vue'),
		meta: { layout: 'client' }
	},
	{
		path: '/client-menu',
		component: () => import('../layout/components/MenuClient.vue'),
		meta: { layout: 'client' }
	}
]

const router = createRouter({
	history: createWebHistory(),
	routes: routes
})

export default router