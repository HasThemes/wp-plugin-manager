// plugins.js - Simplified store with straightforward data loading
import { defineStore } from 'pinia'
import axios from 'axios'

// Create an axios instance with WordPress REST API base URL and nonce
const api = axios.create({
  baseURL: window.htpmData?.restUrl || '/wp-json',
  headers: {
    'X-WP-Nonce': window.htpmData?.nonce || '',
    'Content-Type': 'application/json'
  }
})

export const usePluginStore = defineStore('plugins', {
  state: () => ({
    plugins: [],
    loading: false,
    error: null,
    settings: {},
    postTypes: [],
    pages: [],
    posts: [],
    customPostTypeItems: {}
  }),

  getters: {
    // All plugins that are active in WordPress
    wpActivePlugins: (state) => state.plugins.filter(p => p.wpActive),
    
    // Plugins that are both active in WordPress and not disabled by our plugin
    activePlugins: (state) => state.plugins.filter(p => p.wpActive && !p.isDisabled),
    
    // Plugins that are disabled by our plugin (but still active in WordPress)
    disabledPlugins: (state) => state.plugins.filter(p => p.wpActive && p.isDisabled),
    
    // Plugins that are inactive in WordPress
    inactivePlugins: (state) => state.plugins.filter(p => !p.wpActive),
    
    updateAvailable: (state) => state.plugins.filter(p => p.hasUpdate),
    totalPlugins: (state) => state.plugins.length
  },

  actions: {
    // Fetch all plugins
    async fetchPlugins() {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get('/htpm/v1/plugins')
        
        // Initialize all plugins with isDisabled=true by default
        this.plugins = response.data.map(plugin => ({
          ...plugin,
          // All plugins start as disabled by default
          isDisabled: true
        }))
        
        return this.plugins
      } catch (error) {
        this.error = error.message || 'Failed to fetch plugins'
        console.error('Error fetching plugins:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch settings for a specific plugin
    async fetchPluginSettings(pluginId) {
      try {
        const response = await api.get(`/htpm/v1/plugins/${pluginId}/settings`)
        
        // Store settings in the store state
        this.settings[pluginId] = response.data
        
        // Update the plugin's isDisabled state based on settings
        const pluginIndex = this.plugins.findIndex(p => p.id === pluginId)
        
        if (pluginIndex !== -1) {
          // A plugin is only enabled if settings explicitly say 'no' to deactivation
          const isDisabled = !response.data || response.data.enable_deactivation !== 'no'
          this.plugins[pluginIndex].isDisabled = isDisabled
        }
        
        return response.data
      } catch (error) {
        console.error(`Error fetching settings for plugin ${pluginId}:`, error)
        return null
      }
    },

    // Update plugin settings
// updatePluginSettings method for the store with fixed settings preservation
async updatePluginSettings(pluginId, settings) {
  try {
    // Log what we're about to save for debugging
    console.log('Store: updatePluginSettings for plugin', pluginId, JSON.parse(JSON.stringify(settings)))
    
    // Make sure we're sending a complete, valid settings object
    const completeSettings = { ...settings }
    
    // Ensure all required arrays exist and are properly initialized
    if (!Array.isArray(completeSettings.pages)) {
      completeSettings.pages = []
    }
    
    if (!Array.isArray(completeSettings.posts)) {
      completeSettings.posts = []
    }
    
    if (!Array.isArray(completeSettings.post_types)) {
      completeSettings.post_types = ['page', 'post']
    }
    
    if (!completeSettings.condition_list) {
      completeSettings.condition_list = {
        name: ['uri_equals'],
        value: [''],
      }
    } else {
      if (!Array.isArray(completeSettings.condition_list.name)) {
        completeSettings.condition_list.name = ['uri_equals']
      }
      
      if (!Array.isArray(completeSettings.condition_list.value)) {
        completeSettings.condition_list.value = ['']
      }
    }
    
    // Send the request to the server
    const response = await api.post(`/htpm/v1/plugins/${pluginId}/settings`, completeSettings)
    
    if (response.data.success) {
      // Update settings in store
      this.settings[pluginId] = { ...completeSettings }
      
      // Update the plugin's isDisabled state based on settings
      const pluginIndex = this.plugins.findIndex(p => p.id === pluginId)
      if (pluginIndex !== -1) {
        this.plugins[pluginIndex].isDisabled = completeSettings.enable_deactivation === 'yes'
      }
      
      console.log('Settings updated successfully:', this.settings[pluginId])
    } else {
      console.error('Server returned error:', response.data)
    }
    
    return response.data
  } catch (error) {
    this.error = error.message || 'Failed to update plugin settings'
    console.error('Error updating plugin settings:', error)
    throw error
  }
},

    // Fetch pages for settings selector
    async fetchPages() {
      try {
        if (this.pages.length > 0) {
          return this.pages
        }
        
        const response = await api.get('/htpm/v1/pages')
        this.pages = response.data
        return this.pages
      } catch (error) {
        console.error('Error fetching pages:', error)
        return []
      }
    },
    
    // Fetch posts for settings selector
    async fetchPosts() {
      try {
        if (this.posts.length > 0) {
          return this.posts
        }
        
        const response = await api.get('/htpm/v1/posts')
        this.posts = response.data
        return this.posts
      } catch (error) {
        console.error('Error fetching posts:', error)
        return []
      }
    },
    
    // Fetch available post types
    async fetchPostTypes() {
      try {
        if (this.postTypes.length > 0) {
          return this.postTypes
        }
        
        const response = await api.get('/htpm/v1/post-types')
        this.postTypes = response.data
        return this.postTypes
      } catch (error) {
        console.error('Error fetching post types:', error)
        return []
      }
    },
    
    // Fetch items for a specific post type
    async fetchCustomPostTypeItems(postType) {
      try {
        if (this.customPostTypeItems[postType]?.length > 0) {
          return this.customPostTypeItems[postType]
        }
        
        const response = await api.get(`/htpm/v1/post-type-items/${postType}`)
        this.customPostTypeItems[postType] = response.data
        return response.data
      } catch (error) {
        console.error(`Error fetching ${postType} items:`, error)
        return []
      }
    }
  }
})