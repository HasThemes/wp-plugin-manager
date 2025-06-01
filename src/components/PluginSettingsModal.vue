// PluginSettingsModal.vue
<template>
  <el-dialog
    v-model="dialogVisible"
    :title="plugin?.name + ' Settings'"
    width="650px"
    class="plugin-settings-modal"
    destroy-on-close
  >
    <ModalSettingsSkeleton v-if="loading" />
    <el-form label-position="top" v-else>
      <el-tabs v-model="activeTab" class="modern-tabs">
        <!-- Frontend Tab -->
        <el-tab-pane label="Frontend" name="frontend">
          <FrontendModalContent
            :plugin-settings="pluginSettings"
            :modal-settings-fields="modalSettingsFields"
            :labels-texts="labels_texts"
            :pro-label="proLabel"
            :is-pro="isPro"
            :filtered-post-types="filteredPostTypes"
            :selected-custom-post-types="selectedCustomPostTypes"
            :pages="pages"
            :posts="posts"
            :loading-pages="loadingPages"
            :loading-posts="loadingPosts"
            :loading-custom-posts="loadingCustomPosts"
            @handle-pro-feature-select="handleProFeatureSelect"
            @handle-uri-type-change="handleUriTypeChange"
            @handle-post-types-change="handlePostTypesChange"
            @open-pro-modal="openProModal"
            @format-post-type-name="formatPostTypeName"
            @get-custom-post-type-items="getCustomPostTypeItems"
            @remove-condition="removeCondition"
            @clone-condition="cloneCondition"
            @add-condition="addCondition"
          />
        </el-tab-pane>

        <!-- Backend Tab -->
        <el-tab-pane label="Backend" name="backend">
          <BackendModalContent
            :plugin-settings="pluginSettings"
            :modal-settings-fields="modalSettingsFields"
            :pro-label="proLabel"
            :is-pro="isPro"
            :pages="pages"
            :loading-pages="loadingPages"
            @handle-pro-feature-select="handleProFeatureSelect"
            @open-pro-modal="openProModal"
            @remove-condition="removeCondition"
            @clone-condition="cloneCondition"
            @add-condition="addCondition"
          />
        </el-tab-pane>

        <!-- Conflict Tab -->
        <el-tab-pane label="Conflict" name="conflict">
          <ConflictModalContent
            :pro-label="proLabel"
            :is-pro="isPro"
            :available-plugins="availablePlugins"
            @open-pro-modal="openProModal"
          />
        </el-tab-pane>

        <!-- Login Status Tab -->
        <el-tab-pane label="Login Status" name="login_status">
          <LoginStatusModalContent
            :pro-label="proLabel"
            :is-pro="isPro"
            @open-pro-modal="openProModal"
          />
        </el-tab-pane>
      </el-tabs>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="dialogVisible = false" :disabled="saving">
          {{labels_texts?.cancel}}
        </el-button>
        <el-button 
          type="primary" 
          @click="saveSettings" 
          :loading="saving"
          :disabled="saving"
        >
          {{pluginSettings.enable_deactivation == 'yes' ? 'Save' : labels_texts?.save_enable}}
        </el-button>
      </div>
    </template>
  </el-dialog>

  <!-- Pro Modal -->
  <ProModal ref="proModal" />
</template>

<script setup>
import { ref, defineProps, defineEmits, watch, computed, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { usePluginStore } from '../store/plugins'
import ProModal from './ProModal.vue'
import ModalSettingsSkeleton from '../skeleton/ModalSettingsSkeleton.vue'
import FrontendModalContent from './FrontendModalContent.vue'
import BackendModalContent from './BackendModalContent.vue'
import ConflictModalContent from './ConflictModalContent.vue'
import LoginStatusModalContent from './LoginStatusModalContent.vue'

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
  // Frontend settings
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
  },
  // Backend settings
  admin_scope: 'all_admin',
  backend_pages: [],
  backend_condition_list: {
    name: ['admin_page_equals'],
    value: [''],
  },
  backend_user_roles: []
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
      // Ensure we have proper structure including backend fields
      const defaultSettings = {
        // Frontend settings
        enable_deactivation: props.plugin.enable_deactivation ? 'yes' : 'no',
        frontend_status: props.plugin.enable_deactivation ? 'yes' : 'no',
        device_type: 'all',
        condition_type: 'disable_on_selected',
        uri_type: 'page',
        post_types: ['page', 'post'],
        posts: [],
        pages: [],
        condition_list: {
          name: ['uri_equals'],
          value: [''],
        },
        // Backend settings
        admin_scope: 'all_admin',
        backend_status: 'no',
        backend_pages: [],
        backend_condition_list: {
          name: ['admin_page_equals'],
          value: [''],
        },
        backend_user_roles: [],
        conflict_status: 'no',
        login_status: 'no',
      }
      
      // Merge with default settings to ensure all properties exist
      pluginSettings.value = { ...defaultSettings, ...settings }
      
      // Initialize arrays if they don't exist
      if (!pluginSettings.value.post_types) {
        pluginSettings.value.post_types = ['page', 'post']
      }
      
      if (!pluginSettings.value.condition_list) {
        pluginSettings.value.condition_list = {
          name: ['uri_equals'],
          value: [''],
        }
      }
      
      // Initialize backend fields if they don't exist
      if (!pluginSettings.value.admin_scope) {
        pluginSettings.value.admin_scope = 'all_admin'
      }
      
      if (!pluginSettings.value.backend_pages) {
        pluginSettings.value.backend_pages = []
      }
      
      if (!pluginSettings.value.backend_condition_list) {
        pluginSettings.value.backend_condition_list = {
          name: ['admin_page_equals'],
          value: [''],
        }
      }
      
      if (!pluginSettings.value.backend_user_roles) {
        pluginSettings.value.backend_user_roles = []
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
  saving.value = true // Set loading state immediately
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
    
    // Initialize backend fields if they don't exist
    if (!pluginSettings.value.admin_scope) {
      pluginSettings.value.admin_scope = 'all_admin'
    }
    
    if (!Array.isArray(pluginSettings.value.backend_pages)) {
      pluginSettings.value.backend_pages = []
    }
    
    if (!pluginSettings.value.backend_condition_list) {
      pluginSettings.value.backend_condition_list = {
        name: ['admin_page_equals'],
        value: [''],
      }
    }
    
    if (!Array.isArray(pluginSettings.value.backend_user_roles)) {
      pluginSettings.value.backend_user_roles = []
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
   // dialogVisible.value = false

  } catch (error) {
    console.error('Error saving settings:', error)
    ElMessage.error('Failed to save settings')
  } finally {
    saving.value = false // Always reset loading state
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

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>