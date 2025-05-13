<template>
  <div class="recommended-plugins">
    <div v-if="loading || isInitialLoading" class="loading">
      <el-skeleton :rows="3" animated />
    </div>

    <div v-else-if="error" class="error">
      <el-alert
        :title="error"
        type="error"
        :closable="false"
      />
    </div>

    <div v-else>
      <div class="tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab.title"
          :class="{ active: activeTab === tab.title }"
          @click="handleTabChange(tab.title)"
        >
          {{ tab.title }}
        </button>
      </div>

      <PluginGrid 
        :plugin-list="currentTabPlugins"
        :is-loading="loading || isInitialLoading"
        :plugin-states="PLUGIN_STATES"
        :get-plugin-button-text="getPluginButtonText"
        @plugin-toggled="handlePluginToggle"
    />
      <!-- <div class="plugins-grid">
        
        <div 
          v-for="plugin in currentTabPlugins" 
          :key="plugin.slug"
          class="plugin-card"
        >
          <img 
            :src="getPluginImage(plugin.slug)" 
            :alt="plugin.name"
            class="plugin-image"
          >
          <div class="plugin-info">
            <h3>{{ plugin.name }}</h3>
            <div class="plugin-actions">
              <button 
                v-if="!plugin.link"
                :class="['install-button', { 'installed': isPluginInstalled(plugin.slug) }]"
                @click="handlePluginAction(plugin)"
              >
                {{ getPluginActionText(plugin) }}
              </button>
              <a 
                v-else 
                :href="plugin.link" 
                target="_blank" 
                class="get-pro-button"
              >
                Get Pro
              </a>
            </div>
          </div>
        </div>
      </div> -->
    </div>
  </div>

</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useRecommendedPluginsStore } from '@/store/modules/recommendedPlugins'
import PluginGrid from '@/components/recommended/PluginGrid.vue'
import { usePluginManager } from '@/composables/usePluginManager'
import { ElNotification } from 'element-plus'

const htpmLocalizeData = ref(window.HTPMM || {});

const store = useRecommendedPluginsStore()
const { tabs, installedPlugins, loading, error, assetsUrl } = storeToRefs(store)
const { 
            PLUGIN_STATES,
            getPluginButtonText, 
            handlePluginAction, 
            fetchPluginStatus,
            initializePluginsWithData
        } = usePluginManager()
const activeTab = ref('Recommended')

const isInitialLoading = ref(true)

const initializePlugins = async () => {
    try {
        isInitialLoading.value = true
        await store.fetchTabs()
        if (tabs.value.length > 0) {
            const activeTabPlugins = tabs.value.find(tab => tab.title === activeTab.value)?.plugins || []
            await initializePluginsWithData(activeTabPlugins)
        }
    } catch (error) {
        console.error('Error initializing plugins:', error)
    } finally {
        isInitialLoading.value = false
    }
}

onMounted(() => {
    initializePlugins()
})
const currentTabPlugins = computed(() => {
    if (!tabs.value || !tabs.value.length) return []
    const currentTab = tabs.value.find(tab => tab.title === activeTab.value)
    return currentTab?.plugins || []
})

console.log(currentTabPlugins.value);
const handleTabChange = async (tabTitle) => {
    activeTab.value = tabTitle
    const newTab = tabs.value.find(tab => tab.title === tabTitle)
    if (newTab?.plugins) {
        await initializePluginsWithData(newTab.plugins)
    }
}

const handlePluginToggle = async (plugin) => {
    try {
        await handlePluginAction(plugin)
        // After action, refresh the plugin status
        const updatedPlugins = await initializePluginsWithData(currentTabPlugins.value)
        // Update the store with new plugin data
        store.$patch((state) => {
            const tabIndex = state.tabs.findIndex(tab => tab.title === activeTab.value)
            if (tabIndex !== -1) {
                state.tabs[tabIndex].plugins = updatedPlugins
            }
        })
    } catch (error) {
        console.error('Error handling plugin action:', error)
        ElNotification({
            title: 'Error',
            message: error.message || 'Failed to perform plugin action',
            type: 'error'
        })
    }
}

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
