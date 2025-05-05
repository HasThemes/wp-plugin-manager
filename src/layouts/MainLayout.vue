<template>
  <div class="htpm-dashboard">
    <!-- Header -->
    <Header />
    <el-row :gutter="30" class="htpm-content">
    <el-col :span="19">
      <slot></slot>
    </el-col>
    <el-col :span="5">
      <!-- Sidebar -->
      <sidebar />
    </el-col>
  </el-row>

    <!-- Changelog Drawer -->
    <el-drawer
      v-model="changelogDialog"
      title="Plugin Updates"
      direction="rtl"
      size="400px"
      custom-class="changelog-drawer" style="top: 32px !important; height: calc(100vh - 32px) !important;"
    >
      <div class="changelog-wrapper">
        <div v-for="update in updates" :key="update.id" class="changelog-item">
          <div class="update-header">
            <h3>{{ update.plugin }}</h3>
            <span class="version">v{{ update.version }}</span>
          </div>
          <div class="changelog-content">
            <p>{{ update.changelog }}</p>
          </div>
        </div>
      </div>
    </el-drawer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Header from '../components/Header.vue'
import Sidebar from '../components/Sidebar.vue'

const changelogDialog = ref(false)

</script>

<style lang="scss">
div#htpm-app {
  width: 100%;
  display: flex;
}  
.htpm-dashboard {
  min-height: calc(100vh - 32px);
  padding: 10px 15px;
  width: 100%;
}
</style>
