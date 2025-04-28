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
import { computed } from 'vue'
import { List, CircleCheck, Refresh, Close } from '@element-plus/icons-vue'
import { usePluginStore } from '../store/plugins'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalPlugins: 0,
      activePlugins: 0,
      updateAvailable: 0,
      inactivePlugins: 0
    })
  }
})

// If we're not getting stats from props, use the store
const store = usePluginStore()
const storeStats = computed(() => ({
  totalPlugins: store.totalPlugins,
  activePlugins: store.activePlugins.length,
  updateAvailable: store.updateAvailable.length,
  inactivePlugins: store.inactivePlugins.length
}))

// Use either props or store stats
const displayStats = computed(() => {
  // If any prop stat is 0 and we have store stats available, use store stats
  if (
    (props.stats.totalPlugins === 0 || 
     props.stats.activePlugins === 0) && 
    store.plugins.length > 0
  ) {
    return storeStats.value
  }
  return props.stats
})
</script>

<style lang="scss" scoped>
.stats-cards {
  margin-bottom: 24px;

  .stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 30px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.08);
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
        color: #6b7280;
        margin-top: 4px;
        font-weight: 500;
      }
    }

    &.total {
      .icon-box {
        background: rgba(67, 97, 238, 0.1);
        & .el-icon {
          color: #4361ee;
        }
      }
    }

    &.active {
      .icon-box {
        background: rgba(0, 163, 42, 0.1);
        & .el-icon {
          color: #00a32a;
        }
      }
    }

    &.updates {
      .icon-box {
        background: rgba(219, 166, 23, 0.1);
        & .el-icon {
          color: #dba617;
        }
      }
    }

    &.inactive {
      .icon-box {
        background: rgba(214, 54, 56, 0.1);
        & .el-icon {
          color: #d63638;
        }
      }
    }
  }
}
</style>