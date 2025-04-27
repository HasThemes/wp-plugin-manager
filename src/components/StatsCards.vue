<template>
  <div class="htpm-stats">
    <el-row :gutter="20">
      <el-col :span="6">
        <el-card>
          <template #header>
            <div class="card-header">
              <el-icon><Collection /></el-icon>
              <span>{{ stats.totalPlugins }}</span>
            </div>
          </template>
          <div class="card-content">Total Plugins</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card>
          <template #header>
            <div class="card-header">
              <el-icon><Select /></el-icon>
              <span>{{ stats.activePlugins }}</span>
            </div>
          </template>
          <div class="card-content">Active Plugins</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card>
          <template #header>
            <div class="card-header">
              <el-icon><Timer /></el-icon>
              <span>{{ stats.updateAvailable }}</span>
            </div>
          </template>
          <div class="card-content">Update Available</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card>
          <template #header>
            <div class="card-header">
              <el-icon><Remove /></el-icon>
              <span>{{ stats.inactivePlugins }}</span>
            </div>
          </template>
          <div class="card-content">Inactive Plugins</div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { Collection, Select, Timer, Remove } from '@element-plus/icons-vue'

defineProps({
  stats: {
    type: Object,
    required: true
  }
})
</script>

<style lang="scss" scoped>
.htpm-stats {
  margin-bottom: 20px;

  .card-header {
    display: flex;
    align-items: center;
    gap: 8px;

    .el-icon {
      font-size: 24px;
    }

    span {
      font-size: 24px;
      font-weight: 600;
    }
  }

  .card-content {
    color: #606266;
    font-size: 14px;
  }
}
</style>
