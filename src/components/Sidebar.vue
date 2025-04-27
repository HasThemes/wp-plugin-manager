<template>
      <!-- Sidebar -->
      <div class="htpm-sidebar">
        <!-- Upgrade Banner -->
        <el-card class="upgrade-card">
          <h2 class="upgrade-title">Upgrade to WP Plugin Manager Pro</h2>
          <p class="upgrade-description">Get More Features with Pro</p>
          <p class="upgrade-text">Unlock advanced plugin management, automatic updates, security scanning, and performance optimization with our Pro version.</p>
          <el-button type="primary" class="upgrade-button" @click="upgradeToPro">Upgrade Now</el-button>
        </el-card>

        <!-- Rating Section -->
        <el-card class="rating-card">
          <div class="rating-content">
            <el-icon class="star-icon"><Star /></el-icon>
            <h3 class="rating-title">Enjoying Plugin Manager?</h3>
            <p class="rating-text">Your feedback helps us improve. Please consider leaving a review!</p>
            <div class="rating-stars">
              <el-rate v-model="rating" :colors="rateColors" />
            </div>
            <el-button type="warning" class="rate-button" @click="ratePlugin">Rate Plugin</el-button>
          </div>
        </el-card>
      </div>
</template>

<script setup>
import { ref } from 'vue'
import { Star } from '@element-plus/icons-vue'

const rating = ref(0)
const rateColors = ['#F7BA2A', '#F7BA2A', '#F7BA2A']

const upgradeToPro = () => {
  window.open('https://hasthemes.com/wp-plugin-manager-pro', '_blank')
}

const ratePlugin = () => {
  window.open('https://wordpress.org/plugins/wp-plugin-manager/#reviews', '_blank')
}
</script>

<style lang="scss" scoped>
.htpm-sidebar {
  :deep(.el-card) {
    border: none;
    box-shadow: none !important;
    background: #fff;

    .el-card__body {
      padding: 0;
    }
  }

  .upgrade-card {
    margin-bottom: 30px;
    padding: 24px;
    border-radius: 8px;

    .upgrade-title {
      font-size: 18px;
      font-weight: 600;
      margin: 0 0 8px;
      color: #1e293b;
    }

    .upgrade-description {
      font-size: 14px;
      color: var(--el-color-primary);
      margin: 0 0 16px;
      font-weight: 500;
    }

    .upgrade-text {
      color: #64748b;
      line-height: 1.6;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .upgrade-button {
      width: 100%;
      height: 40px;
      font-weight: 500;
    }
  }

  .rating-card {
    padding: 24px;
    border-radius: 8px;

    .rating-content {
      text-align: center;

      .star-icon {
        font-size: 32px;
        color: #F7BA2A;
        margin-bottom: 16px;
      }

      .rating-title {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 8px;
        color: #1e293b;
      }

      .rating-text {
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 16px;
        font-size: 14px;
      }

      .rating-stars {
        margin-bottom: 16px;
      }

      .rate-button {
        width: 100%;
        height: 40px;
        font-weight: 500;
      }
    }
  }


  .rating-card {
    .rating-content {
      text-align: center;

      .star-icon {
        font-size: 32px;
        color: #F7BA2A;
        margin-bottom: 16px;
      }

      .rating-title {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 8px;
      }

      .rating-text {
        color: #606266;
        line-height: 1.6;
        margin-bottom: 16px;
      }

      .rating-stars {
        margin-bottom: 16px;
      }

      .rate-button {
        width: 100%;
      }
    }
  }
}
</style>
