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
            <el-icon><Check /></el-icon>
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
            <el-icon><Close /></el-icon>
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
import { defineProps } from 'vue'
import { List, Check, Refresh, Close } from '@element-plus/icons-vue'

const props = defineProps({
  stats: {
    type: Object,
    required: true
  }
})
</script>

<style lang="scss" scoped>
.stats-cards {
  margin-bottom: 24px;

  .stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;

    &:hover {
      transform: translateY(-2px);
    }

    .icon-box {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;

      .el-icon {
        font-size: 24px;
        color: #fff;
      }
    }

    .stat-content {
      flex: 1;

      .stat-value {
        font-size: 24px;
        font-weight: 600;
        color: #303133;
        line-height: 1.2;
      }

      .stat-label {
        font-size: 14px;
        color: #909399;
        margin-top: 4px;
      }
    }

    &.total {
      .icon-box {
        background: linear-gradient(135deg, #409EFF, #3a8ee6);
      }
    }

    &.active {
      .icon-box {
        background: linear-gradient(135deg, #67C23A, #529b2e);
      }
    }

    &.updates {
      .icon-box {
        background: linear-gradient(135deg, #E6A23C, #b88230);
      }
    }

    &.inactive {
      .icon-box {
        background: linear-gradient(135deg, #F56C6C, #c45656);
      }
    }
  }
}
</style>
