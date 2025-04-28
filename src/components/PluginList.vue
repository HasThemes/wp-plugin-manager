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
      <div v-for="plugin in filteredPlugins" :key="plugin.id" class="plugin-item">
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
import { ref, computed } from 'vue'
import { Search, Filter, Sort, Box, Setting, Monitor, Edit, Grid } from '@element-plus/icons-vue'
import PluginSettingsModal from './PluginSettingsModal.vue'

const searchQuery = ref('')
const showSettings = ref(false)
const selectedPlugin = ref(null)
const props = defineProps({
  plugins: {
    type: Array,
    required: true,
    default: () => []
  }
})

const filteredPlugins = computed(() => {
  if (!searchQuery.value) return props.plugins
  
  return props.plugins.filter(plugin => 
    plugin.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const handleFilter = () => {
  // Implement advanced filter logic
}

const handleSort = () => {
  // Implement sort logic
}

const togglePlugin = async (plugin) => {
  try {
    await emit('toggle', plugin)
  } catch (error) {
    ElMessage.error('Failed to toggle plugin')
  }
}

const openSettings = (plugin) => {
  selectedPlugin.value = plugin
  showSettings.value = true
}

const savePluginSettings = async (data) => {
  try {
    // TODO: Implement API call to save plugin settings
    // await savePluginSettingsAPI(data)
    ElMessage.success('Plugin settings saved successfully')
  } catch (error) {
    ElMessage.error('Failed to save plugin settings')
  }
}

const getPluginIconClass = (name) => {
  if (name.includes('Query Monitor')) return 'query-monitor'
  if (name.includes('Elementor')) return 'elementor'
  if (name.includes('HT Mega')) return 'htmega'
  return 'default'
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

      &:last-child {
        border-bottom: none;
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
          position: static;
          margin: 0;

          &.query-monitor {
            background: #4c6ef5;
          }

          &.elementor {
            background: #92003b;
          }

          &.htmega {
            background: #0073aa;
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
              width: 4px;
              height: 4px;
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
</style>
