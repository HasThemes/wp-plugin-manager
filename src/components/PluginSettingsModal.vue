<template>
  <el-dialog
    :title="plugin.name + ' Settings'"
    v-model="dialogVisible"
    width="600px"
    @close="handleClose"
  >
    <el-form :model="form" label-width="150px">
      <el-form-item label="Disable This Plugin:">
        <el-switch v-model="form.disabled" />
      </el-form-item>

      <el-form-item label="Disable Plugin on:">
        <el-select v-model="form.devices" placeholder="Select devices" style="width: 100%">
          <el-option label="All Devices" value="all" />
          <el-option label="Desktop Only" value="desktop" />
          <el-option label="Mobile Only" value="mobile" />
          <el-option label="Tablet Only" value="tablet" />
        </el-select>
      </el-form-item>

      <el-form-item label="Action:">
        <el-select v-model="form.action" placeholder="Select action" style="width: 100%">
          <el-option label="Enable on Selected Pages" value="enable" />
          <el-option label="Disable on Selected Pages" value="disable" />
        </el-select>
        <div class="el-form-item-description">
          {{ form.action === 'enable' ? 'Plugin will be enabled only on selected pages' : 'Plugin will be disabled only on selected pages' }}
        </div>
      </el-form-item>

      <el-form-item label="Page Type:">
        <el-select v-model="form.pageType" placeholder="Select page type" style="width: 100%">
          <el-option label="Post, Pages & Custom Post Type" value="all" />
          <el-option label="Custom" value="custom" />
        </el-select>
      </el-form-item>

      <el-form-item label="Select Post Types:">
        <el-checkbox-group v-model="form.postTypes">
          <el-checkbox label="page">Page</el-checkbox>
          <el-checkbox label="post">Post</el-checkbox>
        </el-checkbox-group>
      </el-form-item>

      <el-form-item label="Select Pages:" v-if="form.postTypes.includes('page')">
        <el-select
          v-model="form.selectedPages"
          multiple
          filterable
          remote
          placeholder="Start typing to search pages..."
          :remote-method="searchPages"
          style="width: 100%"
        >
          <el-option
            v-for="page in pages"
            :key="page.id"
            :label="page.title"
            :value="page.id"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="Select Posts:" v-if="form.postTypes.includes('post')">
        <el-select
          v-model="form.selectedPosts"
          multiple
          filterable
          remote
          placeholder="Start typing to search posts..."
          :remote-method="searchPosts"
          style="width: 100%"
        >
          <el-option
            v-for="post in posts"
            :key="post.id"
            :label="post.title"
            :value="post.id"
          />
        </el-select>
      </el-form-item>
    </el-form>

    <template #footer>
      <span class="dialog-footer">
        <el-button @click="handleClose">Cancel</el-button>
        <el-button type="primary" @click="handleSave">Save Changes</el-button>
      </span>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { ElMessage } from 'element-plus'

const props = defineProps({
  plugin: {
    type: Object,
    required: true
  },
  visible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:visible', 'save'])

const dialogVisible = ref(props.visible)
const pages = ref([])
const posts = ref([])

const form = reactive({
  disabled: false,
  devices: 'all',
  action: 'enable',
  pageType: 'all',
  postTypes: [],
  selectedPages: [],
  selectedPosts: []
})

watch(() => props.visible, (newVal) => {
  dialogVisible.value = newVal
})

watch(dialogVisible, (newVal) => {
  emit('update:visible', newVal)
})

const searchPages = async (query) => {
  if (query) {
    // TODO: Implement API call to search pages
    // pages.value = await searchPagesAPI(query)
  }
}

const searchPosts = async (query) => {
  if (query) {
    // TODO: Implement API call to search posts
    // posts.value = await searchPostsAPI(query)
  }
}

const handleClose = () => {
  dialogVisible.value = false
}

const handleSave = async () => {
  try {
    await emit('save', {
      pluginId: props.plugin.id,
      settings: { ...form }
    })
    ElMessage.success('Settings saved successfully')
    handleClose()
  } catch (error) {
    ElMessage.error('Failed to save settings')
  }
}
</script>

<style lang="scss" scoped>
.el-form-item-description {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}
</style>
