<template>
    <!-- Help Section -->
    <div class="htpm-help-section">
      <div class="help-icon">
        <el-icon><QuestionFilled /></el-icon>
      </div>
      <div class="help-content">
        <h3>Need Help with Plugin Manager?</h3>
        <p>Our comprehensive documentation provides detailed information on how to use Plugin Manager effectively to improve your website's performance.</p>
        <div class="help-actions">
          <el-button class="help-btn" @click="$emit('show-documentation')">
            <el-icon><Document /></el-icon>
            <span>Documentation</span>
          </el-button>
          <el-button class="help-btn">
            <el-icon><VideoPlay /></el-icon>
            <span>Video Tutorial</span>
          </el-button>
          <el-button class="help-btn">
            <el-icon><Service /></el-icon>
            <span>Support</span>
          </el-button>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { QuestionFilled, Document, VideoPlay, Service } from '@element-plus/icons-vue'

</script>

<style lang="scss" scoped>
  .htpm-help-section {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    margin-top: 30px;
    display: flex;
    justify-content: start;
    gap: 16px;

    .help-icon {
      width: 48px;
      height: 48px;
      background: var(--el-color-primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 24px;
    }

    h3 {
      font-size: 18px;
      font-weight: 600;
      margin: 0 0 12px;
      color: #333;
    }

    p {
      color: #606266;
      margin: 0 auto 20px;
      font-size: 14px;
      line-height: 1.6;
      max-width: 600px;
    }

    .help-actions {
      display: flex;
      gap: 12px;
      justify-content: start;
      flex-wrap: wrap;

      .help-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0;
        height: auto;
        font-weight: 500;
        background: transparent;
        border: none;
        color:var(--el-color-primary);

        .el-icon {
          color: var(--el-color-primary);
        }

        &:hover {
          background: transparent;
          border-color: none;
          color: var(--el-color-secondary);
          text-decoration: underline;
        }
      }
    }
  }
</style>
