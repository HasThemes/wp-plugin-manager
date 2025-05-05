<template>
  <div class="settings-page htpm-inner-page-wrapper">
    <div class="settings-container">
      <!-- Post Types Settings Section -->
      <div class="settings-section mb-8">
        <h2 class="section-title">
          <el-icon><Document /></el-icon>
          Post Types Settings
        </h2>
        
        <div class="section-content">
          <div class="form-group">
            <label class="setting-label">Select Post Types</label>
            <div class="setting-control">
              <div class="selected-types">
                <span v-for="type in selectedPostTypes" :key="type" class="type-tag">
                  {{ type }}
                  <el-icon class="remove-tag" @click="removePostType(type)"><Close /></el-icon>
                </span>
              </div>
              <select v-model="newPostType" class="form-select" @change="addPostType">
                <option value="">Add post type...</option>
                <option v-for="type in availablePostTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
            </div>
            <p class="setting-description">Select the custom post types where you want to disable plugins.</p>
          </div>

          <div class="form-group">
            <label class="setting-label">Number of Posts to Load</label>
            <div class="setting-control number-input-group">
              <button class="number-btn" @click="decrementPosts">−</button>
              <input type="number" v-model="numberOfPosts" class="number-input" min="1" max="1000">
              <button class="number-btn" @click="incrementPosts">+</button>
            </div>
            <p class="setting-description">Default: 150 posts. Adjust if you have more posts to manage.</p>
          </div>

          <div class="info-note">
            <el-icon><InfoFilled /></el-icon>
            Note: Make sure to save settings to see options for each plugin for the selected post types.
          </div>
        </div>
      </div>
      <!-- Display Settings Section -->
      <div class="settings-section">
        <h2 class="section-title">
          <el-icon><View /></el-icon>
          Display Settings
        </h2>
        
        <div class="section-content">
          <div class="form-group">
            <label class="setting-label">Show Plugin Thumbnails</label>
            <div class="setting-control">
              <el-switch
                v-model="showThumbnails"
                class="custom-switch"
              />
            </div>
            <p class="setting-description">Enable this option to display plugin thumbnails in the plugin list.</p>
          </div>

          <div class="form-group">
            <label class="setting-label">Items Per Page in Plugin List</label>
            <div class="setting-control">
              <select v-model="itemsPerPage" class="form-select">
                <option value="10">10 items</option>
                <option value="20">20 items</option>
                <option value="50">50 items</option>
                <option value="100">100 items</option>
              </select>
            </div>
            <p class="setting-description">Select how many plugins to display per page in the manage plugin list.</p>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="save-button-container">
        <el-button type="primary" @click="saveSettings" size="large" class="save-button">
          <el-icon><Check /></el-icon>
          Save Settings
        </el-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { View, Document, Close, InfoFilled, Check } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'

// Display Settings
const showThumbnails = ref(true)
const itemsPerPage = ref(50) // Default to 50 items

// Post Types Management
const postTypes = ref([
  { value: 'post', label: 'Posts' },
  { value: 'page', label: 'Pages' },
  { value: 'product', label: 'Products' },
  { value: 'portfolio', label: 'Portfolio' },
  { value: 'attachment', label: 'Media' },
])

const selectedPostTypes = ref(['post', 'page'])
const newPostType = ref('')

// Computed property for available post types (excluding selected ones)
const availablePostTypes = computed(() => {
  return postTypes.value.filter(type => !selectedPostTypes.value.includes(type.value))
})

// Post type management methods
const addPostType = () => {
  if (newPostType.value && !selectedPostTypes.value.includes(newPostType.value)) {
    selectedPostTypes.value.push(newPostType.value)
    newPostType.value = ''
  }
}

const removePostType = (type) => {
  const index = selectedPostTypes.value.indexOf(type)
  if (index > -1) {
    selectedPostTypes.value.splice(index, 1)
  }
}

// Number of posts management
const numberOfPosts = ref(150)

const incrementPosts = () => {
  if (numberOfPosts.value < 1000) {
    numberOfPosts.value += 10
  }
}

const decrementPosts = () => {
  if (numberOfPosts.value > 1) {
    numberOfPosts.value = Math.max(1, numberOfPosts.value - 10)
  }
}

// Watch for number input changes to ensure valid range
const validateNumberOfPosts = () => {
  if (numberOfPosts.value < 1) numberOfPosts.value = 1
  if (numberOfPosts.value > 1000) numberOfPosts.value = 1000
}

// Save settings
const saveSettings = () => {
  // TODO: Implement API call
  const settings = {
    display: {
      showThumbnails: showThumbnails.value,
      itemsPerPage: itemsPerPage.value
    },
    postTypes: {
      selected: selectedPostTypes.value,
      numberOfPosts: numberOfPosts.value
    }
  }

  console.log('Settings saved:', settings)
  
  ElMessage({
    message: 'Settings saved successfully!',
    type: 'success',
  })
}
</script>

<style scoped>
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
