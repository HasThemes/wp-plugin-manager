<template>
      <!-- Header -->
      <el-row align="middle" justify="space-between" class="htpm-header-row">
        <el-col :xs="16" :sm="16" :md="18" :lg="18" :xl="18">
          <el-menu 
            mode="horizontal" 
            class="htpm-nav" 
            :ellipsis="true"
            :default-active="activeIndex"
            router
          >
            <!-- <el-menu-item index="/">
              <el-icon><HomeFilled /></el-icon>
              Dashboard
            </el-menu-item> -->
            <el-menu-item index="/">
              <el-icon><Grid /></el-icon>
              General
            </el-menu-item>
            <el-menu-item index="/settings">
              <el-icon><Setting /></el-icon>
              Settings
            </el-menu-item>
            <el-menu-item index="/tools">
              <el-icon><Tools /></el-icon>
              Tools
            </el-menu-item>
            <el-menu-item index="/license">
              <el-icon><Key /></el-icon>
              License
            </el-menu-item>
            <el-menu-item index="https://hasthemes.com/docs" @click="openDocs">
              <el-icon><Document /></el-icon>
              Documentation
            </el-menu-item>
            <el-menu-item index="https://hasthemes.com/contact" @click="openSupport">
              <el-icon><Service /></el-icon>
              Support
            </el-menu-item>
          </el-menu>
        </el-col>
        <el-col :xs="8" :sm="8" :md="6" :lg="6" :xl="6" class="htpm-header-actions">
            <el-button type="primary" @click="upgradeToPro">
              <el-icon><Top /></el-icon>
              Upgrade to Pro
            </el-button>
            <div class="notification-btn-wrapper">
              <el-button :class="{'has-notification': updateCount}" :icon="Bell" circle @click="showChangelog" />
              <span v-if="updateCount" class="notification-indicator"></span>
            </div>
        </el-col>
      </el-row>
      <!-- Documentation Modal -->
      <documentation v-model="documentationDialog" />
  
      <!-- Changelog Drawer -->
      <notification-drawer v-model="changelogDialog" />
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue'
  import { useRoute } from 'vue-router'
  import { usePluginStore } from '../store/plugins'
  import NotificationDrawer from './NotificationDrawer.vue'
  import {
    Grid,
    Setting,
    Tools,
    Key,
    Document,
    Service,
    Bell,
    Top,
    HomeFilled
  } from '@element-plus/icons-vue'

  const route = useRoute()
  const activeIndex = computed(() => route.path)
  const store = usePluginStore()
  const changelogDialog = ref(false)

  // Computed property for notification count
  const updateCount = computed(() => store.notificationStatus?.has_unread || false)

  // Check notification status on mount
  onMounted(async () => {
    await store.checkNotificationStatus()
  })

  const showChangelog = () => {
    // Open drawer immediately
    changelogDialog.value = true
    // Check status in background
    store.checkNotificationStatus()
  }

  const upgradeToPro = () => {
    // Implement upgrade logic
    window.open('https://hasthemes.com/plugins/wp-plugin-manager-pro/', '_blank')
  }

  const openDocs = () => {
    window.open('https://hasthemes.com/docs', '_blank')
  }

  const openSupport = () => {
    window.open('https://hasthemes.com/contact', '_blank')
  }
  </script>
  
  <style lang="scss">
  .notification-btn-wrapper {
    position: relative;
    display: inline-block;

    .notification-indicator {
      position: absolute;
      top: -2px;
      right: -2px;
      width: 10px;
      height: 10px;
      background: #f56c6c;
      border-radius: 50%;
      border: 2px solid #fff;
      animation: pulse 2s infinite;
    }

    .has-notification {
      color: #409eff;
    }
  }

  @keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(245, 108, 108, 0.4);
    }
    70% {
      box-shadow: 0 0 0 6px rgba(245, 108, 108, 0);
    }
    100% {
      box-shadow: 0 0 0 0 rgba(245, 108, 108, 0);
    }
  }

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
      gap: 15px;

      @media screen and (max-width: 768px) {
        gap: 8px;
        .el-button {
          padding: 8px 12px;
          font-size: 12px;
          .el-icon {
            font-size: 14px;
          }
        }
      }
    }
  }
  </style>
  