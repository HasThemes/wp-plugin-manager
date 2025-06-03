<template>
  <div class="conflict-modal-content">
    <!-- Status Field -->
    <div class="form-field status-field">
      <div class="status-control">
        <el-switch
          v-model="pluginSettings.conflict_status"
          class="status-switch"
        />
        <span class="status-label">
          {{ pluginSettings.conflict_status ? 'Enabled' : 'Disabled' }}
        </span>
      </div>
      <div class="field-desc">{{ modalSettingsFields?.status?.description || 'Enable or disable conflict checking for this plugin.' }}</div>
    </div>

    <div class="form-field">
      <label>{{ modalSettingsFields?.conflict_plugins?.label }}</label>
      <div class="field-desc">{{ modalSettingsFields?.conflict_plugins?.description }}</div>
      
      <el-select 
        v-model="pluginSettings.conflicting_plugins" 
        multiple 
        filterable 
        class="w-full" 
        placeholder="Select conflicting plugins..."
        :disabled="!pluginSettings.conflict_status"
      >
        <el-option 
          v-for="plugin in availablePlugins" 
          :key="plugin.value" 
          :label="plugin.label" 
          :value="plugin.value"
          :disabled="plugin.value === currentPluginFile"
        />
      </el-select>
      
      <div v-if="pluginSettings.conflicting_plugins && pluginSettings.conflicting_plugins.length > 0" class="selected-conflicts">
        <p class="conflict-info">
          <el-icon><WarningFilled /></el-icon>
          This plugin will be automatically disabled when any of the selected plugins are active.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { WarningFilled } from '@element-plus/icons-vue'
import { onMounted } from 'vue'

const props = defineProps({
  pluginSettings: {
    type: Object,
    required: true
  },
  modalSettingsFields: {
    type: Object,
    required: true
  },
  proLabel: {
    type: String,
    required: true
  },
  isPro: {
    type: Boolean,
    required: true
  },
  availablePlugins: {
    type: Array,
    default: () => []
  },
  currentPluginFile: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['openProModal'])

// Initialize conflict settings
onMounted(() => {
  if (!props.pluginSettings.conflicting_plugins) {
    props.pluginSettings.conflicting_plugins = []
  }
  if (props.pluginSettings.conflict_status === undefined) {
    props.pluginSettings.conflict_status = false
  }
})

const openProModal = () => {
  emit('openProModal')
}
</script>

<style lang="scss" scoped>
.form-field {
  margin-bottom: 20px;
  position: relative;

  label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #606266;
  }

  .field-desc {
    margin-top: 4px;
    margin-bottom: 16px;
    font-size: 12px;
    color: #909399;
  }
}

.w-full {
  width: 100%;
}

.selected-conflicts {
  margin-top: 12px;
  
  .conflict-info {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px;
    background: #fff7e6;
    border: 1px solid #ffd591;
    border-radius: 6px;
    color: #d48806;
    font-size: 13px;
    margin: 0;
    
    .el-icon {
      color: #fa8c16;
    }
  }
}

.status-field {
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #ebeef5;
  
  .status-control {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
  }
  
  .status-label {
    font-size: 16px;
    font-weight: 500;
    color: #303133;
  }
}

.status-switch {
  &.el-switch {
    --el-switch-on-color: #409eff;
    --el-switch-off-color: #dcdfe6;
  }
}
</style>