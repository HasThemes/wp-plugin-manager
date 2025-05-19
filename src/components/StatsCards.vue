<template>
  <div class="stats-cards">
    <div class="stats-row">
      <template v-for="(stat, index) in statsList" :key="index">
        <template v-if="!isLoading">
          <div :class="['stat-card', stat.type]" @click="handleStatClick(stat.key)">
            <div class="icon-box">
              <el-icon><component :is="stat.icon" /></el-icon>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ stats[stat.key] }}</div>
              <div class="stat-label">{{ stat.label }}</div>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="stat-card">
            <div class="skeleton-content">
              <div class="skeleton-icon">
                <el-skeleton animated>
                  <template #template>
                    <el-skeleton-item variant="circle" style="width: 48px; height: 48px"/>
                  </template>
                </el-skeleton>
              </div>
              <div class="skeleton-text">
                <el-skeleton animated>
                  <template #template>
                    <el-skeleton-item variant="h3" style="width: 60px; height: 24px; margin-bottom: 8px"/>
                    <el-skeleton-item variant="text" style="width: 80px; height: 14px"/>
                  </template>
                </el-skeleton>
              </div>
            </div>
          </div>
        </template>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { usePluginStore } from '../store/plugins'
import { List, CircleCheck, Refresh, CircleClose, Setting } from '@element-plus/icons-vue'

const store = usePluginStore()
const isLoading = ref(true)  // Local loading state

// Compute stats from store getters
const stats = computed(() => ({
  totalPlugins: store.totalPlugins,
  activePlugins: store.wpActivePlugins.length,
  optimizedPlugins: store.disabledPlugins.length, 
  updateAvailable: store.updateAvailable.length,
  inactivePlugins: store.inactivePlugins.length
}))

const handleStatClick = (statKey) => {
  if(statKey === 'updateAvailable') {
    window.open(`${store.adminURL}plugins.php?plugin_status=upgrade`, '_blank');
    //window.location.href = `${store.adminURL}plugins.php?plugin_status=upgrade`
    return
  }
  if(statKey === 'inactivePlugins') {
    window.open(`${store.adminURL}plugins.php?plugin_status=inactive`, '_blank');
    return
  }
  if(statKey === 'activePlugins') {
    window.open(`${store.adminURL}plugins.php?plugin_status=active`, '_blank');
   // window.location.href = `${store.adminURL}plugins.php?plugin_status=active`
    return
  } else {
    window.open(`${store.adminURL}plugins.php`, '_blank');
    //window.location.href = `${store.adminURL}plugins.php`
    return
  }

}

// Watch for changes in plugins store and update loading state
watch(() => store.plugins, (newPlugins) => {
  isLoading.value = !newPlugins || newPlugins.length === 0
}, { immediate: true })

const statsList = [
  { type: 'total', icon: List, key: 'totalPlugins', label: 'Total Plugins' },
  { type: 'active', icon: CircleCheck, key: 'activePlugins', label: 'Active Plugins' },
  // { type: 'optimized', icon: Setting, key: 'optimizedPlugins', label: 'Optimized Plugins' }, // TODO: Add optimized plugins
  { type: 'updates', icon: Refresh, key: 'updateAvailable', label: 'Update Available' },
  { type: 'inactive', icon: CircleClose, key: 'inactivePlugins', label: 'Inactive Plugins' }
]
</script>

<style scoped>
.stats-cards {
  margin-bottom: 20px;
}

.stats-row {
  display: flex;
  flex-direction: row;
  gap: 20px;
  width: 100%;
  flex-wrap: wrap;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  min-height: 55px;
  flex: 1;
  min-width: 150px;
  cursor: pointer;
}

.skeleton-content {
  display: flex;
  align-items: center;
  gap: 15px;
  width: 100%;
}

.skeleton-icon {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
}

.skeleton-text {
  flex-grow: 1;
}

.icon-box {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.skeleton-text .el-skeleton {
    display: flex;
    flex-direction: column;
}
.stat-content {
  flex-grow: 1;
  min-width: 0;
}
.icon-box .el-icon {
  font-size: 24px;
}

.stat-content {
  flex-grow: 1;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  line-height: 1;
  margin-bottom: 8px;
}

.stat-label {
  color: #7c7c7c;
  font-size: 14px;
  font-weight: 500;
}

.stat-card.total .icon-box {
  background: rgba(64, 158, 255, 0.1);
  color: #409EFF;
}

.stat-card.active .icon-box {
  background: rgba(103, 194, 58, 0.1);
  color: #67C23A;
}

.stat-card.updates .icon-box {
  background: rgba(230, 162, 60, 0.1);
  color: #E6A23C;
}

.stat-card.inactive .icon-box {
  background: rgba(245, 108, 108, 0.1);
  color: #F56C6C;
}

.stat-card.optimized .icon-box {
  background: rgba(85, 110, 255, 0.1);
  color: #5594FF;
}
</style>