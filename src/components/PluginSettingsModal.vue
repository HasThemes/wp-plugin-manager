<template>
  <el-dialog
    v-model="dialogVisible"
    :title="plugin?.name + ' Settings'"
    width="650px"
    class="plugin-settings-modal"
    destroy-on-close
  >
    <el-form label-position="top">
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
          <el-select v-model="pluginSettings.uri_type" class="w-full">
            <el-option label="Page" value="page" />
            <el-option label="Post" value="post" />
            <el-option label="Page & Post" value="page_post" />
            <el-option label="Post, Pages & Custom Post Type" value="page_post_cpt" />
            <el-option label="Custom" value="custom" />
          </el-select>
          <div class="field-desc">Choose the types of pages. "Custom" allows you to specify pages matching a particular URI pattern.</div>
          <div class="field-info" v-if="pluginSettings.uri_type === 'page_post_cpt'">
            <el-tooltip
              content="If you wish to select custom posts, please choose the custom post types below"
              placement="top"
            >
              <el-icon class="info-icon"><InfoFilled /></el-icon>
            </el-tooltip>
          </div>
        </div>

        <!-- Post Types Selection -->
        <div class="form-field" v-if="pluginSettings.uri_type === 'page_post_cpt'">
          <label>Select Post Types:</label>
          <el-checkbox-group v-model="pluginSettings.post_types" style="display: flex;gap: 10px;">
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
          <el-select v-model="pluginSettings.pages" multiple filterable class="w-full">
            <el-option label="All Pages" value="all_pages,all_pages" />
            <el-option v-for="page in pages" :key="page.id" :label="page.title" :value="page.id + ',' + page.url" />
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

        <!-- Custom Post Type Selections -->
        <template v-if="pluginSettings.uri_type === 'page_post_cpt'">
          <div 
            v-for="postType in customPostTypes" 
            :key="postType"
            class="form-field" 
            v-show="pluginSettings.post_types.includes(postType)"
          >
            <label>Select {{ formatPostTypeName(postType) }}s:</label>
            <el-select v-model="pluginSettings[postType + 's']" multiple filterable class="w-full">
              <el-option :label="'All ' + formatPostTypeName(postType) + 's'" :value="'all_' + postType + 's,all_' + postType + 's'" />
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
                placeholder="e.g. contact-us or leave blank for homepage"
                class="condition-value"
              />
              <div class="condition-actions">
                <el-button type="danger" circle size="small" @click="removeCondition(index)" :disabled="pluginSettings.condition_list.name.length <= 1">
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
        <el-button type="primary" @click="saveSettings">Save</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed, onMounted } from 'vue'
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

// Default settings structure matching PHP
const pluginSettings = ref({
  enable_deactivation: 'no',
  device_type: 'all',
  condition_type: 'disable_on_selected',
  uri_type: 'page',
  post_types: ['page', 'post'],
  posts: [],
  pages: [],
  // Initialize custom post type arrays
  // These will be set dynamically when loading settings
  condition_list: {
    name: ['uri_equals'],
    value: [''],
  }
})

// Available post types from the PHP code
const availablePostTypes = ref(['page', 'post'])
const customPostTypes = ref([])

// Pages and posts data
const pages = ref([])
const posts = ref([])
const customPostTypeItems = ref({})

// Load data when the component is mounted
onMounted(() => {
  // In a real application, these would be loaded from an API
  loadPostTypes()
})

watch(() => props.visible, (newVal) => {
  if (newVal && props.plugin) {
    // Load plugin settings when modal opens
    loadPluginSettings()
  }
})

const loadPostTypes = async () => {
  try {
    // In a real app, this would be loaded from WordPress API
    // For now, we'll use the static data
    availablePostTypes.value = ['page', 'post', 'product', 'portfolio', 'testimonial']
    
    // Filter out page and post to get custom post types
    customPostTypes.value = availablePostTypes.value.filter(type => !['page', 'post'].includes(type))
    
    // Initialize empty arrays for each custom post type
    customPostTypes.value.forEach(type => {
      pluginSettings.value[type + 's'] = []
      
      // Sample data for each custom post type
      customPostTypeItems.value[type] = [
        { id: 1, title: `${type} 1`, url: `/${type}/1` },
        { id: 2, title: `${type} 2`, url: `/${type}/2` },
        { id: 3, title: `${type} 3`, url: `/${type}/3` }
      ]
    })
  } catch (error) {
    ElMessage.error('Failed to load post types')
  }
}

const loadPluginSettings = async () => {
  try {
    // In a real app, this would load settings from the WordPress API
    // For now, we'll set some sample data
    
    // Reset settings first
    pluginSettings.value = {
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
    
    // Initialize custom post type arrays
    customPostTypes.value.forEach(type => {
      pluginSettings.value[type + 's'] = []
    })
    
    // Load sample pages and posts
    pages.value = [
      { id: 1, title: 'Home', url: '/home' },
      { id: 2, title: 'About', url: '/about' },
      { id: 3, title: 'Contact', url: '/contact' },
      { id: 4, title: 'Services', url: '/services' },
      { id: 5, title: 'Portfolio', url: '/portfolio' }
    ]

    posts.value = [
      { id: 1, title: 'First Post', url: '/posts/1' },
      { id: 2, title: 'Second Post', url: '/posts/2' },
      { id: 3, title: 'Third Post', url: '/posts/3' },
      { id: 4, title: 'Fourth Post', url: '/posts/4' },
      { id: 5, title: 'Fifth Post', url: '/posts/5' }
    ]
    
    // For the demo, add some sample data if this is the first plugin
    if (props.plugin.id === 1) {
      pluginSettings.value.enable_deactivation = 'yes'
      pluginSettings.value.uri_type = 'page_post'
      pluginSettings.value.pages = ['1,/home', '2,/about']
      pluginSettings.value.posts = ['all_posts,all_posts']
    }
    
    // Sample data for the second plugin (custom URI)
    if (props.plugin.id === 2) {
      pluginSettings.value.enable_deactivation = 'yes'
      pluginSettings.value.uri_type = 'custom'
      pluginSettings.value.condition_list.name = ['uri_equals', 'uri_contains']
      pluginSettings.value.condition_list.value = ['contact', 'product']
    }
    
    // Sample data for the third plugin (custom post types)
    if (props.plugin.id === 3) {
      pluginSettings.value.enable_deactivation = 'yes'
      pluginSettings.value.uri_type = 'page_post_cpt'
      pluginSettings.value.post_types = ['page', 'post', 'product']
      pluginSettings.value.pages = ['all_pages,all_pages']
      pluginSettings.value.products = ['1,/product/1']
    }
    
  } catch (error) {
    ElMessage.error('Failed to load plugin settings')
  }
}

const formatPostTypeName = (postType) => {
  // Format post type name (e.g., "my_post_type" to "My Post Type")
  return postType
    .replace(/_/g, ' ')
    .replace(/\b\w/g, l => l.toUpperCase())
}

const getCustomPostTypeItems = (postType) => {
  return customPostTypeItems.value[postType] || []
}

const saveSettings = async () => {
  try {
    // Here we would normally send the settings to the server
    console.log('Saving settings:', pluginSettings.value)
    
    // Update plugin active state based on enable_deactivation setting
    props.plugin.active = pluginSettings.value.enable_deactivation !== 'yes'
    
    ElMessage.success('Settings saved successfully')
    dialogVisible.value = false
    emit('save', { plugin: props.plugin, settings: pluginSettings.value })
  } catch (error) {
    ElMessage.error('Failed to save settings')
  }
}

const addCondition = () => {
  pluginSettings.value.condition_list.name.push('uri_equals')
  pluginSettings.value.condition_list.value.push('')
}

const removeCondition = (index) => {
  // Don't remove if it's the last condition
  if (pluginSettings.value.condition_list.name.length <= 1) return
  
  pluginSettings.value.condition_list.name.splice(index, 1)
  pluginSettings.value.condition_list.value.splice(index, 1)
}

const cloneCondition = (index) => {
  const name = pluginSettings.value.condition_list.name[index]
  const value = pluginSettings.value.condition_list.value[index]
  
  pluginSettings.value.condition_list.name.splice(index + 1, 0, name)
  pluginSettings.value.condition_list.value.splice(index + 1, 0, value)
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
  
  .field-info {
    position: absolute;
    right: 0;
    top: 0;
    color: #409EFF;
    
    .info-icon {
      font-size: 18px;
      cursor: help;
    }
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