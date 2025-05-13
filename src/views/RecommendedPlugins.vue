<template>
  <div class="htpm-recommended-plugins htpm-inner-page-wrapper">
    <div v-if="loading || isInitialLoading" class="loading">
      <el-skeleton :rows="3" animated />
    </div>

    <div v-else-if="error" class="error-message">
      <el-alert
        :title="error"
        type="error"
        :closable="false"
      />
    </div>

    <div v-else>
      <el-tabs v-model="activeTab" @tab-change="handleTabChange">
        <el-tab-pane 
          v-for="tab in tabs" 
          :key="tab.title" 
          :label="tab.title" 
          :name="tab.title"
        >
          <plugin-grid 
            :plugin-list="currentTabPlugins" 
            :is-loading="loading || isInitialLoading"
          />
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRecommendedPluginsStore } from '@/store/modules/recommendedPlugins'
import PluginGrid from '@/components/recommended/PluginGrid.vue'
import { ElTabs, ElTabPane, ElSkeleton } from 'element-plus'

const store = useRecommendedPluginsStore()
const loading = computed(() => store.loading)
const error = computed(() => store.error)
const tabs = computed(() => store.tabs)
const activeTab = ref('')
const isInitialLoading = ref(true)

const currentTabPlugins = computed(() => {
  if (!tabs.value.length) return []
  const currentTab = tabs.value.find(tab => tab.title === activeTab.value)
  return currentTab ? currentTab.plugins : []
})

const initializePlugins = async () => {
  try {
    await store.fetchTabs()
    if (tabs.value.length > 0) {
      activeTab.value = tabs.value[0].title
    }
  } catch (error) {
    console.error('Error initializing plugins:', error)
  } finally {
    isInitialLoading.value = false
  }
}

const handleTabChange = (tabTitle) => {
  activeTab.value = tabTitle;
}

onMounted(() => {
  initializePlugins()
})

const getPluginImage = (slug) => {
  return `${assetsUrl.value}/images/extensions/${slug}.png`
}

const isPluginInstalled = (slug) => {
  return installedPlugins.value.includes(slug)
}

const getPluginActionText = (plugin) => {
  if (isPluginInstalled(plugin.slug)) {
    return 'Installed'
  }
  return 'Install Now'
}

</script>

<style scoped>
.recommended-plugins {
  padding: 20px;
}

.tabs {
  margin-bottom: 20px;
  border-bottom: 1px solid #ddd;
}

.tabs button {
  padding: 10px 20px;
  margin-right: 10px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 14px;
  color: #666;
}

.tabs button.active {
  color: #2271b1;
  border-bottom: 2px solid #2271b1;
}

.plugins-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.plugin-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  background: white;
  transition: transform 0.2s;
  width:100%;
}

.plugin-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.plugin-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.plugin-info {
  padding: 15px;
}

.plugin-info h3 {
  margin: 0 0 15px 0;
  font-size: 16px;
}

.plugin-actions {
  display: flex;
  justify-content: flex-end;
}

.install-button, .get-pro-button {
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  text-decoration: none;
}

.install-button {
  background: #2271b1;
  color: white;
  border: none;
}

.install-button.installed {
  background: #46b450;
}

.get-pro-button {
  background: #ff6b6b;
  color: white;
}
</style>
