<template>
  <el-row :gutter="30" class="htpm-content">
    <el-col :span="19">
      <!-- Stats Cards -->
      <stats-cards />
      <!-- Plugin List -->
      <plugin-list/>
      <HelpSection />

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
import { ref} from 'vue'
import StatsCards from '../components/StatsCards.vue'
import PluginList from '../components/PluginList.vue'
import HelpSection from '../components/HelpSection.vue'
import Sidebar from '../components/Sidebar.vue'
const changelogDialog = ref({
  visible: false,
  updates: []
})
</script>

