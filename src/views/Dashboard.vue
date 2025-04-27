<template>
  <el-row :gutter="30" class="htpm-content">
    <el-col :span="19">
      <!-- Stats Cards -->
      <stats-cards :stats="stats" />

      <!-- Plugin List -->
      <plugin-list 
        :plugins="plugins"
        @toggle-plugin="handleTogglePlugin"
        @open-settings="handleOpenSettings"
      />

      <!-- Settings Modal -->
      <el-dialog
        v-model="settingsDialog.visible"
        :title="`${settingsDialog.plugin?.name || 'Plugin'} Settings`"
        width="500px"
        class="plugin-settings-dialog"
      >
        <el-form :model="settingsDialog.form" label-width="120px">
          <el-form-item label="Disable Plugin">
            <el-switch v-model="settingsDialog.form.disabled" />
          </el-form-item>
          <el-form-item label="Auto Update">
            <el-switch v-model="settingsDialog.form.autoUpdate" />
          </el-form-item>
          <el-form-item label="Email">
            <el-switch v-model="settingsDialog.form.emailNotifications" />
          </el-form-item>
          <el-form-item label="Priority">
            <el-input-number 
              v-model="settingsDialog.form.priority"
              :min="1"
              :max="10"
              controls-position="right"
            />
          </el-form-item>
        </el-form>
        <template #footer>
          <el-button @click="settingsDialog.visible = false">Cancel</el-button>
          <el-button type="primary" @click="saveSettings">Save</el-button>
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

    <el-col :span="5">
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
</template>

<script setup>
import { ref } from 'vue'
import { Star } from '@element-plus/icons-vue'
import StatsCards from '../components/StatsCards.vue'
import PluginList from '../components/PluginList.vue'
import { usePluginStore } from '../store/plugins'

const store = usePluginStore()

const stats = ref({
  totalPlugins: 18,
  activePlugins: 12,
  updateAvailable: 3,
  inactivePlugins: 6
})

const plugins = ref([
  // Add your plugin data here
])

const selectedPlugin = ref(null)
const settingsDialog = ref({
  visible: false,
  plugin: null,
  form: {
    disabled: false,
    autoUpdate: true,
    emailNotifications: false,
    priority: 1
  }
})

const documentationDialog = ref(false)
const changelogDialog = ref({
  visible: false,
  updates: []
})

const rating = ref(0)
const rateColors = ['#F7BA2A', '#F7BA2A', '#F7BA2A']

const handleTogglePlugin = (plugin) => {
  plugin.active = !plugin.active
}

const handleOpenSettings = (plugin) => {
  settingsDialog.value.plugin = plugin
  settingsDialog.value.visible = true
  // Load current plugin settings
  settingsDialog.value.form = {
    disabled: !plugin.active,
    autoUpdate: true,
    emailNotifications: false,
    priority: 1
  }
}

const saveSettings = () => {
  if (settingsDialog.value.plugin) {
    // Update plugin settings
    settingsDialog.value.plugin.active = !settingsDialog.value.form.disabled
    // Close dialog
    settingsDialog.value.visible = false
  }
}

const upgradeToPro = () => {
  // Implement upgrade logic
}

const ratePlugin = () => {
  // Implement rating logic
}
</script>

<style lang="scss" scoped>

.htpm-sidebar {
  .upgrade-card {
    margin-bottom: 20px;

    .upgrade-title {
      font-size: 18px;
      font-weight: 600;
      margin: 0 0 8px;
    }

    .upgrade-description {
      font-size: 14px;
      color: #409EFF;
      margin: 0 0 16px;
    }

    .upgrade-text {
      color: #606266;
      line-height: 1.6;
      margin-bottom: 16px;
    }

    .upgrade-button {
      width: 100%;
    }
  }

  .rating-card {
    .rating-content {
      text-align: center;

      .star-icon {
        font-size: 32px;
        color: #F7BA2A;
        margin-bottom: 16px;
      }

      .rating-title {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 8px;
      }

      .rating-text {
        color: #606266;
        line-height: 1.6;
        margin-bottom: 16px;
      }

      .rating-stars {
        margin-bottom: 16px;
      }

      .rate-button {
        width: 100%;
      }
    }
  }
}
</style>
