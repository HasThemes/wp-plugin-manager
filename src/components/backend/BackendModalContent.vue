<template>
  <!-- Condition Type Selector -->
  <div class="form-field">
    <label>{{ modalSettingsFields?.action?.label }} <span v-if="modalSettingsFields?.device_types?.proBadge" class="pro-badge">{{proLabel}}</span></label>
    <el-select v-model="pluginSettings.condition_type" class="w-full" @change="(value) => handleProFeatureSelect('action', value)">
      <el-option v-for="(label, value) in modalSettingsFields?.action?.options" :key="value" :label="label + (modalSettingsFields?.action?.pro?.includes(value) ? ' (' + proLabel + ')' : '')" :value="value" :disabled="modalSettingsFields?.action?.pro?.includes(value)" />
    </el-select>
    <div class="field-desc">{{ modalSettingsFields?.action?.description }}</div>
  </div>
  <!-- Pages Selection -->
  <div 
    class="form-field">
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
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Delete, Plus, CopyDocument, InfoFilled } from '@element-plus/icons-vue'
import { usePluginStore } from '../store/plugins'
import ProModal from './ProModal.vue'
import ModalSettingsSkeleton from '../skeleton/ModalSettingsSkeleton.vue'

const props = defineProps({
  visible: Boolean,
  plugin: Object
})

const emit = defineEmits(['update:visible', 'save'])
const store = usePluginStore()

const activeTab = ref('frontend')

const availablePlugins = ref([]) // to be used for conflict selection

const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

// Loading states
const loading = ref(false)
const saving = ref(false)
const loadingPages = ref(false)
const loadingPosts = ref(false)
const loadingCustomPosts = reactive({})

const proModal = ref(null);
const proLabel = ref(HTPMM?.buttontxt?.pro ? HTPMM?.buttontxt?.pro : 'PRO');

const handleProFeatureSelect = (field, value) => {
  const fieldSettings = modalSettingsFields[field];
  
  if (!isPro && fieldSettings?.pro?.includes(value)) {
    // Reset to default value based on field type
    switch(field) {
      case 'device_types':
        pluginSettings.device_type = 'all';
        break;
      case 'action':
        pluginSettings.condition_type = 'disable_on_selected';
        break;
      case 'page_types':
        pluginSettings.uri_type = 'page';
        break;
    }
    
    // Show pro modal
    proModal.value?.show();
  }
}
const openProModal = () => {
  proModal.value?.show();
}

const modalSettingsFields = HTPMM.adminSettings.modal_settings_fields
const isPro = HTPMM.adminSettings.is_pro
const labels_texts = HTPMM.adminSettings.labels_texts
// Default settings structure matching PHP - default to disabled
const pluginSettings = ref({
  enable_deactivation: 'yes', // Default to 'yes' (disabled)
  device_type: 'all',
  condition_type: 'disable_on_selected',
  uri_type: 'page',
  post_types: [],
  posts: [],
  pages: [],
  condition_list: {
    name: ['uri_equals'],
    value: [''],
  }
})

// Post types from API
const availablePostTypes = computed(() => store.postTypes)
const selectedAllPostTypesKeys = computed(() => {
  return ['page', 'post'].concat(store.allSettings.htpm_enabled_post_types);
});
const filteredPostTypes = computed(() =>availablePostTypes.value.filter(item => selectedAllPostTypesKeys.value?.includes(item.name))
);
const selectedCustomPostTypes = computed(() => {
  return pluginSettings.value.post_types.filter(type => !['page', 'post'].includes(type))
})

// Pages and posts data from store
const pages = computed(() => store.pages)
const posts = computed(() => store.posts)
// Load data when the component is mounted
onMounted(async () => {
  if (props.plugin) {
    await loadData()
  }
})

// Watch for changes in either plugin or visibility
watch(
  () => ({ plugin: props.plugin, visible: props.visible }),
  async (newVal) => {
    if (newVal.plugin && newVal.visible) {
      await loadData()
    }
  },
  { immediate: true }
)

// Load all required data
const loadData = async () => {
  loading.value = true
  try {
    // Load plugin settings first
    await loadPluginSettings()
    
    // Load necessary data based on the loaded settings
    loadingPages.value = true
    loadingPosts.value = true
    
    await Promise.all([
      store.fetchPages(),
      store.fetchPosts(),
      store.fetchPostTypes()
    ])
    
    loadingPages.value = false
    loadingPosts.value = false
    
    // Load custom post type data
    if (pluginSettings.value.uri_type === 'page_post_cpt') {
      await loadCustomPostTypeData()
    }
  } catch (error) {
    console.error('Error loading data:', error)
    ElMessage.error('Failed to load plugin settings')
  } finally {
    loading.value = false
  }
}

// Load plugin settings
const loadPluginSettings = async () => {
  try {
    if (!props.plugin) return
    
    const settings = await store.fetchPluginSettings(props.plugin.id)
    
    if (settings) {
      // Ensure we have proper structure
      const defaultSettings = {
        // We keep the enable_deactivation setting to maintain compatibility
        // but it will be controlled by the plugin list switcher
        // Default to 'yes' (disabled) unless explicitly set to enabled
        enable_deactivation: props.plugin.enable_deactivation ? 'yes' : 'no',
        device_type: 'all',
        condition_type: 'disable_on_selected',
        uri_type: 'page',
        post_types: ['page', 'post'],
        posts: [],
        pages: [],
        condition_list: {
          name: ['uri_equals'],
          value: [''],
        }
      }
      
      // Merge with default settings to ensure all properties exist
      pluginSettings.value = { ...defaultSettings, ...settings }
      
      // Initialize post_types array if it doesn't exist
      if (!pluginSettings.value.post_types) {
        pluginSettings.value.post_types = ['page', 'post']
      }
      
      // Initialize condition_list if needed
      if (!pluginSettings.value.condition_list) {
        pluginSettings.value.condition_list = {
          name: ['uri_equals'],
          value: [''],
        }
      }
    }
  } catch (error) {
    console.error('Error loading plugin settings:', error)
    ElMessage.error('Failed to load plugin settings')
    throw error
  }
}

// Load custom post type data
const loadCustomPostTypeData = async () => {
  const customTypes = selectedCustomPostTypes.value
  
  for (const type of customTypes) {
    loadingCustomPosts[type] = true
    
    // Initialize array for this post type if needed
    if (!pluginSettings.value[type + 's']) {
      pluginSettings.value[type + 's'] = []
    }
    
    try {
      await store.fetchCustomPostTypeItems(type)
    } catch (error) {
      console.error(`Error loading ${type} items:`, error)
    } finally {
      loadingCustomPosts[type] = false
    }
  }
}

// Handle URI type change
const handleUriTypeChange = async (value) => {
  // If changing to page_post_cpt, ensure post_types are properly set
  if (value === 'page_post_cpt') {
    // Make sure we have at least page and post selected
    if (!pluginSettings.value.post_types.includes('page')) {
      pluginSettings.value.post_types.push('page')
    }
    if (!pluginSettings.value.post_types.includes('post')) {
      pluginSettings.value.post_types.push('post')
    }
    
    // Load custom post type data
    await loadCustomPostTypeData()
  }
}

// Handle post types selection change
const handlePostTypesChange = async (selectedTypes) => {
  // Load data for newly selected post types
  const newCustomTypes = selectedCustomPostTypes.value
  
  for (const type of newCustomTypes) {
    if (!store.customPostTypeItems[type]) {
      loadingCustomPosts[type] = true
      
      // Initialize array for this post type if needed
      if (!pluginSettings.value[type + 's']) {
        pluginSettings.value[type + 's'] = []
      }
      
      try {
        await store.fetchCustomPostTypeItems(type)
      } catch (error) {
        console.error(`Error loading ${type} items:`, error)
      } finally {
        loadingCustomPosts[type] = false
      }
    }
  }
}

// Format post type name for better display
const formatPostTypeName = (postType) => {
  // First try to find in available post types
  const postTypeObj = availablePostTypes.value.find(pt => pt.name === postType)
  if (postTypeObj) {
    return postTypeObj.label
  }
  
  // Fallback to formatting the string
  return postType
    .replace(/_/g, ' ')
    .replace(/\b\w/g, letter => letter.toUpperCase())
}

// Get custom post type items for a specific post type
const getCustomPostTypeItems = (postType) => {
  return store.customPostTypeItems[postType] || []
}

// Add new URI condition
const addCondition = () => {
  if (!isPro) {
    proModal.value?.show();
    return;
  }
  pluginSettings.value.condition_list.name.push('uri_equals')
  pluginSettings.value.condition_list.value.push('')
}

// Remove a URI condition
const removeCondition = (index) => {
  if (pluginSettings.value.condition_list.name.length <= 1) return
  
  pluginSettings.value.condition_list.name.splice(index, 1)
  pluginSettings.value.condition_list.value.splice(index, 1)
}

// Clone a URI condition
const cloneCondition = (index) => {
  const name = pluginSettings.value.condition_list.name[index]
  const value = pluginSettings.value.condition_list.value[index]
  
  pluginSettings.value.condition_list.name.splice(index + 1, 0, name)
  pluginSettings.value.condition_list.value.splice(index + 1, 0, value)
}

// Save settings and close modal
const saveSettings = async () => {
  saving.value = true
  try {
    
    // First make sure all required arrays are properly initialized
    if (!Array.isArray(pluginSettings.value.pages)) {
      pluginSettings.value.pages = []
    }
    
    if (!Array.isArray(pluginSettings.value.posts)) {
      pluginSettings.value.posts = []
    }
    
    if (!Array.isArray(pluginSettings.value.post_types)) {
      pluginSettings.value.post_types = ['page', 'post']
    }
    
    if (!pluginSettings.value.condition_list) {
      pluginSettings.value.condition_list = {
        name: ['uri_equals'],
        value: [''],
      }
    }
    
    // Prepare data for API - create a full, deep copy
    const settingsToSave = JSON.parse(JSON.stringify(pluginSettings.value))
    
    // Since we've removed the switch in the modal, ensure enable_deactivation
    // is set based on the plugin's state in the plugin list
    settingsToSave.enable_deactivation = props.plugin?.enable_deactivation ? 'yes' : 'no'
    
    // Save settings via store and get updated settings
    const updatedSettings = await store.updatePluginSettings(props.plugin.id, settingsToSave)
  
    // Emit the save event with settings and plugin data
    emit('save', {
      plugin: props.plugin,
      settings: updatedSettings || settingsToSave
    })
    
    // Close the dialog
    dialogVisible.value = false

  } catch (error) {
    console.error('Error saving settings:', error)
    ElMessage.error('Failed to save settings')
  } finally {
    saving.value = false
  }
}
</script>

<style lang="scss" scoped>
.plugin-settings-modal {
  .modern-tabs {
    :deep(.el-tabs__header) {
      margin-bottom: 25px;
      border-bottom: 1px solid #dee2e6;

      .el-tabs__nav-wrap::after {
        display: none;
      }

      .el-tabs__nav {
        border: none;
      }

      .el-tabs__item {
        position: relative;
        height: 36px;
        line-height: 36px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 500;
        color: #666;
        border: none;
        margin-right: 4px;
        transition: all 0.2s ease;

        &:hover {
          color: #0d6efd;
          background: rgba(13, 110, 253, 0.04);
        }

        &.is-active {
          color: #0d6efd;
          font-weight: 600;
          background: rgba(13, 110, 253, 0.08);
          border-radius: 4px 4px 0 0;

          &::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: #0d6efd;
          }
        }
      }
    }
  }

  :deep(.el-dialog__header) {
    margin: 0;
    padding: 20px 20px 10px;
    border-bottom: 1px solid #eee;
  }

  :deep(.el-dialog__body) {
    padding: 20px;
  }

  :deep(.el-dialog__footer) {
    padding: 10px 20px;
    border-top: 1px solid #eee;
  }
}
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
.el-checkbox-group{
  flex-wrap: wrap;
}
.el-checkbox{
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
.add-condition:hover{
  background-color: rgb(121.3,187.1,255);
  cursor: pointer;
}
.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>