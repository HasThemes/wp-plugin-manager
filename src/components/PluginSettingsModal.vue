// PluginSettingsModal.vue
<template>
  <el-dialog
    v-model="dialogVisible"
    :title="plugin?.name + ' Settings'"
    width="650px"
    class="plugin-settings-modal"
    destroy-on-close
  >
    <el-form label-position="top" v-loading="loading">
      <!-- Configuration settings for the plugin - Always visible now -->
      <div class="form-field">
        <label>Disable Plugin on:</label>
        <el-select v-model="pluginSettings.device_type" class="w-full">
          <el-option label="All Devices" value="all" />
          <el-option label="Desktop" value="desktop" />
          <el-option label="Tablet" value="tablet" />
          <el-option label="Mobile" value="mobile" />
          <el-option label="Desktop + Tablet" value="desktop_plus_tablet" />
          <el-option label="Tablet + Mobile" value="tablet_plus_mobile" />
        </el-select>
        <div class="field-desc">Select the device(s) where this plugin should be disabled.</div>
      </div>

      <!-- Condition Type Selector -->
      <div class="form-field">
        <label>Action:</label>
        <el-select v-model="pluginSettings.condition_type" class="w-full">
          <el-option label="Disable on Selected Pages" value="disable_on_selected" />
          <el-option label="Enable on Selected Pages" value="enable_on_selected" />
        </el-select>
        <div class="field-desc">Disable on Selected Pages refers to the pages where the plugin will be disabled and enabled elsewhere.</div>
      </div>

      <!-- URI Type Selector -->
      <div class="form-field">
        <label>Page Type:</label>
        <el-select v-model="pluginSettings.uri_type" class="w-full" @change="handleUriTypeChange">
          <el-option label="Page" value="page" />
          <el-option label="Post" value="post" />
          <el-option label="Page & Post" value="page_post" />
          <el-option label="Post, Pages & Custom Post Type" value="page_post_cpt" />
          <el-option label="Custom" value="custom" />
        </el-select>
        <div class="field-desc">Choose the types of pages. "Custom" allows you to specify pages matching a particular URI pattern.</div>
        <el-tooltip
          v-if="pluginSettings.uri_type === 'page_post_cpt'"
          content="If you wish to select custom posts, please choose the custom post types below"
          placement="top"
        >
          <el-icon class="info-icon"><InfoFilled /></el-icon>
        </el-tooltip>
      </div>

      <!-- Post Types Selection -->
      <div class="form-field" v-if="pluginSettings.uri_type === 'page_post_cpt'">
        <label>Select Post Types:</label>
        <el-checkbox-group v-model="pluginSettings.post_types" @change="handlePostTypesChange" style="display: flex;gap: 10px;">
          <el-checkbox v-for="postType in availablePostTypes" :key="postType.name" :label="postType.name">
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
        <label>Select Pages:</label>
        <el-select v-model="pluginSettings.pages" multiple filterable class="w-full" :loading="loadingPages">
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
        <label>Select Posts:</label>
        <el-select v-model="pluginSettings.posts" multiple filterable class="w-full" :loading="loadingPosts">
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
          <label>Select {{ formatPostTypeName(postType) }}s:</label>
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
          <label>URI Conditions:</label>
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
              <el-button type="primary" circle size="small" @click="cloneCondition(index)">
                <el-icon><CopyDocument /></el-icon>
              </el-button>
            </div>
          </div>
          <el-button type="primary" plain size="small" @click="addCondition" class="mt-3 add-condition" color="#fff">
            <el-icon><Plus /></el-icon> Add Condition
          </el-button>
          <div class="field-desc">E.g. You can use 'contact-us' on URLs like https://example.com/contact-us or leave it blank for the homepage.</div>
        </div>
      </template>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="dialogVisible = false">Cancel</el-button>
        <el-button type="primary" @click="saveSettings" :loading="saving">Save & Enable</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Delete, Plus, CopyDocument, InfoFilled } from '@element-plus/icons-vue'
import { usePluginStore } from '../store/plugins'

const props = defineProps({
  visible: Boolean,
  plugin: Object
})

const emit = defineEmits(['update:visible', 'save'])
const store = usePluginStore()

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

// Watch for changes in the plugin prop
watch(() => props.plugin, async (newPlugin) => {
  if (newPlugin) {
    await loadData()
  }
}, { immediate: true })

// Watch for changes in the visible prop
watch(() => props.visible, async (newVisible) => {
  if (newVisible && props.plugin) {
    await loadData()
  }
})

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
    
    // Load custom post type items if needed
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
    // Debug original settings
    console.log('Original settings before save:', JSON.parse(JSON.stringify(pluginSettings.value)))
    
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
    
    // Save settings via store
     await store.updatePluginSettings(props.plugin.id, settingsToSave)
  
    // Emit the save event with settings and plugin data
    emit('save', {
      plugin: props.plugin,
      settings: settingsToSave
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
.el-checkbox-group{
  flex-wrap: wrap;
}
.el-checkbox{
  margin-right: 0;
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