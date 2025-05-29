<template>
  <div class="backend-modal-content">
      <!-- Action Type (same as frontend) -->
      <div class="form-field">
      <label>{{ modalSettingsFields?.action?.label }} <span v-if="modalSettingsFields?.action?.proBadge" class="pro-badge">{{proLabel}}</span></label>
      <el-select v-model="pluginSettings.condition_type" class="w-full" @change="(value) => handleProFeatureSelect('action', value)">
        <el-option v-for="(label, value) in modalSettingsFields?.action?.options" :key="value" :label="label + (modalSettingsFields?.action?.pro?.includes(value) ? ' (' + proLabel + ')' : '')" :value="value" :disabled="modalSettingsFields?.action?.pro?.includes(value)" />
      </el-select>
      <div class="field-desc">{{ modalSettingsFields?.action?.description }}</div>
    </div>
    <!-- Admin Area Scope (Broad Categories) -->
    <div class="form-field">
      <label>{{ modalSettingsFields?.admin_scope?.label }} <span v-if="modalSettingsFields?.admin_scope?.proBadge" class="pro-badge">{{proLabel}}</span></label>
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

    <!-- Backend Page Selection with Grouped Options -->
    <div class="form-field">
      <label>{{ labels_texts?.select_admin_pages || 'Select Admin Pages:' }}</label>
      <el-select 
        v-model="pluginSettings.backend_pages" 
        multiple 
        filterable 
        class="w-full" 
        :loading="loadingBackendPages"
        placeholder="Select admin pages..."
      >
        <!-- Group by WordPress admin sections -->
        <template v-for="group in backendPageGroups" :key="group.label">
          <el-option-group :label="group.label">
            <el-option 
              v-for="option in group.options" 
              :key="option.value" 
              :label="option.label" 
              :value="option.value"
            >
              <div class="admin-page-option">
                <span class="page-title">{{ option.label }}</span>
                <span class="page-url" v-if="option.url">{{ option.url }}</span>
              </div>
            </el-option>
          </el-option-group>
        </template>
      </el-select>
      <div class="field-desc">{{ modalSettingsFields?.page_selection?.description || 'Choose specific admin pages where you want to apply these settings.' }}</div>
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
          />
        </el-select>
        <el-input 
          v-model="pluginSettings.backend_condition_list.value[index]" 
          placeholder="e.g: edit.php, post.php, index.php"
          class="condition-value"
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

  if (!props.pluginSettings.backend_user_roles) {
    props.pluginSettings.backend_user_roles = []
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
  // if (!props.isPro) {
  //   openProModal()
  //   return
  // }
  
  const name = props.pluginSettings.backend_condition_list.name[index]
  const value = props.pluginSettings.backend_condition_list.value[index]
  
  props.pluginSettings.backend_condition_list.name.splice(index + 1, 0, name)
  props.pluginSettings.backend_condition_list.value.splice(index + 1, 0, value)
}

const addBackendCondition = () => {
  // if (!props.isPro) {
  //   openProModal()
  //   return
  // }
  
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
  
  .page-title {
    font-weight: 500;
    color: #303133;
  }
  
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
</style>