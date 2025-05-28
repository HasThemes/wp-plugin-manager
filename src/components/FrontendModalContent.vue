<template>
    <div class="frontend-modal-content">
      <!-- Configuration settings for the plugin - Always visible now -->
      <div class="form-field">
        <label>{{ modalSettingsFields?.device_types?.label }} <span v-if="modalSettingsFields?.device_types?.proBadge" class="pro-badge">{{proLabel}}</span></label>
        <el-select v-model="pluginSettings.device_type" class="w-full" @change="(value) => handleProFeatureSelect('device_types', value)">
          <el-option v-for="(label, value) in modalSettingsFields?.device_types?.options" :key="value" :label="label + (modalSettingsFields?.device_types?.pro?.includes(value) ? ' (' + proLabel + ')' : '')" :value="value" :disabled="modalSettingsFields?.device_types?.pro?.includes(value)" />
        </el-select>
        <div class="field-desc">{{ modalSettingsFields?.device_types?.description }}</div>
      </div>
  
      <!-- Condition Type Selector -->
      <div class="form-field">
        <label>{{ modalSettingsFields?.action?.label }} <span v-if="modalSettingsFields?.device_types?.proBadge" class="pro-badge">{{proLabel}}</span></label>
        <el-select v-model="pluginSettings.condition_type" class="w-full" @change="(value) => handleProFeatureSelect('action', value)">
          <el-option v-for="(label, value) in modalSettingsFields?.action?.options" :key="value" :label="label + (modalSettingsFields?.action?.pro?.includes(value) ? ' (' + proLabel + ')' : '')" :value="value" :disabled="modalSettingsFields?.action?.pro?.includes(value)" />
        </el-select>
        <div class="field-desc">{{ modalSettingsFields?.action?.description }}</div>
      </div>
  
      <!-- URI Type Selector -->
      <div class="form-field">
        <label>{{ modalSettingsFields?.page_types?.label }}</label>
        <el-select v-model="pluginSettings.uri_type" class="w-full" @change="(value) => { handleUriTypeChange(); handleProFeatureSelect('page_types', value); }">
          <el-option v-for="(label, value) in modalSettingsFields?.page_types?.options" :key="value" :label="label + (modalSettingsFields?.page_types?.pro?.includes(value) ? ' (' + proLabel + ')' : '')" :value="value" />
        </el-select>
        <div class="field-desc">{{ modalSettingsFields?.page_types?.description }}</div>
        <el-tooltip
          v-if="pluginSettings.uri_type === 'page_post_cpt' && modalSettingsFields?.page_types?.toopTip?.page_post_cpt?.note"
          :content="modalSettingsFields?.page_types?.toopTip?.page_post_cpt?.note"
          placement="top"
        >
          <el-icon class="info-icon"><InfoFilled /></el-icon>
        </el-tooltip>
      </div>
  
      <!-- Post Types Selection -->
      <div class="form-field" v-if="pluginSettings.uri_type === 'page_post_cpt'">
        <label v-if="labels_texts?.select_post_types">{{ labels_texts?.select_post_types }}</label>
        <el-checkbox-group v-model="pluginSettings.post_types" @change="handlePostTypesChange" style="display: flex;gap: 10px;">
          <el-checkbox v-for="postType in filteredPostTypes" :key="postType.name" :label="postType.name" :disabled="!isPro" @click="!isPro && openProModal()" style="margin-bottom: 0;">
            {{ postType.label }}
          </el-checkbox>
        </el-checkbox-group>
      </div>
  
      <!-- Pages Selection -->
      <div 
        class="form-field" 
        v-if="['page', 'page_post'].includes(pluginSettings.uri_type) || 
              (pluginSettings.uri_type === 'page_post_cpt' && pluginSettings.post_types.includes('page'))"
      >
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
  
      <!-- Posts Selection -->
      <div 
        class="form-field" 
        v-if="['post', 'page_post'].includes(pluginSettings.uri_type) ||
             (pluginSettings.uri_type === 'page_post_cpt' && pluginSettings.post_types.includes('post'))"
      >
        <label>{{ labels_texts?.select_posts }}</label>
        <el-select v-model="pluginSettings.posts" multiple filterable class="w-full" :loading="loadingPosts" :disabled="pluginSettings.uri_type === 'page_post_cpt' && !isPro" @click="pluginSettings.uri_type === 'page_post_cpt' && !isPro && openProModal()">
          <el-option label="All Posts" value="all_posts,all_posts" />
          <el-option 
            v-for="post in posts" 
            :key="post.id" 
            :label="post.title" 
            :value="`${post.id},${post.url}`"
          />
        </el-select>
      </div>
  
      <!-- Custom Post Type Selections - Dynamically rendered for each selected post type -->
      <template v-if="pluginSettings.uri_type === 'page_post_cpt'">
        <div 
          v-for="postType in selectedCustomPostTypes" 
          :key="postType"
          class="form-field"
        >
          <label>{{ labels_texts?.select }} {{ formatPostTypeName(postType) }}:</label>
          <el-select 
            v-model="pluginSettings[postType + 's']" 
            multiple 
            filterable 
            class="w-full"
            :loading="loadingCustomPosts[postType]"
          >
            <el-option 
              :label="`All ${formatPostTypeName(postType)}s`" 
              :value="`all_${postType}s,all_${postType}s`"
            />
            <el-option 
              v-for="item in getCustomPostTypeItems(postType)" 
              :key="item.id" 
              :label="item.title" 
              :value="`${item.id},${item.url}`"
            />
          </el-select>
        </div>
      </template>
  
      <!-- Custom URI Conditions -->
      <template v-if="pluginSettings.uri_type === 'custom'">
        <div class="form-field">
          <label>{{ labels_texts?.uri_conditions }}</label>
          <div v-for="(condition, index) in pluginSettings.condition_list.name" :key="index" class="uri-condition">
            <el-select v-model="pluginSettings.condition_list.name[index]" class="condition-type" :disabled="!isPro" @click=" !isPro && openProModal()">
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
      </template>
    </div>
  </template>
  
  <script setup>
  import { Delete, Plus, CopyDocument, InfoFilled } from '@element-plus/icons-vue'
  
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
    filteredPostTypes: {
      type: Array,
      required: true
    },
    selectedCustomPostTypes: {
      type: Array,
      required: true
    },
    pages: {
      type: Array,
      required: true
    },
    posts: {
      type: Array,
      required: true
    },
    loadingPages: {
      type: Boolean,
      default: false
    },
    loadingPosts: {
      type: Boolean,
      default: false
    },
    loadingCustomPosts: {
      type: Object,
      default: () => ({})
    }
  })
  
  const emit = defineEmits([
    'handleProFeatureSelect',
    'handleUriTypeChange',
    'handlePostTypesChange',
    'openProModal',
    'formatPostTypeName',
    'getCustomPostTypeItems',
    'removeCondition',
    'cloneCondition',
    'addCondition'
  ])
  const labels_texts = HTPMM.adminSettings.labels_texts;
  const handleProFeatureSelect = (field, value) => {
    emit('handleProFeatureSelect', field, value)
  }
  
  const handleUriTypeChange = () => {
    emit('handleUriTypeChange')
  }
  
  const handlePostTypesChange = (selectedTypes) => {
    emit('handlePostTypesChange', selectedTypes)
  }
  
  const openProModal = () => {
    emit('openProModal')
  }
  
  const formatPostTypeName = (postType) => {
    return emit('formatPostTypeName', postType)
  }
  
  const getCustomPostTypeItems = (postType) => {
    return emit('getCustomPostTypeItems', postType)
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
  
  .el-checkbox-group {
    flex-wrap: wrap;
  }
  
  .el-checkbox {
    margin-right: 0;
    height: auto;
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
    
    .info-icon {
      position: absolute;
      right: 0;
      top: 0;
      color: #409EFF;
      font-size: 18px;
      cursor: help;
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