<template>
  <div class="htpm-plugins">
    <div class="htpm-plugins-header">
      <h2>Manage Plugins</h2>
      <div class="htpm-plugins-actions">
        <el-input
          v-model="searchQuery"
          placeholder="Search plugins..."
          :prefix-icon="Search"
        />
        <el-button @click="handleFilter" :icon="Filter">Filter</el-button>
        <el-button @click="handleSort" :icon="Sort">Sort</el-button>
      </div>
    </div>

    <el-table :data="plugins" style="width: 100%">
      <el-table-column prop="name" label="Plugin Name">
        <template #default="{ row }">
          <div class="plugin-info">
            <el-icon><Box /></el-icon>
            <span class="plugin-name">{{ row.name }}</span>
            <el-tag size="small" type="success" v-if="row.active">Active</el-tag>
          </div>
        </template>
      </el-table-column>
      <el-table-column prop="version" label="Version" width="120" />
      <el-table-column prop="author" label="Author" width="180" />
      <el-table-column label="Actions" width="200" align="right">
        <template #default="{ row }">
          <el-button-group>
            <el-button
              :type="row.active ? 'danger' : 'success'"
              size="small"
              @click="$emit('toggle-plugin', row)"
            >
              {{ row.active ? 'Deactivate' : 'Activate' }}
            </el-button>
            <el-button
              type="primary"
              size="small"
              @click="$emit('open-settings', row)"
            >
              Settings
            </el-button>
          </el-button-group>
        </template>
      </el-table-column>
    </el-table>

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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Search, Filter, Sort, Box, QuestionFilled, Document, VideoPlay, Service } from '@element-plus/icons-vue'

const searchQuery = ref('')

const props = defineProps({
  plugins: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['toggle-plugin', 'open-settings'])

const handleFilter = () => {
  // Implement filter logic
}

const handleSort = () => {
  // Implement sort logic
}

const togglePlugin = (plugin) => {
  emit('toggle-plugin', plugin)
}

const openSettings = (plugin) => {
  emit('open-settings', plugin)
}
</script>

<style lang="scss" scoped>
.htpm-plugins {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  margin-top: 20px;

  .htpm-plugins-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;

    h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .htpm-plugins-actions {
      display: flex;
      gap: 8px;
      align-items: center;

      .el-input {
        width: 200px;
      }
    }
  }

  .plugin-info {
    display: flex;
    align-items: center;
    gap: 8px;

    .el-icon {
      font-size: 18px;
    }

    .plugin-name {
      font-weight: 500;
    }
  }

  .htpm-help-section {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    margin-top: 30px;
    border: 1px solid #ebeef5;
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
}
</style>
