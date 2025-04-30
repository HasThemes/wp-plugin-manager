
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
      </div>
    </div>
    
    <!-- Loading indicator shown until plugins are fully loaded -->
    <div v-if="loading" class="loading-indicator">
      <el-skeleton :rows="5" animated />
    </div>
    
    <!-- Only show plugin list when loading is complete -->
    <div v-else class="plugin-list">
      <!-- Show only WordPress-activated plugins -->
      <div 
        v-for="plugin in filteredPlugins" 
        :key="plugin.id" 
        class="plugin-item"
        :class="{ 'plugin-disabled': (plugin.enable_deactivation == 'yes') }"
      >
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
              <span class="status-dot" :class="{ active: (plugin.enable_deactivation == 'yes') }"></span>
              <span class="status-text">{{ plugin.enable_deactivation == 'yes' ? 'Optimized' : 'Not Optimized Yet' }}</span>
            </div>
          </div>
        </div>
        <div class="plugin-actions">
          <!-- Toggle whether the plugin is loaded or not -->
          <el-switch
            :model-value="plugin.enable_deactivation == 'yes'"
            @update:model-value="() => togglePluginLoading(plugin)"
            class="plugin-switch"
          />
          <el-button
            type="default"
            :icon="Setting"
            circle
            class="settings-button"
            :class="{ 'active-settings': plugin.enable_deactivation == 'yes' }"
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

<script>
import { ref, computed, onMounted } from 'vue'
import { Search, Box, Setting, Monitor, Edit, Grid } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import PluginSettingsModal from './PluginSettingsModal.vue'
import { usePluginStore } from '../store/plugins'

export default {
  name: 'PluginList',
  components: {
    PluginSettingsModal
  },
  setup() {
    const store = usePluginStore()
    const searchQuery = ref('')
    const showSettings = ref(false)
    const selectedPlugin = ref(null)
    const loading = ref(true)
    const plugins = ref([])

    // Load plugins on component mount
    onMounted(async () => {
      loading.value = true
      
      try {
        // First fetch all plugins
        await store.fetchPlugins()
        
        // Wait for all plugin settings to load
        const promises = []
        for (const plugin of store.plugins) {
          if (plugin.wpActive) {
            promises.push(store.fetchPluginSettings(plugin.id))
          }
        }
        
        await Promise.all(promises)
        
        // Only after all settings have loaded, prepare the plugins list
        plugins.value = store.plugins.map(plugin => {
          const settings = store.settings[plugin.id] || null;
          
          return {
            ...plugin,
            settings: settings,
            // A plugin is enabled only if settings explicitly set enable_deactivation to 'no'
            enable_deactivation: !settings || settings.enable_deactivation !== 'no'
          };
        })
      } catch (error) {
        ElMessage.error('Failed to load plugins')
        console.error('Error loading plugins:', error)
      } finally {
        loading.value = false
      }
    })

    // Filter plugins based on search query
    const filteredPlugins = computed(() => {
      // First filter only active WordPress plugins
      const activePlugins = plugins.value.filter(plugin => plugin.wpActive)
      
      // Then apply search filter if there's a query
      if (!searchQuery.value) return activePlugins
      
      return activePlugins.filter(plugin => 
        plugin.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      )
    })

    // Toggle plugin loading status
    const togglePluginLoading = async (plugin) => {
      try {
        // Toggle the enable_deactivation state
        plugin.enable_deactivation = plugin.enable_deactivation == 'yes' ? 'no' : 'yes';
        
        // Get existing settings
        let existingSettings = store.settings[plugin.id];
        
        // If no settings exist, create default ones
        if (!existingSettings || Object.keys(existingSettings).length === 0) {
          existingSettings = {
            enable_deactivation: 'yes',
            device_type: 'all',
            condition_type: 'disable_on_selected',
            uri_type: 'page',
            post_types: ['page', 'post'],
            posts: [],
            pages: [],
            condition_list: {
              name: ['uri_equals'],
              value: [''],
            }
          };
        }
        
        // Create a complete copy of existing settings
        const settings = JSON.parse(JSON.stringify(existingSettings));
        
        // Update only the enable_deactivation setting
        settings.enable_deactivation = plugin.enable_deactivation;
        
        // Update settings via store
        await store.updatePluginSettings(plugin.id, settings);
        
        // Update the plugin's local settings
        plugin.settings = settings;
        
        ElMessage.success(`Plugin ${plugin.enable_deactivation ? 'disabled' : 'enabled'} successfully`);
      } catch (error) {
        // Revert the UI change if the API call fails
        plugin.enable_deactivation = plugin.enable_deactivation == 'yes' ? 'no' : 'yes';
        ElMessage.error('Failed to update plugin status');
        console.error('Error toggling plugin loading:', error);
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
        const { plugin, settings } = data
        
        // Ensure enable_deactivation reflects the current state from the plugin list
        settings.enable_deactivation = plugin.enable_deactivation;
        
        // Update the plugin's settings in the store
        await store.updatePluginSettings(plugin.id, settings)
        
        // Important: Update the settings in our local plugin object
        const pluginIndex = plugins.value.findIndex(p => p.id === plugin.id)
        if (pluginIndex !== -1) {
          plugins.value[pluginIndex].settings = { ...settings }
        }
        
        ElMessage.success('Settings saved successfully')
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

    return {
      searchQuery,
      showSettings,
      selectedPlugin,
      loading,
      filteredPlugins,
      togglePluginLoading,
      openSettings,
      savePluginSettings,
      getPluginIconClass,
      Search, Box, Setting, Monitor, Edit, Grid
    }
  }
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

  .loading-indicator {
    padding: 20px 0;
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

      &.plugin-disabled {
        background-color: #f5f5f5;
        opacity: 0.85;
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

        .plugin-switch {
          margin-right: 5px;
        }

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

/* Final CSS for plugin list, switch and modal settings */

/* Plugin list item styling */
.plugin-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #ebeef5;
  transition: all 0.3s ease;
}

.plugin-item:hover {
  background-color: #f8f9fa;
}

/* Disabled plugin style */
.plugin-item.plugin-disabled {
  background-color: #f5f5f5;
  opacity: 0.85;
}

.plugin-item.plugin-disabled .plugin-details h3 {
  color: #909399;
}

.plugin-item.plugin-disabled .settings-button {
  color: #c0c4cc;
  border-color: #dcdfe6;
}

/* Settings button (gear icon) styling */
.settings-button {
  color: #909399;
  border-color: #dcdfe6;
  transition: all 0.2s ease;
}

.settings-button.active-settings {
  color: #409eff !important;
  border-color: #409eff !important;
  background-color: rgba(64, 158, 255, 0.1) !important;
}

.settings-button:hover {
  color: #409eff !important;
  border-color: #409eff !important;
}

/* Switch styling */
.el-switch__core {
  border-color: #dcdfe6;
  background-color: #dcdfe6;
}

.el-switch.is-checked .el-switch__core {
  border-color: #10b981 !important;
  background-color: #10b981 !important;
}

/* Status indicator styling */
.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #909399;
  display: inline-block;
  margin-right: 5px;
}

.status-dot.active {
  background-color: #10b981;
}

.status-text {
  font-size: 12px;
  color: #909399;
}

/* Plugin actions styling */
.plugin-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Modal settings dialog improvements */
.plugin-settings-modal .el-dialog {
  border-radius: 10px;
  overflow: hidden;
}

.plugin-settings-modal .el-dialog__header {
  background-color: #f8f9fa;
  padding: 15px 20px;
}

.plugin-settings-modal .el-dialog__title {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

.plugin-settings-modal .el-dialog__body {
  padding: 20px;
}

.plugin-settings-modal .form-field {
  margin-bottom: 20px;
  transition: all 0.3s ease;
  padding: 8px;
  border-radius: 6px;
}

.plugin-settings-modal .form-field:hover {
  background-color: #f8f9fa;
}

.plugin-settings-modal .form-field label {
  margin-bottom: 10px;
  font-weight: 500;
  color: #303133;
}

.plugin-settings-modal .form-field .el-switch {
  height: 24px;
}

.plugin-settings-modal .form-field .disable-switch {
  margin-right: 10px;
}

/* Modal dialog footer buttons */
.plugin-settings-modal .dialog-footer .el-button--primary {
  background-color: #409eff;
  border-color: #409eff;
  font-weight: 500;
}

.plugin-settings-modal .dialog-footer .el-button--primary:hover {
  background-color: #66b1ff;
  border-color: #66b1ff;
}

/* Dropdown select styling */
.el-select .el-input__inner {
  border-radius: 4px;
}

.el-select .el-input.is-focus .el-input__inner {
  border-color: #409eff;
}

.el-select .el-input__inner:hover {
  border-color: #c0c4cc;
}

/* Multi-select tag styling */
.el-select .el-select__tags {
  padding: 0 6px;
}

.el-select .el-tag {
  background-color: #f0f2f5;
  border-color: #e4e7ed;
  color: #606266;
  margin: 2px 4px 2px 0;
}

.el-select .el-tag .el-tag__close {
  color: #909399;
}

.el-select .el-tag .el-tag__close:hover {
  background-color: #909399;
  color: #fff;
}

/* Custom URI conditions styling */
.uri-condition {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 10px;
  padding: 8px;
  border-radius: 4px;
  background-color: #f9f9f9;
}

.uri-condition:hover {
  background-color: #f0f2f5;
}

.uri-condition .condition-type {
  width: 140px;
  flex-shrink: 0;
}

.uri-condition .condition-value {
  flex-grow: 1;
}

.uri-condition .condition-actions {
  display: flex;
  gap: 5px;
  flex-shrink: 0;
}

.uri-condition .condition-actions .el-button {
  padding: 6px;
  height: 32px;
  width: 32px;
}

/* Add condition button */
.mt-3 {
  margin-top: 12px;
}

/* Full width elements */
.w-full {
  width: 100%;
}

/* Plugin list header styling */
.htpm-plugins-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #ebeef5;
}

.htpm-plugins-header h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

.htpm-plugins-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Search input styling */
.htpm-plugins-actions .el-input {
  width: 220px;
}

.htpm-plugins-actions .el-input__inner {
  border-radius: 4px;
  height: 36px;
}

/* Filter and sort buttons */
.htpm-plugins-actions .el-button {
  height: 36px;
  padding: 0 15px;
}

/* Plugin icon styling */
.plugin-icon-image {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #fff;
  background: #e9ecef;
  transition: all 0.3s ease;
}

.plugin-icon-image.query-monitor {
  background: #4c6ef5;
}

.plugin-icon-image.elementor {
  background: #92003b;
}

.plugin-icon-image.htmega {
  background: #0073aa;
}

.plugin-icon-image.woocommerce {
  background: #7f54b3;
}

.plugin-icon-image.yoast {
  background: #a4286a;
}

/* Plugin details styling */
.plugin-details h3 {
  margin: 0 0 4px;
  font-size: 14px;
  font-weight: 500;
  color: #303133;
  line-height: 1.4;
}

/* Switch extra styling for better visual appearance */
.el-switch.is-checked .el-switch__core .el-switch__action {
  transform: translate3d(18px, 0, 0);
}

.el-switch__action {
  width: 14px;
  height: 14px;
}

.el-switch__core {
  width: 36px !important;
  height: 20px;
  border-radius: 10px;
}

/* Loading indicators */
.el-loading-mask {
  background-color: rgba(255, 255, 255, 0.8);
}

.el-loading-spinner .circular {
  height: 42px;
  width: 42px;
}

.el-loading-spinner .path {
  stroke: #409eff;
}
</style>