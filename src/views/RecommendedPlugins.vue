<template>
  <div class="recommended-plugins">
    <div v-if="loading" class="loading">
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
          @click="activeTab = tab.title"
        >
          {{ tab.title }}
        </button>
      </div>

      <PluginGrid 
        :plugin-list="pluginList"
        :is-loading="loading"
        :plugin-states="PLUGIN_STATES"
        :get-plugin-button-text="getPluginButtonText"
        @plugin-toggled="handlePluginAction"
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

const store = useRecommendedPluginsStore()
const { tabs, installedPlugins, loading, error, assetsUrl } = storeToRefs(store)
const activeTab = ref('Recommended')
const PLUGIN_STATES = {
    NOT_INSTALLED: 'not_installed',
    INSTALLING: 'installing',
    INACTIVE: 'inactive',
    ACTIVATING: 'activating',
    ACTIVE: 'active'
}

onMounted(() => {
  // Load plugins data
  store.fetchTabs()
})
const pluginList = ref([
            {
                name: 'Support Genix Lite – Support Tickets Managing System',
                slug: 'support-genix-lite',
                description: 'Support Genix is a support ticket system for WordPress and WooCommerce.',
                status: 'inactive',
                isLoading: false,
                icon: null
            },
            {
                name: 'Pixelavo – Facebook Pixel Conversion API',
                slug: 'pixelavo',
                description: 'Easy connection of Facebook pixel to your online store.',
                status: 'not_installed',
                isLoading: false,
                icon: null
            },
            {
                name: 'Whols – Wholesale Prices and B2B Store',
                slug: 'whols',
                description: 'WooCommerce Wholesale plugin for B2B store management.',
                status: 'inactive',
                isLoading: false,
                icon: null
            },
            {
                name: "JustTables – WooCommerce Product Table",
                slug: 'just-tables',
                description: "JustTables is an incredible WordPress plugin that lets you showcase all your WooCommerce products in a sortable and filterable table view.",
                status: 'active',
                isLoading: false,
                icon: null,
            },
            // {
            //     name: "Multi Currency For WooCommerce",
            //     slug: 'wc-multi-currency',
            //     description: "WC Multicurrency is a prominent currency switcher plugin for WooCommerce.",
            //     status: 'inactive',
            //     isLoading: false,
            //     icon: null,
            // }
        ]);
// Computed properties
const currentTabPlugins = computed(() => {
  const currentTab = tabs.value.find(tab => tab.title === activeTab.value)
  return currentTab ? currentTab.plugins : []
})

const handleAction = async (plugin) => {
            const index = pluginList.value.findIndex(p => p.slug === plugin.slug)
            if (index === -1) return

            pluginList.value[index].isLoading = true
            
            try {
                const newStatus = await handlePluginAction(plugin, (slug, status) => {
                    const targetIndex = pluginList.value.findIndex(p => p.slug === slug)
                    if (targetIndex !== -1) {
                        pluginList.value[targetIndex].status = status
                    }
                })

                if (newStatus) {
                    pluginList.value[index].status = newStatus
                }
            } finally {
                pluginList.value[index].isLoading = false
            }
        }

        const initializePlugins = async () => {
            isInitialLoading.value = true;
            try {
                const slugs = pluginList.value.map(p => p.slug);
                let enrichedPlugins = null;

                // Check cache first
                const cacheKey = slugs.join(',')+'cache';
                const cachedData = dataFetchingCache.get(cacheKey);
                
                if (cachedData) {
                    enrichedPlugins = cachedData;
                } else {
                    // Fetch both WordPress.org data and installation status
                    enrichedPlugins = await initializePluginsWithData(pluginList.value);
                    dataFetchingCache.set(cacheKey, enrichedPlugins, {duration: 6000});
                }

                // Update plugins with enriched data
                pluginList.value = enrichedPlugins.map(plugin => ({
                    ...plugin,
                    icon: plugin.icons?.['2x'] || plugin.icons?.['1x'] || plugin.icons?.default || null
                }));

            } catch (error) {
                console.error('Error loading plugins:', error);
            } finally {
                isInitialLoading.value = false;
            }

        }

// Methods
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

const handlePluginAction = async (plugin) => {
  if (!plugin.installed) {
    await store.installPlugin(plugin)
  }
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
