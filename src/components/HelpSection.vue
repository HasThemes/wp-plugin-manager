<template>
    <!-- Help Section -->
    <div class="htpm-help-section">
      <div class="help-icon">
        <el-icon><QuestionFilled /></el-icon>
      </div>
      <div class="help-content">
        <h3>{{ helpSection.title }}</h3>
        <p>{{ helpSection.description }}</p>
        <div class="help-actions">
          <el-button class="help-btn" @click="openDocumentation">
            <el-icon><Document /></el-icon>
            <span>{{ helpSection.documentation }}</span>
          </el-button>
          <el-button class="help-btn"  @click="openVideoTutorial">
            <el-icon><VideoPlay /></el-icon>
            <span>{{ helpSection.videoTutorial }}</span>
          </el-button>
          <el-button class="help-btn" @click="openSupport">
            <el-icon><Service /></el-icon>
            <span>{{ helpSection.support }}</span>
          </el-button>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { QuestionFilled, Document, VideoPlay, Service } from '@element-plus/icons-vue'
const helpSection = ref({
      title: window.HTPMM?.helpSection?.title ?? '',
      description: window.HTPMM?.helpSection?.description ?? '',
      documentation: window.HTPMM?.helpSection?.documentation ?? '',
      videoTutorial: window.HTPMM?.helpSection?.videoTutorial ?? '',
      support: window.HTPMM?.helpSection?.support ?? '',
    });

    const openSupport = () => {
      window.open('https://hasthemes.com/support/', '_blank');
    }
    const openDocumentation = () => {
      window.open('https://hasthemes.com/support/', '_blank');
    }
    const openVideoTutorial = () => {
      window.open('https://hasthemes.com/support/', '_blank');
    }
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
