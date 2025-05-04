<template>
  <div class="stats-cards">
    <el-row :gutter="20">
      <el-col :span="6">
        <div class="stat-card total">
          <div class="icon-box">
            <el-icon><List /></el-icon>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ stats.totalPlugins }}</div>
            <div class="stat-label">Total Plugins</div>
          </div>
        </div>
      </el-col>

      <el-col :span="6">
        <div class="stat-card active">
          <div class="icon-box">
            <el-icon><CircleCheck /></el-icon>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ stats.activePlugins }}</div>
            <div class="stat-label">Active Plugins</div>
          </div>
        </div>
      </el-col>

      <el-col :span="6">
        <div class="stat-card updates">
          <div class="icon-box">
            <el-icon><Refresh /></el-icon>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ stats.updateAvailable }}</div>
            <div class="stat-label">Update Available</div>
          </div>
        </div>
      </el-col>

      <el-col :span="6">
        <div class="stat-card inactive">
          <div class="icon-box">
            <el-icon><CircleClose /></el-icon>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ stats.inactivePlugins }}</div>
            <div class="stat-label">Inactive Plugins</div>
          </div>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { usePluginStore } from '../store/plugins'
import { List, CircleCheck, Refresh, CircleClose } from '@element-plus/icons-vue'

const store = usePluginStore()

// Load plugins when component mounts
onMounted(async () => {
  await store.fetchPlugins()
})

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
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
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