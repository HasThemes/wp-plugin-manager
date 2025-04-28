// PluginList.vue
<template>
  <div class="htpm-plugins">
    <div class="htpm-plugins-header">
      <h2>Manage Plugins</h2>
      <div class="htpm-plugins-actions">
        <el-input
          v-model="searchQuery"
          placeholder="Search plugins..."
          :prefix-icon="Search"
        />
        <el-button @click="handleFilter" :icon="Filter">Filter</el-button>
        <el-button @click="handleSort" :icon="Sort">Sort</el-button>
      </div>
    </div>
    <div class="plugin-list">
      <!-- Only show active plugins based on the new design -->
      <div v-for="plugin in filteredActivePlugins" :key="plugin.id" class="plugin-item">
        <div class="plugin-info">
          <div class="plugin-icon-image" :class="getPluginIconClass(plugin.name)">
            <el-icon v-if="plugin.name.includes('Query')"><Monitor /></el-icon>
            <el-icon v-else-if="plugin.name.includes('Elementor')"><Edit /></el-icon>
            <el-icon v-else-if="plugin.name.includes('HT Mega')"><Grid /></el-icon>
            <el-icon v-else><Box /></el-icon>
          </div>
          <div class="plugin-details">
            <h3>{{ plugin.name }}</h3>
            <div class="plugin-status">
              <span class="status-dot" :class="{ active: plugin.active }"></span>
              <span class="status-text">{{ plugin.active ? 'Active' : 'Inactive' }}</span>
            </div>
          </div>
        </div>
        <div class="plugin-actions">
          <el-switch
            v-model="plugin.active"
            @change="() => togglePlugin(plugin)"
            class="plugin-switch"
          />
          <el-button
            type="default"
            :icon="Setting"
            circle
            class="settings-button"
            :class="{ 'active-settings': plugin.active }"
            @click="openSettings(plugin)"
          />
        </div>
      </div>
    </div>

    <!-- Plugin Settings Modal -->
    <plugin-settings-modal
      :visible="showSettings"
      :plugin="selectedPlugin"
      @update:visible="showSettings = $event"
      @save="savePluginSettings"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Search, Filter, Sort, Box, Setting, Monitor, Edit, Grid } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import PluginSettingsModal from './PluginSettingsModal.vue'
import { usePluginStore } from '../store/plugins'

const store = usePluginStore()
const searchQuery = ref('')
const showSettings = ref(false)
const selectedPlugin = ref(null)

// Props for component flexibility
const props = defineProps({
  plugins: {
    type: Array,
    default: () => []
  }
})

// Emits for component events
const emit = defineEmits(['toggle', 'update-settings'])

// Use store plugins or prop plugins
const storePlugins = computed(() => store.plugins)
const activePlugins = computed(() => store.activePlugins)

// Filter only active plugins (new design requirement)
const filteredActivePlugins = computed(() => {
  // Use plugins from props if provided, otherwise use from store
  const pluginsSource = props.plugins.length > 0 
    ? props.plugins.filter(plugin => plugin.active) 
    : activePlugins.value
  
  if (!searchQuery.value) return pluginsSource
  
  return pluginsSource.filter(plugin => 
    plugin.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Load plugins on component mount if using store
onMounted(async () => {
  if (props.plugins.length === 0) {
    try {
      await store.fetchPlugins()
    } catch (error) {
      ElMessage.error('Failed to load plugins')
      console.error('Error loading plugins:', error)
    }
  }
})

// Filter plugins handling
const handleFilter = () => {
  ElMessage.info('Filter functionality will be implemented here')
}

// Sort plugins handling
const handleSort = () => {
  ElMessage.info('Sort functionality will be implemented here')
}

// Toggle plugin active status
const togglePlugin = async (plugin) => {
  try {
    if (props.plugins.length > 0) {
      // If using props, emit event to parent
      emit('toggle', plugin)
    } else {
      // If using store, update through store
      await store.togglePlugin(plugin)
    }
  } catch (error) {
    ElMessage.error('Failed to toggle plugin status')
    console.error('Error toggling plugin:', error)
  }
}

// Open settings modal for a plugin
const openSettings = (plugin) => {
  selectedPlugin.value = plugin
  showSettings.value = true
}

// Save plugin settings
const savePluginSettings = async (data) => {
  try {
    // Update plugin settings
    if (props.plugins.length > 0) {
      // If using props, emit event to parent
      emit('update-settings', data)
    } else {
      // If using store, update plugin in store
      const { plugin, settings } = data
      await store.updatePluginSettings(plugin.id, settings)
    }
    
    ElMessage.success('Plugin settings saved successfully')
  } catch (error) {
    ElMessage.error('Failed to save plugin settings')
    console.error('Error saving plugin settings:', error)
  }
}

// Get plugin icon class based on plugin name
const getPluginIconClass = (name) => {
  if (name.includes('Query Monitor')) return 'query-monitor'
  if (name.includes('Elementor')) return 'elementor'
  if (name.includes('HT Mega')) return 'htmega'
  if (name.includes('WooCommerce')) return 'woocommerce'
  if (name.includes('Yoast SEO')) return 'yoast'
  return 'default'
}
</script>

<style lang="scss" scoped>
.htpm-plugins {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  margin-top: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);

  .htpm-plugins-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;

    h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .htpm-plugins-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }
  }

  .plugin-list {
    .plugin-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px;
      border-bottom: 1px solid #ebeef5;
      transition: background-color 0.3s;

      &:last-child {
        border-bottom: none;
      }

      &:hover {
        background-color: #f8f9fa;
      }

      .plugin-info {
        display: flex;
        align-items: center;
        gap: 12px;

        .plugin-icon-image {
          width: 32px;
          height: 32px;
          border-radius: 4px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 16px;
          color: #fff;
          background: #e9ecef;

          &.query-monitor {
            background: #4c6ef5;
          }

          &.elementor {
            background: #92003b;
          }

          &.htmega {
            background: #0073aa;
          }

          &.woocommerce {
            background: #7f54b3;
          }

          &.yoast {
            background: #a4286a;
          }

          :deep(.el-icon) {
            font-size: 16px;
          }
        }

        .plugin-details {
          h3 {
            margin: 0 0 2px;
            font-size: 13px;
            font-weight: 500;
            color: #1f2937;
          }

          .plugin-status {
            display: flex;
            align-items: center;
            gap: 6px;

            .status-dot {
              width: 6px;
              height: 6px;
              border-radius: 50%;
              background: #909399;

              &.active {
                background: #10b981;
              }
            }

            .status-text {
              font-size: 12px;
              color: #909399;
            }
          }
        }
      }

      .plugin-actions {
        display: flex;
        align-items: center;
        gap: 12px;

        .settings-button {
          border: 1px solid #dcdfe6;
          color: #606266;

          &.active-settings {
            color: #409eff;
            border-color: #409eff;
          }

          &:hover {
            color: #409eff;
            border-color: #409eff;
          }
        }
      }
    }
  }

  .el-input {
    width: 200px;
  }
}
/* Active settings button style */
.settings-button.active-settings {
  color: #409eff !important;
  border-color: #409eff !important;
  background-color: rgba(64, 158, 255, 0.1) !important;
}

/* Plugin item active state */
.plugin-item.active {
  border-left: 3px solid #409eff;
  background-color: rgba(64, 158, 255, 0.05);
}

/* Add hover effect to settings button */
.settings-button:hover {
  color: #409eff !important;
  border-color: #409eff !important;
  background-color: rgba(64, 158, 255, 0.1) !important;
}

/* Improved styling for the plugin modal */
.plugin-settings-modal .el-dialog {
  border-radius: 8px;
  overflow: hidden;
}

.plugin-settings-modal .el-dialog__header {
  background-color: #f8f9fa;
}

.plugin-settings-modal .form-field {
  background-color: #fff;
  transition: all 0.3s ease;
}

.plugin-settings-modal .form-field:hover {
  background-color: #f8f9fa;
}

/* Improve switch appearance when active */
.el-switch.is-checked .el-switch__core {
  border-color: #10b981 !important;
  background-color: #10b981 !important;
}

/* Improve page selection dropdowns */
.plugin-settings-modal .el-select .el-select__tags {
  max-height: 80px;
  overflow-y: auto;
}

/* Add better styling to the dialog buttons */
.plugin-settings-modal .dialog-footer .el-button--primary {
  background-color: #409eff;
  border-color: #409eff;
}

.plugin-settings-modal .dialog-footer .el-button--primary:hover {
  background-color: #66b1ff;
  border-color: #66b1ff;
}

/* Additional style for the filter and search section */
.htpm-plugins-actions {
  background-color: #f8f9fa;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
}
</style>