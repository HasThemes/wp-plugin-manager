<template>
  <!-- Header -->
  <el-row align="middle" justify="space-between" class="htpm-header-row">
    <el-col :xs="16" :sm="16" :md="18" :lg="18" :xl="18">
      <el-menu 
        mode="horizontal" 
        class="htpm-nav" 
        :ellipsis="true"
        :default-active="activeIndex"
        :router="false"
      >
        <template v-for="(menu, key) in sortedMenuItems" :key="key">
          <el-menu-item 
            v-if="menu.visible" 
            :index="menu.isRouter ? menu.link : key"
            @click="() => handleMenuClick(menu)"
          >
            <el-icon><component :is="icons[menu.icon]" /></el-icon>
            {{ menu.label }}
          </el-menu-item>
        </template>
      </el-menu>
    </el-col>
    <el-col :xs="8" :sm="8" :md="6" :lg="6" :xl="6" class="htpm-header-actions">
        <el-button type="primary" @click="upgradeToPro" v-if="!isPro">
          <el-icon><Top /></el-icon>
          <span class="hidden-sm-and-down">{{ labels_texts?.upgrade_to_pro }} </span>
        </el-button>
        <div class="notification-btn-wrapper">
          <el-button :class="{'has-notification': updateCount}" :icon="Bell" circle @click="showChangelog" />
          <span v-if="updateCount" class="notification-indicator"></span>
        </div>
    </el-col>
  </el-row>
   <!-- Changelog Drawer -->
   <notification-drawer v-model="changelogDialog" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import { Key, Grid, Setting, Document, Service, Promotion, Top, Bell } from '@element-plus/icons-vue'
import { usePluginStore } from '../store/plugins'
import NotificationDrawer from './NotificationDrawer.vue'
const store = usePluginStore()
const changelogDialog = ref(false)

const route = useRoute()
const router = useRouter()
const activeIndex = computed(() => route.path)
const labels_texts = HTPMM.adminSettings.labels_texts
  // Computed property for notification count
  const updateCount = computed(() => store.notificationStatus?.has_unread || false)
// Register Element Plus icons
const icons = {
  Grid,
  Setting,
  Document,
  Service,
  Key,
  Promotion,
  Top,
  Bell
}

// Handle menu clicks
const upgradeToPro = () => {
  window.open(HTPMM?.helpSection?.upgradeLink, '_blank')
}

  // Check notification status on mount
  onMounted(async () => {
    await store.checkNotificationStatus()
  })

  const showChangelog = () => {
    // Just open drawer immediately
    changelogDialog.value = true
  }

const handleMenuClick = (menu) => {
  if (menu.isRouter) {
    // Use router.push for internal routes
    router.push(menu.link)
    return
  }
  
  // For external links
  if (menu.link) {
    if (menu.target === '_blank') {
      window.open(menu.link, '_blank')
    } else {
      window.location.href = menu.link
    }
  }
}
const menuSettings = HTPMM.adminSettings.menu_settings
const isPro = HTPMM.adminSettings.is_pro

// Sort menu items by order
const sortedMenuItems = computed(() => {
  return Object.entries(menuSettings)
    .sort(([,a], [,b]) => a.order - b.order)
    .reduce((acc, [key, value]) => ({ ...acc, [key]: value }), {})
})

// Handle menu actions
const handleMenuAction = (action) => {
  if (typeof window[action] === 'function') {
    window[action]()
  }
}

const openProModal = () => {
  if (typeof window.openProModal === 'function') {
    window.openProModal()
  }
}
</script>

<style lang="scss">
.htpm-header-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;

  .notification-btn-wrapper {
    position: relative;
    display: inline-block;

    .el-button {
      background-color: transparent;
      border: none;
      padding: 8px;
      height: auto;

      &:hover {
        background-color: #f5f7fa;
      }

      &.has-notification {
        color: var(--el-color-primary);
      }
    }

    .notification-indicator {
      position: absolute;
      top: 2px;
      right: 2px;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: var(--el-color-danger);
    }
  }
}

</style>

<style lang="scss" scoped>
.htpm-header-row {
  padding: 0 20px;
  background: #fff;
  border-bottom: 1px solid #e4e7ed;
  
  .htpm-nav {
    border: none;
    
    :deep(.el-menu-item) {
      height: 50px;
      line-height: 50px;
      
      &.is-active {
        font-weight: 600;
      }
      
      .el-icon {
        margin-right: 4px;
      }
    }
  }
  
  .text-right {
    text-align: right;
  }
}
</style>

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
          .hidden-sm-and-down {
            display: none;
          }
        }

      }
    }
    @media screen and (max-width: 1024px) {
      position: relative !important;
      top: 0 !important;
    }
  }
  </style>
  