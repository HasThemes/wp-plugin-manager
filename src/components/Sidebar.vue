<template>
  <div class="htpm-sidebar">
    <!-- Dynamic Sidebar Content -->
    <div v-if="sidebarContent" v-html="sidebarContent"></div>
    <template v-else>
      <el-card class="wp-plugin-skeleton-card">
        <el-skeleton :rows="6" animated>
          <template #template>
            <div class="header">
              <el-skeleton-item variant="h3" style="width: 80%; margin: 0 auto;" />
              <el-skeleton-item variant="text" style="width: 90%; margin: 10px auto;" />
            </div>

            <div class="features" style="margin-top: 20px;">
              <el-skeleton-item variant="text" style="width: 60%; margin-bottom: 10px;" />
              <el-divider />

              <el-skeleton-item variant="text" style="width: 90%; margin: 10px auto;" />
              <el-skeleton-item variant="text" style="width: 85%; margin: 10px auto;" />
              <el-skeleton-item variant="text" style="width: 80%; margin: 10px auto;" />
              <el-skeleton-item variant="text" style="width: 88%; margin: 10px auto;" />
              <el-skeleton-item variant="text" style="width: 82%; margin: 10px auto;" />
            </div>

            <div class="footer" style="margin-top: 30px;">
              <el-skeleton-item variant="button" style="width: 60%; height: 40px;" />
            </div>
          </template>
        </el-skeleton>
      </el-card>
      <el-card class="wp-plugin-skeleton-card" style="margin-top: 20px;">
        <el-skeleton :rows="6" animated>
          <template #template>
            <div class="header">
              <el-skeleton-item variant="h3" style="width: 80%; margin: 0 auto;" />
              <el-skeleton-item variant="text" style="width: 90%; margin: 10px auto;" />
            </div>

            <div class="features" style="margin-top: 20px;">
              <el-skeleton-item variant="text" style="width: 60%; margin-bottom: 10px;" />
              <el-divider />

              <el-skeleton-item variant="text" style="width: 90%; margin: 10px auto;" />
              <el-skeleton-item variant="text" style="width: 85%; margin: 10px auto;" />
              <el-skeleton-item variant="text" style="width: 80%; margin: 10px auto;" />
              <el-skeleton-item variant="text" style="width: 88%; margin: 10px auto;" />
              <el-skeleton-item variant="text" style="width: 82%; margin: 10px auto;" />
            </div>

            <div class="footer" style="margin-top: 30px;">
              <el-skeleton-item variant="button" style="width: 60%; height: 40px;" />
            </div>
          </template>
        </el-skeleton>
      </el-card>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const sidebarContent = ref('')
// Create an axios instance with WordPress REST API base URL and nonce
const api = axios.create({
  baseURL: window.HTPMM?.restUrl || `${window.location.origin}/plugin-manager/wp-json`,
  headers: {
    'X-WP-Nonce': window.HTPMM?.nonce || '',
    'Content-Type': 'application/json'
  }
})
const fetchSidebarContent = async () => {
  try {
    const response = await api.get('/htpm/v1/sidebar-content')
    sidebarContent.value = response.data.content
  } catch (error) {
    console.error('Error fetching sidebar content:', error)
  }
}

onMounted(() => {
  fetchSidebarContent()
})
</script>
<style scoped>
.wp-plugin-skeleton-card {
  max-width: 360px;
  margin: auto;
  padding: 20px;
  border-radius: 12px;
  text-align: left;
}
</style>
