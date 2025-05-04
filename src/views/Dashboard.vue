<template>
  <el-row :gutter="30" class="htpm-content">
    <el-col :span="19">
      <!-- Stats Cards -->
      <stats-cards />

      <!-- Plugin List -->
      <plugin-list 
        :plugins="plugins"
        @toggle-plugin="handleTogglePlugin"
        @open-settings="handleOpenSettings"
      />
      <HelpSection />

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
      <sidebar />
    </el-col>
  </el-row>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import StatsCards from '../components/StatsCards.vue'
import PluginList from '../components/PluginList.vue'
import { usePluginStore } from '../store/plugins'
import HelpSection from '../components/HelpSection.vue'
import Sidebar from '../components/Sidebar.vue'

const store = usePluginStore()
// Get plugins from store
const plugins = computed(() => store.plugins)

// Load plugins when component mounts
onMounted(async () => {
  await store.fetchPlugins()
})

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
</script>

