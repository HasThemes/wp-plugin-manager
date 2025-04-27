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

    <el-row :gutter="20" class="htpm-content">
      <el-col :span="16">
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
                    <el-icon><Timer /></el-icon>
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
                    <el-icon><Remove /></el-icon>
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
                v-model="searchQuery"
                placeholder="Search plugins..."
                :prefix-icon="Search"
              />
              <el-button @click="" :icon="Filter">Filter</el-button>
              <el-button @click="" :icon="Sort">Sort</el-button>
            </div>
          </div>

          <el-table :data="plugins" style="width: 100%">
            <el-table-column prop="name" label="Plugin Name">
              <template #default="{ row }">
                <div class="plugin-info">
                  <el-icon><Extension /></el-icon>
                  <span class="plugin-name">{{ row.name }}</span>
                  <el-tag size="small" type="success" v-if="row.active">Active</el-tag>
                </div>
              </template>
            </el-table-column>
            <el-table-column prop="version" label="Version" width="120" />
            <el-table-column prop="author" label="Author" width="180" />
            <el-table-column label="Actions" width="200" align="right">
              <template #default="{ row }">
                <el-button-group>
                  <el-button
                    :type="row.active ? 'danger' : 'success'"
                    size="small"
                    @click="togglePlugin(row)"
                  >
                    {{ row.active ? 'Deactivate' : 'Activate' }}
                  </el-button>
                  <el-button
                    type="primary"
                    size="small"
                    @click="openSettings(row)"
                  >
                    Settings
                  </el-button>
                </el-button-group>
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
            <el-form-item label="Auto Update">
              <el-switch v-model="settingsDialog.form.autoUpdate" />
            </el-form-item>
            <el-form-item label="Email Notifications">
              <el-switch v-model="settingsDialog.form.emailNotifications" />
            </el-form-item>
            <el-form-item label="Priority">
              <el-input-number v-model="settingsDialog.form.priority" :min="1" :max="100" />
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
          <div v-for="update in changelogDialog.updates" :key="update.id">
            <h3>{{ update.plugin }} - v{{ update.version }}</h3>
            <div class="changelog-content">
              <p>{{ update.changelog }}</p>
            </div>
          </div>
        </el-dialog>
      </el-col>

      <el-col :span="8">
        <!-- Sidebar -->
        <div class="htpm-sidebar">
          <!-- Upgrade Banner -->
          <el-card class="upgrade-card">
            <h2 class="upgrade-title">Upgrade to WP Plugin Manager Pro</h2>
            <p class="upgrade-description">Get More Features with Pro</p>
            <p class="upgrade-text">Unlock advanced plugin management, automatic updates, security scanning, and performance optimization with our Pro version.</p>
            <el-button type="primary" class="upgrade-button" @click="upgradeToPro">Upgrade Now</el-button>
          </el-card>

          <!-- Rating Section -->
          <el-card class="rating-card">
            <div class="rating-content">
              <el-icon class="star-icon"><Star /></el-icon>
              <h3 class="rating-title">Enjoying Plugin Manager?</h3>
              <p class="rating-text">Your feedback helps us improve. Please consider leaving a review!</p>
              <div class="rating-stars">
                <el-rate v-model="rating" :colors="rateColors" />
              </div>
              <el-button type="warning" class="rate-button" @click="ratePlugin">Rate Plugin</el-button>
            </div>
          </el-card>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Star } from '@element-plus/icons-vue'
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

const rating = ref(0)
const rateColors = ['#99A9BF', '#F7BA2A', '#FF9900']

const ratePlugin = () => {
  window.open('https://wordpress.org/plugins/wp-plugin-manager/#reviews', '_blank')
}
</script>

<style lang="scss">
.htpm-dashboard {
  background: #f5f7fa;
  min-height: calc(100vh - 32px);
  
  .htpm-header {
    background: #fff;
    border-bottom: 1px solid #e4e7ed;
    margin-bottom: 20px;
  }

  .htpm-content {
    padding: 0 20px;
  }

  .htpm-stats,
  .htpm-plugins {
    margin-bottom: 20px;
  }

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
  .htpm-sidebar {
    float: right;
    width: 300px;
    padding: 20px;
    
    .upgrade-card,
    .rating-card {
      margin-bottom: 20px;
    }

    .upgrade-title {
      font-size: 18px;
      font-weight: 600;
      margin: 0 0 10px;
      color: #333;
      text-align: center;
    }

    .upgrade-description {
      font-size: 16px;
      color: #666;
      margin: 10px 0;
      text-align: center;
    }

    .upgrade-text {
      font-size: 14px;
      color: #666;
      margin: 15px 0;
      line-height: 1.5;
    }

    .upgrade-button {
      width: 100%;
      margin-top: 15px;
    }

    .rating-content {
      text-align: center;
    }

    .star-icon {
      font-size: 32px;
      color: #F7BA2A;
      margin-bottom: 10px;
    }

    .rating-title {
      font-size: 16px;
      font-weight: 600;
      margin: 10px 0;
      color: #333;
    }

    .rating-text {
      font-size: 14px;
      color: #666;
      margin: 10px 0;
      line-height: 1.5;
    }

    .rating-stars {
      margin: 15px 0;
    }

    .rate-button {
      width: 100%;
      margin-top: 10px;
    }
  }
}
</style>
