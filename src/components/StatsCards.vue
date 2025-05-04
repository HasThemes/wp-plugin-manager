<template>
  <div class="stats-cards">
    <el-row :gutter="20">
      <el-col :span="6" v-for="(stat, index) in statsList" :key="index">
        <template v-if="!isLoading">
          <div :class="['stat-card', stat.type]">
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
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { usePluginStore } from '../store/plugins'
import { List, CircleCheck, Refresh, CircleClose } from '@element-plus/icons-vue'

const store = usePluginStore()
const isLoading = ref(true)  // Local loading state

// Load plugins when component mounts
onMounted(async () => {
  try {
    isLoading.value = true
    await store.fetchPlugins()
  } finally {
    isLoading.value = false
  }
})

const statsList = [
  { type: 'total', icon: List, key: 'totalPlugins', label: 'Total Plugins' },
  { type: 'active', icon: CircleCheck, key: 'activePlugins', label: 'Active Plugins' },
  { type: 'updates', icon: Refresh, key: 'updateAvailable', label: 'Update Available' },
  { type: 'inactive', icon: CircleClose, key: 'inactivePlugins', label: 'Inactive Plugins' }
]

// Compute stats from store getters
const stats = computed(() => ({
  totalPlugins: store.totalPlugins,
  activePlugins: store.wpActivePlugins.length,
  updateAvailable: store.updateAvailable.length,
  inactivePlugins: store.inactivePlugins.length
}))
</script>

<style scoped>
.stats-cards {
  margin-bottom: 20px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  height: 88px;
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

.skeleton-icon :deep(.el-skeleton-item) {
  width: 100%;
  height: 100%;
  background: rgba(64, 158, 255, 0.1);
}

.skeleton-text {
  flex-grow: 1;
}
.skeleton-text .el-skeleton {
    display: flex;
    flex-direction: column;
}

.icon-box {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
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
  color: #909399;
  font-size: 14px;
}
</style>