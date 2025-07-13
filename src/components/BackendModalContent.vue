<template>
  <div class="backend-modal-content">
    <!-- Status Field -->
    <div class="form-field status-field">
        <div class="status-control">
          <el-switch
            v-model="pluginSettings.backend_status"
            class="status-switch"
            :disabled="modalSettingsFields?.status?.proBadge"
            @click="modalSettingsFields?.status?.proBadge && openProModal()"
          />
          <span class="status-label">
            {{ pluginSettings.backend_status ? 'Enabled' : 'Disabled' }}
          </span>
          <!-- <span v-if="modalSettingsFields?.status?.proBadge" class="pro-badge">{{proLabel}}</span> -->
        </div>
        <div class="field-desc">{{ modalSettingsFields?.status?.description || 'Enable or disable this configuration. When disabled, settings are saved but not applied.' }}</div>
      </div>
      <!-- Action Type (same as frontend) -->
      <div class="form-field">
      <label>{{ modalSettingsFields?.action_backend?.label }}</label>
      <el-select v-model="pluginSettings.backend_condition_type" class="w-full" @change="(value) => handleProFeatureSelect('action', value)" :disabled="modalSettingsFields?.action_backend?.proBadge">
        <el-option v-for="(label, value) in modalSettingsFields?.action_backend?.options" :key="value" :label="label + (modalSettingsFields?.action_backend?.pro?.includes(value) ? ' (' + proLabel + ')' : '')" :value="value" :disabled="modalSettingsFields?.action_backend?.pro?.includes(value)" />
      </el-select>
      <div class="field-desc">{{ modalSettingsFields?.action_backend?.description }}</div>
    </div>
    <!-- Admin Area Scope (Broad Categories) -->
    <div class="form-field">
      <label>{{ modalSettingsFields?.admin_scope?.label }}</label>
      <el-select 
          v-model="pluginSettings.admin_scope" 
          multiple 
          filterable 
          class="w-full" 
          placeholder="Select admin areas..."
          @change="(value) => handleProFeatureSelect('admin_scope', value)"
        >
        <el-option 
          v-for="(label, value) in modalSettingsFields?.admin_scope?.options" 
          :key="value" 
          :label="label + (modalSettingsFields?.admin_scope?.pro?.includes(value) ? ' (' + proLabel + ')' : '')" 
          :value="value" 
          :disabled="modalSettingsFields?.admin_scope?.pro?.includes(value)" 
        />
      </el-select>
      <div class="field-desc">{{ modalSettingsFields?.admin_scope?.description }}</div>
    </div>

    <!-- Custom Page Conditions (Specific Targeting) -->
    <div class="form-field">
      <label>{{ labels_texts?.custom_page_conditions || 'Custom Page Conditions:' }}</label>
      <div v-for="(condition, index) in pluginSettings.backend_condition_list.name" :key="index" class="uri-condition">
        <el-select v-model="pluginSettings.backend_condition_list.name[index]" class="condition-type">
          <el-option 
            v-for="(label, value) in modalSettingsFields?.custom_conditions?.options" 
            :key="value" 
            :label="label" 
            :value="value" 
            :disabled="modalSettingsFields?.custom_conditions?.proBadge"
          />
        </el-select>
        <el-input 
          v-model="pluginSettings.backend_condition_list.value[index]" 
          placeholder="e.g: edit.php, post.php, index.php"
          class="condition-value"
          :disabled="modalSettingsFields?.custom_conditions?.proBadge"
        />
        <div class="condition-actions">
          <el-button 
            type="danger" 
            circle 
            size="small" 
            @click="removeBackendCondition(index)" 
            :disabled="pluginSettings.backend_condition_list.name.length <= 1"
          >
            <el-icon><Delete /></el-icon>
          </el-button>
          <el-button type="primary" circle size="small" @click="cloneBackendCondition(index)">
            <el-icon><CopyDocument /></el-icon>
          </el-button>
        </div>
      </div>
      <el-button type="primary" plain size="small" @click="addBackendCondition" class="mt-3 add-condition" color="#fff">
        <el-icon><Plus /></el-icon> {{ labels_texts?.add_condition || 'Add Condition' }}
      </el-button>
      <div class="field-desc">{{ modalSettingsFields?.custom_conditions?.description || 'Define specific conditions for targeting exact admin pages or screens. E.g., use "edit.php" for Posts page, "post.php" for Edit Post page.' }}</div>
    </div>
  </div>
</template>

<script setup>
import { Delete, Plus, CopyDocument } from '@element-plus/icons-vue'
import { ref, computed, onMounted } from 'vue'

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
  pages: {
    type: Array,
    required: true
  },
  loadingPages: {
    type: Boolean,
    default: false
  }
})

const labels_texts = HTPMM.adminSettings.labels_texts
const loadingBackendPages = ref(false)

// Get backend page groups from the localized settings
const backendPageGroups = computed(() => {
  return props.modalSettingsFields?.page_selection?.groups || 
         HTPMM?.adminSettings?.backend_modal_settings?.page_selection?.groups || []
})

const emit = defineEmits([
  'handleProFeatureSelect',
  'openProModal',
  'removeCondition',
  'cloneCondition',
  'addCondition'
])

// Initialize backend-specific settings if they don't exist
onMounted(() => {
  if (!props.pluginSettings.admin_scope) {
    props.pluginSettings.admin_scope = []
  }
  
  if (!props.pluginSettings.backend_pages) {
    props.pluginSettings.backend_pages = []
  }
  
  if (!props.pluginSettings.backend_condition_list) {
    props.pluginSettings.backend_condition_list = {
      name: ['admin_page_equals'],
      value: [''],
    }
  }

  if (!props.isPro) {
    props.pluginSettings.backend_status = false
  }

})

const handleProFeatureSelect = (field, value) => {
  emit('handleProFeatureSelect', field, value)
}

const openProModal = () => {
  emit('openProModal')
}

const removeBackendCondition = (index) => {
  if (props.pluginSettings.backend_condition_list.name.length <= 1) return
  
  props.pluginSettings.backend_condition_list.name.splice(index, 1)
  props.pluginSettings.backend_condition_list.value.splice(index, 1)
}

const cloneBackendCondition = (index) => {
  if (!props.isPro) {
    openProModal()
    return
  }
  
  const name = props.pluginSettings.backend_condition_list.name[index]
  const value = props.pluginSettings.backend_condition_list.value[index]
  
  props.pluginSettings.backend_condition_list.name.splice(index + 1, 0, name)
  props.pluginSettings.backend_condition_list.value.splice(index + 1, 0, value)
}

const addBackendCondition = () => {
  if (!props.isPro) {
    openProModal()
    return
  }
  
  props.pluginSettings.backend_condition_list.name.push('admin_page_equals')
  props.pluginSettings.backend_condition_list.value.push('')
}
</script>

<style lang="scss" scoped>
.pro-badge {
  background-color: rgba(214, 54, 56, 0.1);
  border: 1px solid #d636386b;
  color: #d63638;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 10px;
  font-weight: 600;
  line-height: 1;
  text-transform: uppercase;
}

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
    font-size: 12px;
    color: #909399;
  }
}

.admin-page-option {
  display: flex;
  flex-direction: column;
  
  // .page-title {
  //   font-weight: 400;
  //   color: #303133;
  // }
  
  .page-url {
    font-size: 11px;
    color: #909399;
    margin-top: 2px;
  }
}

.pro-feature-placeholder {
  padding: 30px 20px;
  text-align: center;
  background: #f8f9fa;
  border-radius: 6px;
  border: 2px dashed #dee2e6;
  
  :deep(.el-empty__description) {
    margin: 10px 0;
    font-size: 13px;
  }
}

.role-restrictions {
  .role-checkboxes {
    display: flex;
    flex-direction: column;
    gap: 8px;
    
    :deep(.el-checkbox) {
      margin: 0;
      
      .el-checkbox__label {
        font-size: 14px;
        color: #606266;
      }
    }
  }
}

.uri-condition {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
  align-items: flex-start;

  .condition-type {
    width: 180px;
  }

  .condition-value {
    flex: 1;
  }

  .condition-actions {
    display: flex;
    gap: 5px;
  }
}

.w-full {
  width: 100%;
}

.mt-3 {
  margin-top: 12px;
}

.add-condition:hover {
  background-color: rgb(121.3, 187.1, 255);
  cursor: pointer;
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
    
    &.enabled {
      color: #67c23a;
    }
    
    &.disabled {
      color: #909399;
    }
  }
}

.status-switch {
  &.el-switch {
    --el-switch-on-color: #409eff;
    --el-switch-off-color: #dcdfe6;
    
    .el-switch__core {
      min-width: 50px;
      height: 28px;
      border-radius: 14px;
      
      .el-switch__action {
        width: 24px;
        height: 24px;
        top: 2px;
        border-radius: 50%;
      }
    }
    
    &.is-checked .el-switch__core {
      background-color: #409eff;
    }
    
    &:not(.is-checked) .el-switch__core {
      background-color: #dcdfe6;
    }
  }
}
</style>