<template>
  <div class="settings-page htpm-inner-page-wrapper">
    <SettingsPageSkeleton v-if="isLoading" />

    <div v-else class="settings-container">
      <!-- Post Types Settings Section -->
      <div class="settings-section mb-8">
        <h2 class="section-title">
          <el-icon><Document /></el-icon>
          {{ labels_texts?.post_types_settings }}
        </h2>
        
        <div class="section-content">
          <div class="form-group">
            <label class="setting-label"> {{ dashboardSettings.post_typs_settings.custom_post_types.label }} <el-tag v-if="dashboardSettings?.post_typs_settings?.custom_post_types?.proBadge" class="pro-badge" type="warning" size="small">{{ proLabel }}</el-tag></label>
            <div class="setting-control">
              <div class="selected-types">
                <span v-if="!dashboardSettings?.post_typs_settings?.custom_post_types?.isPro" v-for="type in filteredPostTypes" :key="type" class="type-tag">
                  {{ type }}
                  <el-icon class="remove-tag" @click="removePostType(type)"><Close /></el-icon>
                </span>
              </div>
              <select v-model="newPostType" class="form-select" @change="addPostType" @click="()=> dashboardSettings?.post_typs_settings?.custom_post_types?.isPro ? openProModal() : null" :disabled="dashboardSettings?.post_typs_settings?.custom_post_types?.isPro">
                <option value="">{{ labels_texts?.add_post_type }}</option>
                <option v-for="type in dashboardSettings?.post_typs_settings?.custom_post_types?.options" :key="type.name" :value="type.name" :disabled="dashboardSettings?.post_typs_settings?.custom_post_types?.isPro">
                  {{ type.label }}
                </option>
              </select>
            </div>
            <p class="setting-description">{{dashboardSettings?.post_typs_settings?.custom_post_types?.desc }}</p>
          </div>

          <div class="form-group">
            <label class="setting-label">{{ dashboardSettings.post_typs_settings.load_posts.label }} <el-tag v-if="!isPro" class="pro-badge" type="warning" size="small">{{ proLabel }}</el-tag></label>
            <div class="setting-control number-input-group">
              <button class="number-btn" @click="decrementPosts">−</button>
              <input type="number" v-model="settingsPagesSettings.htpm_load_posts" class="number-input" :min="dashboardSettings.post_typs_settings.load_posts.min" :max="dashboardSettings.post_typs_settings.load_posts.max" :disabled="dashboardSettings.post_typs_settings.load_posts.isPro">
              <button class="number-btn" @click="incrementPosts">+</button>
            </div>
            <p class="setting-description">{{ dashboardSettings.post_typs_settings.load_posts.desc }}</p>
          </div>

          <div class="info-note">
            <el-icon><InfoFilled /></el-icon>
            {{ labels_texts?.save_settings_note }}
          </div>
        </div>
      </div>
      <!-- Display Settings Section -->
      <div class="settings-section">
        <h2 class="section-title">
          <el-icon><View /></el-icon>
          {{ labels_texts?.display_settings }}
        </h2>
        
        <div class="section-content">
          <div class="form-group">
            <label class="setting-label">{{ labels_texts?.show_thumbnails }}</label>
            <div class="setting-control">
              <el-switch
                v-model="settingsPagesSettings.showThumbnails"
                class="custom-switch"
              />
            </div>
            <p class="setting-description">{{ labels_texts?.show_thumbnails_desc }}</p>
          </div>

          <div class="form-group">
            <label class="setting-label">{{ labels_texts?.items_per_page }}</label>
            <div class="setting-control">
              <select v-model="settingsPagesSettings.itemsPerPage" class="form-select">
                <option value="10">10 {{ labels_texts?.items }}</option>
                <option value="20">20 {{ labels_texts?.items }}</option>
                <option value="50">50 {{ labels_texts?.items }}</option>
                <option value="100">100 {{ labels_texts?.items }}</option>
              </select>
            </div>
            <p class="setting-description" v-if="labels_texts?.items_per_page_desc">{{ labels_texts?.items_per_page_desc }}</p>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="save-button-container">
        <el-button type="primary" @click="saveSettings" size="large" class="save-button" :loading="isSaving">
          <template #icon>
            <el-icon v-if="!isSaving"><Check /></el-icon>
          </template>
          {{ labels_texts?.save_settings }}
        </el-button>
      </div>
    </div>
  </div>

  <ProModal ref="proModal" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { View, Document, Close, InfoFilled, Check, Warning } from '@element-plus/icons-vue'
import { ElMessage, ElNotification } from 'element-plus'
import { usePluginStore } from '../store/plugins'
import ProModal from '../components/ProModal.vue'
import SettingsPageSkeleton from '../skeleton/SettingsPageSkeleton.vue'

const store = usePluginStore()
const labels_texts = HTPMM.adminSettings.labels_texts
const modalSettingsFields = HTPMM.adminSettings.modal_settings_fields
const isPro = HTPMM.adminSettings.is_pro
const dashboardSettings = HTPMM.adminSettings.dashboard_settings
const proModal = ref(null)
const proLabel = ref(HTPMM.buttontxt.pro);
// Helper function for string formatting
const sprintf = (str, ...args) => {
  return str.replace(/%d/g, () => args.shift())
}
// Display Settings
const settingsPagesSettings = ref(
  {
    postTypes: [],
    htpm_load_posts: 150,
    showThumbnails: true,
    itemsPerPage: 10
  }
)
const filteredPostTypes = computed(() => {
  return settingsPagesSettings.value.postTypes.filter(type => !['page', 'post'].includes(type))
})
const isLoading = ref(true)
const newPostType = ref('')
const availablePostTypes = ref([])

// Fetch available post types
const fetchPostTypes = async () => {
  try {
    const data = await store.fetchPostTypes()
    availablePostTypes.value = data.map(type => ({
      value: type.name,
      label: type.label
    }))
    availablePostTypes.value = availablePostTypes.value.filter(type => type.value !== 'page' && type.value !== 'post')
  } catch (error) {
    console.error('Error fetching post types:', error)
    ElNotification({
      title: 'Error',
      message: 'Failed to load post types. Please refresh the page or contact support.',
      type: 'error',
      duration: 5000,
      icon: Warning
    })
  }
}

// Load saved settings
const loadSavedSettings = async () => {
  isLoading.value = true
  try {
    if(store.plugins.length <=0){
      await store.fetchPlugins()
      }
   
    // First fetch post types
    //await fetchPostTypes()
    
    // Then fetch saved settings
    const savedSettings = store.allSettings;
    // Safely handle post types array
    settingsPagesSettings.value.postTypes = savedSettings?.htpm_enabled_post_types || ['page', 'post'];
    // settingsPagesSettings.value.postTypes = settingsPagesSettings.value.postTypes.filter(type => type !== 'page' && type !== 'post');
    // Handle other settings with default values
    settingsPagesSettings.value.htpm_load_posts = parseInt(savedSettings?.htpm_load_posts) || 150;
    settingsPagesSettings.value.showThumbnails = savedSettings?.showThumbnails ?? true;
    settingsPagesSettings.value.itemsPerPage = parseInt(savedSettings?.itemsPerPage) || 10;
      
  } catch (error) {
    console.error('Error loading settings:', error)
    ElNotification({
      title: 'Error',
      message: 'Failed to load settings. Please try again.',
      type: 'error',
      duration: 5000
    })
  } finally {
    isLoading.value = false
  }
}

// Load data on component mount
onMounted(async () => {
  await loadSavedSettings()
})

// Post type management methods
const addPostType = () => {
  if (dashboardSettings?.post_typs_settings?.custom_post_types?.isPro) {
    openProModal()
    return
  }
  if (newPostType.value && !settingsPagesSettings.value.postTypes.includes(newPostType.value)) {
    settingsPagesSettings.value.postTypes.push(newPostType.value)
    newPostType.value = ''
  }
}

const removePostType = (type) => {
  // Don't allow removing page or post
  if (type === 'page' || type === 'post') return
  
  const index = settingsPagesSettings.value.postTypes.indexOf(type)
  if (index > -1) {
    const newPostTypes = [...settingsPagesSettings.value.postTypes]
    newPostTypes.splice(index, 1)
    settingsPagesSettings.value.postTypes = newPostTypes
  }
}

// Number of posts increment/decrement
const incrementPosts = () => {
  if(dashboardSettings.post_typs_settings.load_posts.isPro){
    openProModal()
    return
  }
  if (settingsPagesSettings.value.htpm_load_posts < 1000) {
    settingsPagesSettings.value.htpm_load_posts = parseInt(settingsPagesSettings.value.htpm_load_posts) + 10
  }
}

const decrementPosts = () => {
  if(dashboardSettings.post_typs_settings.load_posts.isPro){
    openProModal()
    return
  }
  if (settingsPagesSettings.value.htpm_load_posts > 1) {
    settingsPagesSettings.value.htpm_load_posts = Math.max(1, parseInt(settingsPagesSettings.value.htpm_load_posts) - 10)
  }
}

const validateNumberOfPosts = () => {
  settingsPagesSettings.value.htpm_load_posts = Math.max(1, Math.min(1000, parseInt(settingsPagesSettings.value.htpm_load_posts) || 150))
}

const openProModal = () => {
  proModal.value?.show();
}

// Save settings
const isSaving = ref(false)

const saveSettings = async () => {
  try {
    isSaving.value = true
    // Validate values before saving
    validateNumberOfPosts()
    
    // Create settings object
    const settings = {
      postTypes: Array.isArray(settingsPagesSettings.value.postTypes) 
        ? settingsPagesSettings.value.postTypes.filter(type => type && typeof type === 'string')
        : ['page', 'post'],
      htpm_load_posts: parseInt(settingsPagesSettings.value.htpm_load_posts) || 150,
      showThumbnails: Boolean(settingsPagesSettings.value.showThumbnails),
      itemsPerPage: parseInt(settingsPagesSettings.value.itemsPerPage) || 10
    }
    
    const response = await store.updateDashboardSettings(settings)
    
    if (response?.success) {
      ElNotification({
        title: 'Success',
        message: 'Settings saved successfully',
        type: 'success'
      })
    } else {
      throw new Error(response?.message || 'Failed to save settings')
    }
  } catch (error) {
    console.error('Error saving settings:', error)
    ElNotification({
      title: 'Error',
      message: error.message || 'Failed to save settings. Please try again.',
      type: 'error',
      duration: 5000
    })
  } finally {
    isSaving.value = false
  }
}
</script>

<style scoped>
.pro-badge {
  vertical-align: middle;
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
.settings-container {
  width: 100%;
  max-width: 100%;
}
.settings-section {
  background: white;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  width: 100%;
}

.settings-section + .settings-section {
  margin-top: 2rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1rem;
  font-weight: 600;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
  margin: 0;
}

.section-content {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 2rem;
}

.form-group:last-child {
  margin-bottom: 0;
}

.setting-label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.setting-description {
  color: #6b7280;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

.setting-control {
  margin-top: 0.5rem;
}

.form:deep(.el-select) {
  width: 100%;
}

.form-select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  background-color: white;
}

.selected-types {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.type-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: #e5e7eb;
  border-radius: 4px;
  font-size: 0.875rem;
}

.remove-tag {
  cursor: pointer;
  width: 16px;
  height: 16px;
  color: #6b7280;
}

.remove-tag:hover {
  color: #ef4444;
}

.number-input-group {
  display: inline-flex;
  align-items: center;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  overflow: hidden;
}

.number-btn {
  padding: 0.5rem 0.75rem;
  background: #f3f4f6;
  border: none;
  cursor: pointer;
}

.number-btn:hover {
  background: #e5e7eb;
}

.number-input {
  width: 80px;
  text-align: center;
  border: none;
  border-left: 1px solid #d1d5db;
  border-right: 1px solid #d1d5db;
  padding: 0.5rem;
}

.info-note {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 1.5rem;
  padding: 1rem;
  background: #fff7ed;
  border: 1px solid #fdba74;
  border-radius: 4px;
  color: #c2410c;
  font-size: 0.875rem;
}

.save-button-container {
  display: flex;
  justify-content: flex-end;
  padding: 1rem;
  background: white;
  border-top: 1px solid #e5e7eb;
  margin-top: 2rem;
  position: sticky;
  bottom: 0;
  z-index: 10;
}

.save-button {
  min-width: 120px;
}

/* Element Plus overrides */
:deep(.el-switch) {
  --el-switch-on-color: #3b82f6;
}
</style>
