<!-- src/layouts/AdminLayout.vue -->
<template>
  <div class="layout" :data-collapsed="isCollapsed">
    <TopAdmin @toggle-sidebar="toggleSidebar" />
    
    <div class="content-wrapper">
      <div class="sidebar">
        <MenuAdmin :collapsed="isCollapsed" @toggle="toggleSidebar" />
      </div>

      <div class="main">
        <main class="page-content">
          <router-view />
        </main>
        <!-- <Bot /> -->
      </div>
    </div>
  </div>
</template>

<script>
import MenuAdmin from "../components/MenuAdmin.vue";
import TopAdmin from "../components/TopAdmin.vue";
import Bot from "../components/Bot.vue";

export default {
  name: "AdminLayout",
  components: { MenuAdmin, TopAdmin, Bot },
  data() { return { isCollapsed: false }; },
  methods: { toggleSidebar(){ this.isCollapsed = !this.isCollapsed } }
}
</script>

<style scoped>
.layout{
  /* sidebar width controlled here */
  --sidebar-w: 240px;
  display: flex;
  flex-direction: column;
  background:#f5f7fb;
  min-height: 100vh;
}
.layout[data-collapsed="true"]{ --sidebar-w: 72px; }

/* Content wrapper chứa sidebar và main content */
.content-wrapper{
  display: grid;
  grid-template-columns: var(--sidebar-w) 1fr;
  flex: 1;
  min-height: 0;
}

/* Sidebar */
.sidebar{ 
  position: sticky; 
  top: 0; 
  height: calc(100vh - 64px); /* trừ chiều cao TopAdmin */
  overflow-y: auto;
}

/* Main content area */
.main{ 
  display: flex; 
  flex-direction: column; 
  min-height: 0;
  overflow-y: auto;
}

.page-content{ 
  flex: 1; 
  padding: 20px 30px; 
  overflow-y: auto; 
}
</style>
