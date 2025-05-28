<template>
    <div class="backend-modal-content">
      <!-- Condition Type Selector -->
      <div class="form-field">
        <label>{{ modalSettingsFields?.action?.label }} <span v-if="modalSettingsFields?.device_types?.proBadge" class="pro-badge">{{proLabel}}</span></label>
        <el-select v-model="pluginSettings.condition_type" class="w-full" @change="(value) => handleProFeatureSelect('action', value)">
          <el-option v-for="(label, value) in modalSettingsFields?.action?.options" :key="value" :label="label + (modalSettingsFields?.action?.pro?.includes(value) ? ' (' + proLabel + ')' : '')" :value="value" :disabled="modalSettingsFields?.action?.pro?.includes(value)" />
        </el-select>
        <div class="field-desc">{{ modalSettingsFields?.action?.description }}</div>
      </div>
  
      <!-- Pages Selection -->
      <div class="form-field">
        <label>{{ labels_texts?.select_pages }}</label>
        <el-select v-model="pluginSettings.pages" multiple filterable class="w-full" :loading="loadingPages" :disabled="pluginSettings.uri_type === 'page_post_cpt' && !isPro" @click="pluginSettings.uri_type === 'page_post_cpt' && !isPro && openProModal()">
          <el-option label="All Pages" value="all_pages,all_pages" />
          <el-option 
            v-for="page in pages" 
            :key="page.id" 
            :label="page.title" 
            :value="`${page.id},${page.url}`" 
          />
        </el-select>
      </div>
  
      <!-- Custom URI Conditions -->
      <div class="form-field">
        <label>{{ labels_texts?.uri_conditions }}</label>
        <div v-for="(condition, index) in pluginSettings.condition_list.name" :key="index" class="uri-condition">
          <el-select v-model="pluginSettings.condition_list.name[index]" class="condition-type">
            <el-option label="URI Equals" value="uri_equals" />
            <el-option label="URI Not Equals" value="uri_not_equals" />
            <el-option label="URI Contains" value="uri_contains" />
            <el-option label="URI Not Contains" value="uri_not_contains" />
          </el-select>
          <el-input 
            v-model="pluginSettings.condition_list.value[index]" 
            placeholder="e.g: contact-us or leave blank for homepage"
            class="condition-value"
            :disabled="!isPro"
            @click="!isPro && openProModal()"
          />
          <div class="condition-actions">
            <el-button 
              type="danger" 
              circle 
              size="small" 
              @click="removeCondition(index)" 
              :disabled="pluginSettings.condition_list.name.length <= 1"
            >
              <el-icon><Delete /></el-icon>
            </el-button>
            <el-button type="primary" circle size="small" @click="cloneCondition(index)" :disabled="!isPro">
              <el-icon><CopyDocument /></el-icon>
            </el-button>
          </div>
        </div>
        <el-button type="primary" plain size="small" @click="addCondition" class="mt-3 add-condition" color="#fff" :disabled="!isPro">
          <el-icon><Plus /></el-icon> {{labels_texts?.add_condition }}
        </el-button>
        <div class="field-desc">{{ labels_texts?.field_desc_uri }}</div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { Delete, Plus, CopyDocument } from '@element-plus/icons-vue'
  
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
  const emit = defineEmits([
    'handleProFeatureSelect',
    'openProModal',
    'removeCondition',
    'cloneCondition',
    'addCondition'
  ])
  
  const handleProFeatureSelect = (field, value) => {
    emit('handleProFeatureSelect', field, value)
  }
  
  const openProModal = () => {
    emit('openProModal')
  }
  
  const removeCondition = (index) => {
    emit('removeCondition', index)
  }
  
  const cloneCondition = (index) => {
    emit('cloneCondition', index)
  }
  
  const addCondition = () => {
    emit('addCondition')
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
  
  .uri-condition {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
    align-items: flex-start;
  
    .condition-type {
      width: 150px;
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