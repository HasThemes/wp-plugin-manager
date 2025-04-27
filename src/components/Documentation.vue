<template>
  <el-dialog
    v-model="dialogVisible"
    title="Documentation"
    width="80%"
    class="documentation-dialog"
  >
    <el-container>
      <el-aside width="250px" class="doc-sidebar">
        <el-menu
          default-active="getting-started"
          class="doc-menu"
          @select="handleDocSelect"
        >
          <el-menu-item index="getting-started">
            <el-icon><Guide /></el-icon>
            <span>Getting Started</span>
          </el-menu-item>
          <el-menu-item index="installation">
            <el-icon><Download /></el-icon>
            <span>Installation</span>
          </el-menu-item>
          <el-menu-item index="configuration">
            <el-icon><Setting /></el-icon>
            <span>Configuration</span>
          </el-menu-item>
          <el-menu-item index="usage">
            <el-icon><Operation /></el-icon>
            <span>Basic Usage</span>
          </el-menu-item>
          <el-menu-item index="advanced">
            <el-icon><Cpu /></el-icon>
            <span>Advanced Features</span>
          </el-menu-item>
          <el-menu-item index="faq">
            <el-icon><QuestionFilled /></el-icon>
            <span>FAQ</span>
          </el-menu-item>
        </el-menu>
      </el-aside>
      <el-main class="doc-content">
        <div v-if="currentDoc === 'getting-started'">
          <h2>Getting Started with WP Plugin Manager</h2>
          <p>Welcome to WP Plugin Manager! This guide will help you get started with managing your WordPress plugins efficiently.</p>
          <el-divider />
          <h3>Key Features</h3>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-card>
                <template #header>
                  <div class="card-header">
                    <el-icon><Monitor /></el-icon>
                    <span>Easy Management</span>
                  </div>
                </template>
                <div class="card-content">Manage all your plugins from a single dashboard</div>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card>
                <template #header>
                  <div class="card-header">
                    <el-icon><Refresh /></el-icon>
                    <span>Auto Updates</span>
                  </div>
                </template>
                <div class="card-content">Keep plugins updated automatically</div>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card>
                <template #header>
                  <div class="card-header">
                    <el-icon><Lock /></el-icon>
                    <span>Security</span>
                  </div>
                </template>
                <div class="card-content">Monitor plugin security and vulnerabilities</div>
              </el-card>
            </el-col>
          </el-row>
        </div>
      </el-main>
    </el-container>
  </el-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
  Guide,
  Download,
  Setting,
  Operation,
  Cpu,
  QuestionFilled,
  Monitor,
  Refresh,
  Lock
} from '@element-plus/icons-vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const dialogVisible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const currentDoc = ref('getting-started')

const handleDocSelect = (key) => {
  currentDoc.value = key
}
</script>

<style lang="scss" scoped>
.documentation-dialog {
  :deep(.el-dialog__body) {
    padding: 0;
  }

  .doc-sidebar {
    background: #f5f7fa;
    border-right: 1px solid #e4e7ed;
    height: calc(80vh - 100px);

    .doc-menu {
      border-right: none;

      .el-menu-item {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 50px;
        padding: 0 16px;
        font-weight: 500;

        .el-icon {
          font-size: 18px;
        }
      }
    }
  }

  .doc-content {
    padding: 24px;
    height: calc(80vh - 100px);
    overflow-y: auto;

    h2 {
      margin-top: 0;
      margin-bottom: 16px;
      font-size: 24px;
      font-weight: 600;
    }

    h3 {
      margin-top: 24px;
      margin-bottom: 16px;
      font-size: 18px;
      font-weight: 600;
    }

    p {
      color: #606266;
      line-height: 1.6;
      margin-bottom: 16px;
    }

    .el-divider {
      margin: 24px 0;
    }

    .el-card {
      margin-bottom: 16px;

      .card-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;

        .el-icon {
          font-size: 18px;
        }
      }

      .card-content {
        color: #606266;
        line-height: 1.6;
      }
    }
  }
}
</style>
