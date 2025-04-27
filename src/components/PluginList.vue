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

    <!-- Need Help Section -->
    <div class="htpm-help-section">
      <div class="help-icon">
        <el-icon><QuestionFilled /></el-icon>
      </div>
      <h3>Need Help with Plugin Manager?</h3>
      <p>Our comprehensive documentation provides detailed information on how to use Plugin Manager effectively to improve your website's performance.</p>
      <div class="help-actions">
        <el-button @click="$emit('show-documentation')" type="primary" plain>
          <el-icon><Document /></el-icon>
          Documentation
        </el-button>
        <el-button type="primary" plain>
          <el-icon><VideoPlay /></el-icon>
          Video Tutorial
        </el-button>
        <el-button type="primary" plain>
          <el-icon><Service /></el-icon>
          Support
        </el-button>
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
    text-align: center;
    border: 1px solid #ebeef5;

    .help-icon {
      width: 48px;
      height: 48px;
      background: var(--el-color-primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
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
      justify-content: center;

      .el-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        height: auto;
        border-radius: 4px;
        font-weight: 500;
      }
    }
  }
}
</style>
