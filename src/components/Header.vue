<template>
      <!-- Header -->
      <el-row align="middle" justify="space-between" class="htpm-header-row">
        <el-col :span="18">
          <el-menu mode="horizontal" class="htpm-nav" :ellipsis="true">
            <el-menu-item index="1">
              <el-icon><Grid /></el-icon>
              General
            </el-menu-item>
            <el-menu-item index="2">
              <el-icon><Setting /></el-icon>
              Settings
            </el-menu-item>
            <el-menu-item index="3">
              <el-icon><Tools /></el-icon>
              Tools
            </el-menu-item>
            <el-menu-item index="4">
              <el-icon><Key /></el-icon>
              License
            </el-menu-item>
            <el-menu-item index="5">
              <el-icon><Document /></el-icon>
              Documentation
            </el-menu-item>
            <el-menu-item index="6">
              <el-icon><Service /></el-icon>
              Support
            </el-menu-item>
          </el-menu>
        </el-col>
        <el-col :span="6" class="htpm-header-actions">
          <el-space>
            <el-button type="primary" @click="upgradeToPro">
              <el-icon><Top /></el-icon>
              Upgrade to Pro
            </el-button>
            <el-badge :value="updateCount" :hidden="!updateCount" class="notification-badge">
              <el-button :icon="Bell" circle @click="showChangelog" />
            </el-badge>
          </el-space>
        </el-col>
      </el-row>
      <!-- Documentation Modal -->
      <documentation v-model="documentationDialog" />
  
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
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import {
    Grid,
    Setting,
    Tools,
    Key,
    Document,
    Service,
    Bell,
    Top
  } from '@element-plus/icons-vue'  
  const updateCount = ref(3)
  const changelogDialog = ref(false)
  
  const showChangelog = () => {
    changelogDialog.value = true
  }
  
  const upgradeToPro = () => {
    // Implement upgrade logic
  }
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
  
  :deep(.changelog-drawer) {
    margin-top: 32px;
  }
  
  .changelog-wrapper {
    padding: 0 20px;
  
    .changelog-item {
      padding: 20px 0;
      border-bottom: 1px solid #e4e7ed;
  
      &:last-child {
        border-bottom: none;
      }
  
      .update-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
  
        h3 {
          margin: 0;
          font-size: 16px;
          font-weight: 600;
          color: #1e293b;
        }
  
        .version {
          font-size: 14px;
          color: var(--el-color-primary);
          font-weight: 500;
        }
      }
  
      .changelog-content {
        p {
          margin: 0;
          font-size: 14px;
          line-height: 1.6;
          color: #64748b;
        }
      }
    }
  }
  
  .htpm-header-row {
    background: #fff;
    border-bottom: 1px solid #e4e7ed;
    margin-bottom: 20px;
    padding: 0 20px;
    position: sticky;
    top: 32px;
    z-index: 100;
    border-radius: 5px;
  
    .htpm-nav {
      border: none;
      padding: 0;
  
      .el-menu-item {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 60px;
        padding: 0 16px;
        font-weight: 500;
  
        .el-icon {
          font-size: 18px;
        }
  
        &.is-active {
          color: var(--el-color-primary);
          border-bottom: 2px solid var(--el-color-primary);
        }
      }
    }
  
    .htpm-header-actions {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      height: 60px;
      padding-right: 16px;
  
      .el-button {
        &.is-circle {
          margin-right: 8px;
        }
      }
    }
  }
  </style>
  