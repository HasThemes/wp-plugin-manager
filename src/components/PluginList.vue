
<template>
  <div class="htpm-plugins">
    <div class="htpm-plugins-header">
      <h3>Manage Plugins</h3>
      <div class="htpm-plugins-actions">
        <el-input
          v-model="searchQuery"
          placeholder="Search plugins..."
          :prefix-icon="Search"
        />
      </div>
    </div>
    
    <!-- Loading indicator shown until plugins are fully loaded -->
    <!-- <div v-if="loading" class="loading-indicator">
      <el-skeleton :rows="5" animated />
    </div> -->
    <PluginListSkeleton v-if="loading" />
    <!-- Only show plugin list when loading is complete -->
    <div v-else class="plugin-list">
      <!-- Show only WordPress-activated plugins -->
      <div 
        v-for="plugin in filteredPlugins" 
        :key="plugin.id" 
        class="plugin-item"
        :class="{ 'plugin-disabled': (plugin.enable_deactivation !== 'yes') }"
      >
        <div class="plugin-info">
          <div class="plugin-icon-image default">
            <img v-if="plugin.icon" :src="plugin.icon" :alt="plugin.name + ' icon'" class="plugin-thumbnail">
            <template v-else>
                {{plugin.name.charAt(0).toUpperCase()}}
            </template>
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
          <el-popconfirm
            :title="'This plugin was optimized with specific settings. Review them before enabling to avoid potential issues'"
            confirm-button-text="Enable Anyway"
            cancel-button-text="Review Settings"
            confirm-button-type="primary"
            cancel-button-type="default"
            placement="top"
            :icon="QuestionFilled"
            @confirm="handleOptimizeNow(plugin)"
            @cancel="openSettings(plugin)"
            :visible="showPopconfirm === plugin.id"
            @hide="showPopconfirm = null"
            width="300"
            padding="8px 12px 12px"
            popper-class="plugin-optimize-popconfirm"
          >
            <template #reference>
              <el-switch
                :model-value="plugin.enable_deactivation == 'yes'"
                @click="handleToggle(plugin)"
                class="plugin-switch"
              />
            </template>
          </el-popconfirm>
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

    <!-- Pagination -->
    <div class="pagination-container" v-if="!loading && pagination.total > pagination.pageSize">
      <el-pagination
        layout="total, prev, pager, next"
        :total="pagination.total"
        :page-size="pagination.pageSize"
        :current-page="pagination.currentPage"
        @current-change="handlePageChange"
      />
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
import { ref, computed, watch, reactive } from 'vue'
import { Search, Setting, QuestionFilled } from '@element-plus/icons-vue'
import { ElNotification } from 'element-plus'
import PluginSettingsModal from './PluginSettingsModal.vue'
import { usePluginStore } from '../store/plugins'
import PluginListSkeleton from '../skeleton/PluginListSkeleton.vue'
import { debounce } from 'lodash-es'

const store = usePluginStore()
const searchQuery = ref('')
const debouncedSearchQuery = ref('')
const showSettings = ref(false)
const selectedPlugin = ref(null)
const loading = ref(true)
const plugins = ref([])
const pagination = reactive({
  currentPage: 1,
  pageSize: parseInt(store.allSettings?.itemsPerPage || 10),
  total: 0
})
const showPopconfirm = ref(null) // Track which plugin's popconfirm is shown

// Create debounced search handler
const updateDebouncedSearch = debounce((value) => {
  debouncedSearchQuery.value = value
}, 300)

// Watch for search query changes
watch(searchQuery, (newValue) => {
  updateDebouncedSearch(newValue)
})

// Watch for plugins and update local list
watch(() => store.plugins, async (newPlugins) => {
      if (!newPlugins || newPlugins.length === 0) return
      
      loading.value = true
      try {
        // Fetch settings for all active plugins at once
        await store.fetchAllPluginSettings()
        
        // Prepare the plugins list
        plugins.value = newPlugins.map(plugin => {
          const settings = store.settings[plugin.id] || null;
          
          return {
            ...plugin,
            settings: settings,
            // A plugin is enabled only if settings explicitly set enable_deactivation to 'no'
            enable_deactivation: (!settings || settings.enable_deactivation !== 'yes')? 'no' : 'yes'
          };
        })
      } catch (error) {
        ElNotification({
          title: "Error",
          message: 'Failed to load plugin settings',
          type: 'error',
          position: 'top-right',
          duration: 3000
        });
        console.error('Error loading plugin settings:', error)
      } finally {
        loading.value = false
      }
    }, { immediate: true })

    // Filter plugins based on search query
    const filteredPlugins = computed(() => {
      // First filter only active WordPress plugins
      const activePlugins = plugins.value.filter(plugin => plugin.wpActive)
      
      // Then apply search filter if there's a query
      const searchFilteredPlugins = !debouncedSearchQuery.value 
        ? activePlugins 
        : activePlugins.filter(plugin => 
            plugin.name.toLowerCase().includes(debouncedSearchQuery.value.toLowerCase())
          )
      
      // Update total for pagination
      pagination.total = searchFilteredPlugins.length
      
      // Apply pagination
      const startIndex = (pagination.currentPage - 1) * pagination.pageSize
      return searchFilteredPlugins.slice(startIndex, startIndex + pagination.pageSize)
    })

    // Handle the toggle click
    const handleToggle = (plugin) => {
      const newState = plugin.enable_deactivation == 'yes' ? 'no' : 'yes';
      if (newState === 'yes') {
        // Show popconfirm for enabling
        showPopconfirm.value = plugin.id;
      } else {
        // Direct disable without confirmation
        togglePluginLoading(plugin);
      }
    };

    // Handle the optimize now action
    const handleOptimizeNow = async (plugin) => {
      try {
        // Set both the plugin state and settings to enabled
        plugin.enable_deactivation = 'yes';
        const existingSettings = store.settings[plugin.id] || {
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
        
        existingSettings.enable_deactivation = 'yes';
        await store.updatePluginSettings(plugin.id, existingSettings);
        ElNotification({
          title: "Success",
          message: 'Plugin optimized successfully',
          type: 'success',
          position: 'top-right',
          duration: 3000
        });
      } catch (error) {
        plugin.enable_deactivation = 'no';
        ElNotification({
          title: "Error",
          message: 'Failed to optimize plugin',
          type: 'error',
          position: 'top-right',
          duration: 3000
        });
        console.error('Error optimizing plugin:', error);
      }
    };

    // Toggle plugin loading status (now only handles disable)
    const togglePluginLoading = async (plugin) => {
      try {
        // Only handle disabling here
        plugin.enable_deactivation = 'no';
        
        // Get existing settings
        let existingSettings = store.settings[plugin.id];
        
        // If no settings exist, create default ones
        if (!existingSettings || Object.keys(existingSettings).length === 0) {
          existingSettings = {
            enable_deactivation: 'no',
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
        } else {
          existingSettings.enable_deactivation = 'no';
        }
        
        // Update settings via store
        await store.updatePluginSettings(plugin.id, existingSettings);
        
        // Update the plugin's local settings
        plugin.settings = existingSettings;
        
        ElNotification({
          title: "Success",
          message: 'Plugin optimization disabled successfully',
          type: 'success',
          position: 'top-right',
          duration: 3000
        });
      } catch (error) {
        // Revert the UI change if the API call fails
        plugin.enable_deactivation = 'yes';
        ElNotification({
          title: "Error",
          message: 'Failed to update plugin status',
          type: 'error',
          position: 'top-right',
          duration: 3000
        });
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
        
        // Always enable the plugin when saving settings from modal
        settings.enable_deactivation = 'yes';
        plugin.enable_deactivation = 'yes';
        
        // Important: Update the settings in our local plugin object
        const pluginIndex = plugins.value.findIndex(p => p.id === plugin.id)
        if (pluginIndex !== -1) {
          plugins.value[pluginIndex].settings = { ...settings }
        }
        
        ElNotification({
          title: "Success",
          message: 'Settings saved and plugin optimized successfully',
          type: 'success',
          position: 'top-right',
          duration: 3000
        });
        showSettings.value = false; // Close the modal after saving
      } catch (error) {
        ElNotification({
          title: "Error",
          message: 'Failed to save plugin settings',
          type: 'error',
          position: 'top-right',
          duration: 3000
        });
        console.error('Error saving plugin settings:', error)
      }
    }

    // Handle page change for pagination
    const handlePageChange = (page) => {
      pagination.currentPage = page
    }

</script>

<style lang="scss" scoped>
.htpm-plugins {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  margin-top: 20px;

  .htpm-plugins-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;

    h3 {
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
      border-top: 1px solid #ebeef5;
      transition: background-color 0.3s;

      &:hover {
        background-color: #f8f9fa;
      }
      .plugin-info {
        display: flex;
        align-items: center;
        gap: 12px;

        .plugin-icon-image {
          width: 40px;
          height: 40px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 8px;
          background: var(--el-color-primary-light-9);
          margin-right: 16px;
          overflow: hidden;

          &.default {
            background: #f0f2f5;
            .el-icon {
              font-size: 24px;
              color: #909399;
            }
          }

          .plugin-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

  .pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    
    .el-pagination {
      display: flex;
      align-items: center;
      gap: 10px;
      
      .el-pager {
        display: flex;
        align-items: center;
        gap: 5px;
        
        li {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 32px;
          height: 32px;
          border-radius: 4px;
          cursor: pointer;
          transition: all 0.2s ease;
          
          &.is-active {
            background-color: var(--el-color-primary);
            color: white;
          }
          
          &:hover:not(.is-active) {
            background-color: #f0f2f5;
          }
        }
      }
    }
  }
}

.plugin-optimize-popconfirm {
  .el-popper.is-light {
    min-width: 320px !important;
    padding: 20px !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08) !important;
    border: 1px solid #e5e7eb !important;
  }

  .el-popconfirm__main {
    padding: 0 !important;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 1.6;
    color: #374151;
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }

  .el-popconfirm__icon {
    color: #f59e0b;
    font-size: 20px;
    margin-right: 0;
  }

  .el-popconfirm__action {
    margin-top: 0;
    padding-top: 20px;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    border-top: 1px solid #e5e7eb;

    .el-button {
      margin-left: 0 !important;
      padding: 8px 20px !important;
      height: 38px;
      font-size: 14px;
      font-weight: 500;
      border-radius: 6px;
      min-width: 120px;
      transition: all 0.2s ease;

      &--info {
        background: #f3f4f6;
        border-color: #e5e7eb;
        color: #4b5563;

        &:hover {
          background: #e5e7eb;
          border-color: #d1d5db;
          color: #374151;
        }

        &:active {
          background: #d1d5db;
        }
      }

      &--primary {
        background-color: #409eff;
      }
    }
  }
}

:deep(.plugin-optimize-popconfirm) {
  .el-popconfirm__main {
    margin: 12px 0;
  }
  
  .el-popconfirm__title {
    font-size: 15px;
    font-weight: 500;
    margin-bottom: 8px;
  }
  
  .el-popconfirm__description {
    color: #666;
    font-size: 13px;
    margin-bottom: 12px;
  }

  .el-button {
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
  }

  .el-button--primary {
    background-color: #409eff;
  }
}

.plugin-list {
  .plugin-switch {
    &.el-switch {
      --el-switch-on-color: #409eff;
      
      .el-switch__core {
        border: 2px solid #e5e7eb;
        height: 24px;
        padding: 2px;
        
        .el-switch__action {
          width: 18px;
          height: 18px;
          background: white;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .el-switch__inner {
          transition: all 0.3s ease;
          
          .is-icon, .is-text {
            color: #fff;
            font-size: 12px;
          }
        }
      }

      &:not(.is-checked) {
        .el-switch__core {
          background: white;
        }
      }

      &:hover {
        .el-switch__core {
          border-color: #d1d5db;
        }
      }

      &.is-checked {
        .el-switch__core {
          border-color: #10b981;
          
          .el-switch__action {
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
          }
        }

        &:hover {
          .el-switch__core {
            border-color: #059669;
            background: #059669;
          }
        }
      }
    }
  }
}
</style>