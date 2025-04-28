<template>
  <el-dialog
    v-model="dialogVisible"
    :title="plugin?.name + ' Settings'"
    width="650px"
    class="plugin-settings-modal"
    destroy-on-close
  >
    <el-form label-position="top" v-loading="loading">
      <!-- Enable/Disable plugin toggle -->
      <div class="form-field">
        <el-switch
          v-model="pluginSettings.enable_deactivation"
          :active-value="'yes'"
          :inactive-value="'no'"
          class="disable-switch"
        />
        <label>Disable This Plugin</label>
      </div>

      <!-- Only show these settings if plugin is set to be disabled -->
      <template v-if="pluginSettings.enable_deactivation === 'yes'">
        <!-- Device Type Selector -->
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
          <el-checkbox-group v-model="pluginSettings.post_types" @change="handlePostTypesChange"  style="display: flex;gap: 10px;">
            <el-checkbox v-for="postType in availablePostTypes" :key="postType" :label="postType">
              {{ formatPostTypeName(postType) }}
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
          <!-- <el-select v-model="pluginSettings.pages" multiple filterable class="w-full">
            <el-option label="All Pages" value="all_pages,all_pages" />
            <el-option v-for="page in pages" :key="page.id" :label="page.title" :value="page.id + ',' + page.url" />
          </el-select> -->

          <el-select v-model="pluginSettings.pages" multiple filterable class="w-full">
            <el-option label="All Pages" value="all_pages,all_pages" />
            <el-option 
              v-for="page in store.pages" 
              :key="page.id" 
              :label="page.title" 
              :value="page.id + ',' + page.url" 
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
          <el-select v-model="pluginSettings.posts" multiple filterable class="w-full">
            <el-option label="All Posts" value="all_posts,all_posts" />
            <el-option v-for="post in posts" :key="post.id" :label="post.title" :value="post.id + ',' + post.url" />
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
                :label="'All ' + formatPostTypeName(postType) + 's'" 
                :value="'all_' + postType + 's,all_' + postType + 's'" 
              />
              <el-option 
                v-for="item in getCustomPostTypeItems(postType)" 
                :key="item.id" 
                :label="item.title" 
                :value="item.id + ',' + item.url" 
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
            <el-button type="primary" plain size="small" @click="addCondition" class="mt-3">
              <el-icon><Plus /></el-icon> Add Condition
            </el-button>
            <div class="field-desc">E.g. You can use 'contact-us' on URLs like https://example.com/contact-us or leave it blank for the homepage.</div>
          </div>
        </template>
      </template>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="dialogVisible = false">Cancel</el-button>
        <el-button type="primary" @click="saveSettings" :loading="saving">Save</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Delete, Plus, CopyDocument, InfoFilled } from '@element-plus/icons-vue'

const props = defineProps({
  visible: Boolean,
  plugin: Object
})

const emit = defineEmits(['update:visible', 'save'])

const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

// Loading states
const loading = ref(false)
const saving = ref(false)
const loadingCustomPosts = reactive({})

// Default settings structure matching PHP
const pluginSettings = ref({
  enable_deactivation: 'no',
  device_type: 'all',
  condition_type: 'disable_on_selected',
  uri_type: 'page',
  post_types: ['page', 'post'],
  posts: [],
  pages: [],
  // These will be set dynamically when loading settings
  condition_list: {
    name: ['uri_equals'],
    value: [''],
  }
})

// Available post types from the PHP code
const availablePostTypes = ref(['page', 'post', 'product', 'portfolio', 'testimonial'])
const selectedCustomPostTypes = computed(() => {
  return pluginSettings.value.post_types.filter(type => !['page', 'post'].includes(type))
})

// Pages and posts data
const pages = ref([])
const posts = ref([])
const customPostTypeItems = ref({})

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
    await Promise.all([
      loadPluginSettings(),
      store.fetchPages(),
      store.fetchPosts(),
      store.fetchPostTypes()
    ])
    
    // Get post types and load data for custom post types
    const customTypes = store.customPostTypes
    customTypes.forEach(async (type) => {
      await store.fetchCustomPostTypeItems(type)
    })
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    loading.value = false
  }
}

// Load plugin settings
const loadPluginSettings = async () => {
  try {
    // In a real application, this would fetch from an API
    // For demo purposes, we're using simulated data

    // Reset settings first
    const defaultSettings = {
      enable_deactivation: props.plugin.active ? 'no' : 'yes',
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
    
    // For the demo, add some sample data based on plugin ID
    if (props.plugin.id === 1) {
      defaultSettings.enable_deactivation = 'yes'
      defaultSettings.uri_type = 'page_post'
      defaultSettings.pages = ['1,/home', '2,/about']
      defaultSettings.posts = ['all_posts,all_posts']
    } else if (props.plugin.id === 2) {
      defaultSettings.enable_deactivation = 'yes'
      defaultSettings.uri_type = 'custom'
      defaultSettings.condition_list.name = ['uri_equals', 'uri_contains']
      defaultSettings.condition_list.value = ['contact', 'product']
    } else if (props.plugin.id === 3) {
      defaultSettings.enable_deactivation = 'yes'
      defaultSettings.uri_type = 'page_post_cpt'
      defaultSettings.post_types = ['page', 'post', 'product']
      defaultSettings.pages = ['all_pages,all_pages']
      defaultSettings.products = ['1,/product/1']
    }
    
    // Initialize custom post type arrays
    selectedCustomPostTypes.value.forEach(type => {
      if (!defaultSettings[type + 's']) {
        defaultSettings[type + 's'] = []
      }
    })
    
    pluginSettings.value = defaultSettings
    
  } catch (error) {
    console.error('Error loading plugin settings:', error)
    throw error
  }
}

// Load pages data
const loadPages = async () => {
  try {
    // In a real application, this would fetch from an API
    pages.value = [
      { id: 1, title: 'Home', url: '/home' },
      { id: 2, title: 'About', url: '/about' },
      { id: 3, title: 'Contact', url: '/contact' },
      { id: 4, title: 'Services', url: '/services' },
      { id: 5, title: 'Portfolio', url: '/portfolio' }
    ]
  } catch (error) {
    console.error('Error loading pages:', error)
    throw error
  }
}

// Load posts data
const loadPosts = async () => {
  try {
    // In a real application, this would fetch from an API
    posts.value = [
      { id: 1, title: 'First Post', url: '/posts/1' },
      { id: 2, title: 'Second Post', url: '/posts/2' },
      { id: 3, title: 'Third Post', url: '/posts/3' },
      { id: 4, title: 'Fourth Post', url: '/posts/4' },
      { id: 5, title: 'Fifth Post', url: '/posts/5' }
    ]
  } catch (error) {
    console.error('Error loading posts:', error)
    throw error
  }
}

// Load post types
const loadPostTypes = async () => {
  try {
    // In a real application, this would fetch from an API
    availablePostTypes.value = ['page', 'post', 'product', 'portfolio', 'testimonial']
    
    // Load sample data for each custom post type
    const customTypes = availablePostTypes.value.filter(type => !['page', 'post'].includes(type))
    customTypes.forEach(async (type) => {
      await loadCustomPostTypeItems(type)
    })
  } catch (error) {
    console.error('Error loading post types:', error)
    throw error
  }
}

// Load items for a specific custom post type
const loadCustomPostTypeItems = async (postType) => {
  loadingCustomPosts[postType] = true
  try {
    // Initialize the array if needed
    if (!pluginSettings.value[postType + 's']) {
      pluginSettings.value[postType + 's'] = []
    }
    
    // In a real application, this would fetch from an API
    customPostTypeItems.value[postType] = [
      { id: 1, title: `${formatPostTypeName(postType)} 1`, url: `/${postType}/1` },
      { id: 2, title: `${formatPostTypeName(postType)} 2`, url: `/${postType}/2` },
      { id: 3, title: `${formatPostTypeName(postType)} 3`, url: `/${postType}/3` }
    ]
  } catch (error) {
    console.error(`Error loading ${postType} items:`, error)
  } finally {
    loadingCustomPosts[postType] = false
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
    
    // Load custom post type items
    selectedCustomPostTypes.value.forEach(async (type) => {
      await loadCustomPostTypeItems(type)
    })
  }
}

// Handle post types selection change
const handlePostTypesChange = async (selectedTypes) => {
  // Ensure we're loading data for newly selected post types
  selectedCustomPostTypes.value.forEach(async (type) => {
    if (!customPostTypeItems.value[type]) {
      await loadCustomPostTypeItems(type)
    }
  })
}

// Format post type name for better display
const formatPostTypeName = (postType) => {
  return postType
    .replace(/_/g, ' ')
    .replace(/\b\w/g, letter => letter.toUpperCase())
}

// Get custom post type items for a specific post type
const getCustomPostTypeItems = (postType) => {
  return customPostTypeItems.value[postType] || []
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
    // In a real application, this would send data to the server
    console.log('Saving settings:', pluginSettings.value)
    
    // Simulate API delay
    await new Promise(resolve => setTimeout(resolve, 600))
    
    // Emit the save event with settings and plugin data
    emit('save', {
      plugin: props.plugin,
      settings: pluginSettings.value
    })
    
    // Close the dialog
    dialogVisible.value = false
    
    ElMessage.success('Settings saved successfully')
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
  
  .disable-switch {
    margin-right: 10px;
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

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>