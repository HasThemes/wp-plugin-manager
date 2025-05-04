<template>
  <div class="htpm-sidebar">
    <!-- Dynamic Sidebar Content -->
    <div v-if="sidebarContent" v-html="sidebarContent"></div>
    <div v-else>
      <el-skeleton :rows="3" animated />
    </div>
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
