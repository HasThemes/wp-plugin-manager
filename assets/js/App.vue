<template>
  <div class="htpm-dashboard">
    <!-- Header -->
    <div class="htpm-header">
      <el-menu mode="horizontal" class="htpm-nav">
        <el-menu-item index="1">General</el-menu-item>
        <el-menu-item index="2">Settings</el-menu-item>
        <el-menu-item index="3">Tools</el-menu-item>
        <el-menu-item index="4">License</el-menu-item>
        <el-menu-item index="5">Documentation</el-menu-item>
        <el-menu-item index="6">Support</el-menu-item>
      </el-menu>
      
      <div class="htpm-header-actions">
        <el-badge :value="updateCount" :hidden="!updateCount" class="notification-badge">
          <el-button :icon="Bell" circle @click="showChangelog" />
        </el-badge>
        <el-button type="primary" @click="upgradeToPro">Upgrade to Pro</el-button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="htpm-stats">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-card>
            <template #header>
              <div class="card-header">
                <el-icon><Collection /></el-icon>
                <span>{{ stats.totalPlugins }}</span>
              </div>
            </template>
            <div class="card-content">Total Plugins</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card>
            <template #header>
              <div class="card-header">
                <el-icon><Select /></el-icon>
                <span>{{ stats.activePlugins }}</span>
              </div>
            </template>
            <div class="card-content">Active Plugins</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card>
            <template #header>
              <div class="card-header">
                <el-icon><RefreshRight /></el-icon>
                <span>{{ stats.updateAvailable }}</span>
              </div>
            </template>
            <div class="card-content">Update Available</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card>
            <template #header>
              <div class="card-header">
                <el-icon><Close /></el-icon>
                <span>{{ stats.inactivePlugins }}</span>
              </div>
            </template>
            <div class="card-content">Inactive Plugins</div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- Plugin List -->
    <div class="htpm-plugins">
      <div class="htpm-plugins-header">
        <h2>Manage Plugins</h2>
        <div class="htpm-plugins-actions">
          <el-input
            v-model="search"
            placeholder="Search plugins..."
            :prefix-icon="Search"
          />
          <el-button :icon="Filter">Filter</el-button>
          <el-button :icon="Sort">Sort</el-button>
        </div>
      </div>

      <el-table :data="filteredPlugins" style="width: 100%">
        <el-table-column prop="icon" width="50">
          <template #default="{ row }">
            <el-avatar :src="row.icon" :size="32" />
          </template>
        </el-table-column>
        <el-table-column prop="name" label="Plugin Name">
          <template #default="{ row }">
            <div class="plugin-info">
              <span class="plugin-name">{{ row.name }}</span>
              <el-tag size="small" :type="row.active ? 'success' : 'info'">
                {{ row.active ? 'Active' : 'Inactive' }}
              </el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column width="120" align="right">
          <template #default="{ row }">
            <el-switch
              v-model="row.active"
              @change="togglePlugin(row)"
            />
            <el-button
              :icon="Setting"
              circle
              @click="openSettings(row)"
            />
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- Settings Modal -->
    <el-dialog
      v-model="settingsDialog.visible"
      :title="settingsDialog.plugin?.name + ' Settings'"
      width="50%"
    >
      <el-form :model="settingsDialog.form" label-width="120px">
        <el-form-item label="Disable Plugin">
          <el-switch v-model="settingsDialog.form.disabled" />
        </el-form-item>
        <el-form-item label="Device Type">
          <el-select v-model="settingsDialog.form.deviceType" placeholder="Select devices">
            <el-option label="Desktop + Tablet" value="desktop_tablet" />
            <el-option label="Mobile Only" value="mobile" />
            <el-option label="All Devices" value="all" />
          </el-select>
        </el-form-item>
        <el-form-item label="Action">
          <el-select v-model="settingsDialog.form.action">
            <el-option label="Enable on Selected Pages" value="enable" />
            <el-option label="Disable on Selected Pages" value="disable" />
          </el-select>
        </el-form-item>
        <el-form-item label="Page Type">
          <el-select v-model="settingsDialog.form.pageType">
            <el-option label="Custom" value="custom" />
            <el-option label="All Pages" value="all" />
            <el-option label="Front Page" value="front" />
          </el-select>
        </el-form-item>
        <el-form-item label="URI Condition">
          <el-input v-model="settingsDialog.form.uriCondition" placeholder="e.g. /contact-us" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="settingsDialog.visible = false">Cancel</el-button>
          <el-button type="primary" @click="savePluginSettings">Save</el-button>
        </span>
      </template>
    </el-dialog>

    <!-- Changelog Modal -->
    <el-dialog
      v-model="changelogDialog.visible"
      title="Plugin Updates"
      width="40%"
    >
      <div v-for="update in changelogDialog.updates" :key="update.plugin">
        <h3>{{ update.plugin }}</h3>
        <p>{{ update.changelog }}</p>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Bell,
  Setting,
  Search,
  Filter,
  Sort,
  Collection,
  Select,
  RefreshRight,
  Close
} from '@element-plus/icons-vue'
import { usePluginStore } from './store/plugins'

const store = usePluginStore()
const search = ref('')
const stats = ref({
  totalPlugins: 18,
  activePlugins: 12,
  updateAvailable: 3,
  inactivePlugins: 6
})

const updateCount = ref(3)

const settingsDialog = ref({
  visible: false,
  plugin: null,
  form: {
    disabled: false,
    deviceType: 'desktop_tablet',
    action: 'enable',
    pageType: 'custom',
    uriCondition: ''
  }
})

const changelogDialog = ref({
  visible: false,
  updates: [
    {
      plugin: 'Query Monitor',
      changelog: 'Added support for PHP 8.2 and improved performance monitoring'
    },
    {
      plugin: 'Elementor',
      changelog: 'New animation effects and bug fixes'
    }
  ]
})

const filteredPlugins = computed(() => {
  return store.plugins.filter(plugin => 
    plugin.name.toLowerCase().includes(search.value.toLowerCase())
  )
})

const openSettings = (plugin) => {
  settingsDialog.value.plugin = plugin
  settingsDialog.value.visible = true
}

const savePluginSettings = async () => {
  await store.updatePluginSettings({
    plugin: settingsDialog.value.plugin,
    settings: settingsDialog.value.form
  })
  settingsDialog.value.visible = false
}

const togglePlugin = async (plugin) => {
  await store.togglePlugin(plugin)
}

const showChangelog = () => {
  changelogDialog.value.visible = true
}

const upgradeToPro = () => {
  window.open('https://hasthemes.com/plugins/wp-plugin-manager-pro/', '_blank')
}
</script>

<style lang="scss">
.htpm-dashboard {
  padding: 20px;
  background: #f5f7fa;
  min-height: calc(100vh - 32px);

  .htpm-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);

    .htpm-nav {
      border: none;
    }

    .htpm-header-actions {
      padding: 0 16px;
      display: flex;
      gap: 12px;
      align-items: center;
    }
  }

  .htpm-stats {
    margin-bottom: 24px;

    .el-card {
      border-radius: 8px;
      
      .card-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 24px;
        font-weight: 600;
      }

      .card-content {
        color: #606266;
        font-size: 14px;
      }
    }
  }

  .htpm-plugins {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);

    .htpm-plugins-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;

      h2 {
        margin: 0;
        font-size: 18px;
      }

      .htpm-plugins-actions {
        display: flex;
        gap: 12px;
        align-items: center;

        .el-input {
          width: 200px;
        }
      }
    }

    .plugin-info {
      display: flex;
      align-items: center;
      gap: 8px;

      .plugin-name {
        font-weight: 500;
      }
    }
  }
}

.el-dialog {
  border-radius: 8px;
  
  .el-dialog__header {
    margin: 0;
    padding: 20px;
    border-bottom: 1px solid #dcdfe6;
  }

  .el-dialog__body {
    padding: 20px;
  }

  .el-dialog__footer {
    padding: 20px;
    border-top: 1px solid #dcdfe6;
  }
}
</style>
