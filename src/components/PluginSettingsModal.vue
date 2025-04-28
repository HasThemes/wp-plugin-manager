<template>
  <el-dialog
    v-model="dialogVisible"
    :title="plugin?.name + ' Settings'"
    width="600px"
    class="plugin-settings-modal"
    destroy-on-close
  >
    <el-form>
      <el-form-item label="Disable This Plugin:">
        <el-switch v-model="pluginSettings.enable_deactivation" active-value="yes" inactive-value="no" />
      </el-form-item>

        <el-form-item label="Disable Plugin on:" v-show="pluginSettings.enable_deactivation === 'yes'">
              <el-select v-model="pluginSettings.device_type" class="w-full">
                <el-option label="Desktop + Tablet" value="desktop_plus_tablet" />
                <el-option label="Desktop" value="desktop" />
                <el-option label="Tablet" value="tablet" />
                <el-option label="Mobile" value="mobile" />
                <el-option label="Tablet + Mobile" value="tablet_plus_mobile" />
                <el-option label="All Devices" value="all" />
              </el-select>
              <div class="field-desc">Select the device(s) where this plugin should be disabled.</div>
            </el-form-item>

            <el-form-item label="Action:" v-show="pluginSettings.enable_deactivation === 'yes'">
              <el-select v-model="pluginSettings.condition_type" class="w-full">
                <el-option label="Disable on Selected Pages" value="disable_on_selected" />
                <el-option label="Enable on Selected Pages" value="enable_on_selected" />
              </el-select>
              <div class="field-desc">Disable on Selected Pages refers to the pages where the plugin will be disabled and enabled elsewhere.</div>
            </el-form-item>

            <el-form-item label="Page Type:" v-show="pluginSettings.enable_deactivation === 'yes'">
              <el-select v-model="pluginSettings.uri_type" class="w-full">
                <el-option label="Page" value="page" />
                <el-option label="Post" value="post" />
                <el-option label="Page & Post" value="page_post" />
                <el-option label="Post, Pages & Custom Post Type" value="page_post_cpt" />
                <el-option label="Custom" value="custom" />
              </el-select>
              <div class="field-desc">Choose the types of pages. "Custom" allows you to specify pages matching a particular URI pattern.</div>
            </el-form-item>

            <!-- Post Types Selection -->
            <el-form-item 
              label="Select Post Types:" 
              v-show="pluginSettings.enable_deactivation === 'yes' && pluginSettings.uri_type === 'page_post_cpt'"
            >
              <el-checkbox-group v-model="pluginSettings.post_types">
                <el-checkbox v-for="type in postTypes" :key="type" :label="type">{{ type }}</el-checkbox>
              </el-checkbox-group>
            </el-form-item>

            <!-- Pages Selection -->
            <el-form-item 
              label="Select Pages:" 
              v-show="pluginSettings.enable_deactivation === 'yes' && ['page', 'page_post', 'page_post_cpt'].includes(pluginSettings.uri_type)"
            >
              <el-select v-model="pluginSettings.pages" multiple filterable class="w-full">
                <el-option label="All Pages" value="all_pages" />
                <el-option v-for="page in pages" :key="page.id" :label="page.title" :value="page.id" />
              </el-select>
            </el-form-item>

            <!-- Custom URI Conditions -->
            <template v-if="pluginSettings.enable_deactivation === 'yes' && pluginSettings.uri_type === 'custom'">
              <div v-for="(condition, index) in pluginSettings.condition_list.name" :key="index" class="uri-condition">
                <el-select v-model="pluginSettings.condition_list.name[index]" class="condition-type">
                  <el-option label="URI Equals" value="uri_equals" />
                  <el-option label="URI Contains" value="uri_contains" />
                  <el-option label="URI Starts With" value="uri_starts_with" />
                </el-select>
                <el-input 
                  v-model="pluginSettings.condition_list.value[index]" 
                  placeholder="e.g. /contact-us or leave blank for homepage"
                  class="condition-value"
                />
                <div class="condition-actions">
                  <el-button type="danger" circle @click="removeCondition(index)">
                    <el-icon><Delete /></el-icon>
                  </el-button>
                  <el-button type="primary" circle @click="cloneCondition(index)">
                    <el-icon><CopyDocument /></el-icon>
                  </el-button>
                </div>
              </div>
              <el-button type="primary" plain @click="addCondition" class="mt-3">
                <el-icon><Plus /></el-icon> Add Condition
              </el-button>
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
import { ref, defineProps, defineEmits, watch, computed } from 'vue'
import { ElMessage } from 'element-plus'

const props = defineProps({
  visible: Boolean,
  plugin: Object
})

const emit = defineEmits(['update:visible', 'save'])

const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const pluginSettings = ref({
  enable_deactivation: 'no',
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
})

watch(() => props.visible, (newVal) => {
  if (newVal && props.plugin) {
    // Load plugin settings when modal opens
    loadPluginSettings(props.plugin)
  }
})

const customPostTypes = ref([])
const pages = ref([])
const posts = ref([])

const showPageSelect = computed(() => {
  return ['page', 'page_post', 'page_post_cpt'].includes(settings.value.uri_type) &&
    (settings.value.uri_type !== 'page_post_cpt' || settings.value.post_types.includes('page'))
})

const showPostSelect = computed(() => {
  return ['post', 'page_post', 'page_post_cpt'].includes(settings.value.uri_type) &&
    (settings.value.uri_type !== 'page_post_cpt' || settings.value.post_types.includes('post'))
})

const loadPluginSettings = async (plugin) => {
  try {
    // TODO: Load actual plugin settings from backend
    pluginSettings.value = {
      enable_deactivation: !plugin.active ? 'yes' : 'no',
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

    // Load custom post types
    customPostTypes.value = ['portfolio', 'product'] // TODO: Load from backend

    // Load pages
    pages.value = [
      { id: 1, title: 'Home', url: '/home' },
      { id: 2, title: 'About', url: '/about' },
      { id: 3, title: 'Contact', url: '/contact' }
    ] // TODO: Load from backend

    // Load posts
    posts.value = [
      { id: 1, title: 'First Post', url: '/posts/1' },
      { id: 2, title: 'Second Post', url: '/posts/2' },
      { id: 3, title: 'Third Post', url: '/posts/3' }
    ] // TODO: Load from backend
  } catch (error) {
    ElMessage.error('Failed to load plugin settings')
  }
}

const saveSettings = async () => {
  try {
    // TODO: Save settings to backend
    await savePluginSettingsToBackend({
      pluginId: props.plugin.id,
      ...settings.value
    })
    ElMessage.success('Settings saved successfully')
    dialogVisible.value = false
    emit('save')
  } catch (error) {
    ElMessage.error('Failed to save settings')
  }
}

const savePluginSettingsToBackend = async (settings) => {
  // TODO: Implement actual API call
  console.log('Saving settings:', settings)
  return new Promise(resolve => setTimeout(resolve, 500))
}
</script>

<style lang="scss" scoped>
.plugin-settings-modal {
  :deep(.el-dialog__body) {
    padding: 0;
  }
}

.plugin-settings {
  padding: 24px;

  .settings-group {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .settings-row {
    display: flex;
    flex-direction: column;
    gap: 8px;

    label {
      font-size: 13px;
      font-weight: 500;
      color: #374151;
    }

    .help-text {
      font-size: 12px;
      color: #6b7280;
      margin-top: 4px;
    }

    .checkbox-group {
      display: flex;
      gap: 16px;
      margin-top: 4px;
    }
  }
}

.w-full {
  width: 100%;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
}
</style>
