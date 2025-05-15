// plugins.js - Simplified store with straightforward data loading
import { defineStore } from 'pinia'
import api from '../utils/axios'

// Default dashboard settings
const defaultDashboardSettings = {
  selectedPostTypes: [],
  numberOfPosts: 150,
  showThumbnails: false,
  itemsPerPage: 10
}

// Create an axios instance with WordPress REST API base URL and nonce

export const usePluginStore = defineStore('plugins', {
  state: () => ({
    apiBase: (window.HTPMM?.restUrl || '/wp-json').replace(/\/$/, '') + '/htpm/v1',
    plugins: [],
    allSettings: window.HTPMM?.adminSettings?.allSettings || {},
    dashboardSettings: {
      htpm_dashboard_settings: { ...defaultDashboardSettings }
    },
    loading: false,
    error: null,
    settings: {},
    postTypes: [],
    pages: [],
    posts: [],
    customPostTypeItems: {},
    selectedAllPostTypes:[],
    changelog: [],
    changelogLoading: false,
    changelogRead: false,
    notificationStatus: false,
  }),

  getters: {
    // All plugins that are active in WordPress
    wpActivePlugins: (state) => state.plugins.filter(p => p.wpActive),
    
    // Plugins that are both active in WordPress and not disabled by our plugin
    activePlugins: (state) => state.plugins.filter(p => p.wpActive && !p.enable_deactivation),
    
    // Plugins that are disabled by our plugin (but still active in WordPress)
    disabledPlugins: (state) => state.plugins.filter(p => p.wpActive && p.enable_deactivation),
    
    // Plugins that are inactive in WordPress
    inactivePlugins: (state) => state.plugins.filter(p => !p.wpActive),
    
    // Plugins that have been optimized
    optimizedPlugins: (state) => state.plugins.filter(p => p.wpActive && p.enable_deactivation === 'yes'),
    
    // Plugins with updates available
    updateAvailable: (state) => state.plugins.filter(p => p.hasUpdate),
    totalPlugins: (state) => state.plugins.length,
    selectedAllPostTypes: (state) => state.selectedAllPostTypes,

  },

  actions: {
    // Fetch all plugins
    async fetchPlugins() {
      this.loading = true
      this.error = null
      
      try {
        const response = await api.get('/htpm/v1/plugins')
        
        // Initialize all plugins with enable_deactivation=true by default
        this.plugins = response?.data?.htpm_list_plugins?.map(plugin => ({
          ...plugin,
          // All plugins start as disabled by default
          enable_deactivation: 'no'
        })) || []
        
        // Store all_settings directly without spreading since it's an object
        this.allSettings = response?.data?.all_settings || {}

        return this.plugins
      } catch (error) {
        this.error = error.message || 'Failed to fetch plugins'
        console.error('Error fetching plugins:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch all plugin settings
    async fetchAllPluginSettings() {
      const apiUrl = `${this.apiBase}/plugins/settings`;
      try {
        const response = await fetch(apiUrl, {
          headers: {
            'X-WP-Nonce': window.HTPMM?.nonce || '',
            'Content-Type': 'application/json'
          }
        });
        const result = await response.json();
        
        if (result.success) {
          // Update all settings at once
          this.settings = result.data;
        }
        
        return result.data;
      } catch (error) {
        console.error('Error fetching all plugin settings:', error);
        throw error;
      }
    },

    // Fetch settings for a specific plugin
    async fetchPluginSettings(pluginId) {
      try {
        // Return cached settings if available
        if (this.settings[pluginId]) {
          return this.settings[pluginId];
        }

        const response = await api.get(`/htpm/v1/plugins/${pluginId}/settings`)
        
        // Store settings in the store state
        this.settings[pluginId] = response.data
        
        // Update the plugin's enable_deactivation state based on settings
        const pluginIndex = this.plugins.findIndex(p => p.id === pluginId)
        
        if (pluginIndex !== -1) {
          // A plugin is only enabled if settings explicitly say 'no' to deactivation
          const enable_deactivation = !response.data || response.data.enable_deactivation !== 'no'
          this.plugins[pluginIndex].enable_deactivation = enable_deactivation
        }
        
        return response.data
      } catch (error) {
        console.error(`Error fetching settings for plugin ${pluginId}:`, error)
        return null
      }
    },
    // Fetch dashboard settings
    async fetchDashboardSettings() {
      try {
        const response = await api.get('/htpm/v1/get-dashboard-settings')
        console.log('API Response:', response.data)
        
        // Get settings from response or use defaults
        const settings = response.data?.htpm_dashboard_settings || { ...defaultDashboardSettings }
        
        // Create a new settings object with proper type handling
        const newSettings = {
          selectedPostTypes: Array.isArray(settings.selectedPostTypes) ? [...settings.selectedPostTypes] : [],
          numberOfPosts: parseInt(settings.numberOfPosts) || 150,
          showThumbnails: typeof settings.showThumbnails === 'boolean' ? settings.showThumbnails : true,
          itemsPerPage: parseInt(settings.itemsPerPage) || 10
        }

        // Update state
        this.dashboardSettings = {
          htpm_dashboard_settings: newSettings
        }
        return this.dashboardSettings
      } catch (error) {
        console.error('Error fetching dashboard settings:', error)
        // Reset to default settings on error
        this.dashboardSettings = {
          htpm_dashboard_settings: { ...defaultDashboardSettings }
        }
        return this.dashboardSettings
      }
    },
    // Update dashboard settings
    async updateDashboardSettings(settings) {
      try {
        if (!settings) {
          throw new Error('Invalid settings format')
        }

        // Ensure settings are properly formatted
        const formattedSettings = {
          postTypes: Array.isArray(settings.postTypes) ? settings.postTypes : ['page', 'post'],
          htpm_load_posts: parseInt(settings.htpm_load_posts) || 150,
          showThumbnails: Boolean(settings.showThumbnails),
          itemsPerPage: parseInt(settings.itemsPerPage) || 10
        }

        const response = await api.post('/htpm/v1/update-dashboard-settings', formattedSettings)

        if (response.data && response.data.success) {
          // Update state with new settings
          this.allSettings = {
            ...this.allSettings,
            htpm_enabled_post_types: formattedSettings.postTypes,
            htpm_load_posts: formattedSettings.htpm_load_posts,
            showThumbnails: formattedSettings.showThumbnails,
            itemsPerPage: formattedSettings.itemsPerPage
          }
          return response.data
        } else {
          throw new Error(response.data?.message || 'Failed to update settings')
        }
      } catch (error) {
        console.error('Error updating dashboard settings:', error)
        throw error
      }
    },
    // selected selected AllPost Types
    // Update plugin settings
// updatePluginSettings method for the store with fixed settings preservation
async updatePluginSettings(pluginId, settings) {
  try {
    // Invalidate cache for this plugin
    delete this.settings[pluginId];

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
      // Update the plugin's enable_deactivation state in the plugins list
      const pluginIndex = this.plugins.findIndex(p => p.id === pluginId)
      if (pluginIndex !== -1) {
        this.plugins[pluginIndex].enable_deactivation = completeSettings.enable_deactivation === 'yes'
      }
      
      // Store settings in the store state
      this.settings[pluginId] = completeSettings
      
      // Return the updated settings
      return completeSettings
    } else {
      console.error('Server returned error:', response.data)
      throw new Error(response.data?.message || 'Failed to update settings')
    }
  } catch (error) {
    this.error = error.message || 'Failed to update plugin settings'
    console.error('Error updating plugin settings:', error)
    throw error
  }
},

    /**
     * Fetch Changelog Data
     */
    async fetchChangelog() {
      this.changelogLoading = true;
      try {
        const response = await api.get('/htpm/v1/changelog');
        if (response?.data?.success) {
          this.changelog = response.data.data;
          return response.data.data;
        } else {
          throw new Error('Invalid response format');
        }
      } catch (error) {
        console.error('Error fetching changelog:', error);
        throw error;
      } finally {
        this.changelogLoading = false;
      }
    },

    /**
     * Mark Changelog as Read
     */
    async markChangelogRead() {
      try {
        const response = await api.post('/htpm/v1/changelog/mark-read');
        if (response?.data?.success) {
          this.changelogRead = true;
          this.notificationStatus = false;
          return response.data;
        } else {
          throw new Error('Invalid response format');
        }
      } catch (error) {
        console.error('Error marking changelog as read:', error);
        throw error;
      }
    },

    /**
     * Check Notification Status
     */
    async checkNotificationStatus() {
      try {
        const response = await api.get('/htpm/v1/changelog/status');
        if (response?.data?.success) {
          this.notificationStatus = response.data.data;
          return response.data.data;
        } else {
          throw new Error('Invalid response format');
        }
      } catch (error) {
        console.error('Error checking notification status:', error);
        throw error;
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
        // Ensure we're getting the correct data structure
        this.postTypes = Array.isArray(response.data) ? response.data : []
        
        // Map the response to the expected format if needed
        if (this.postTypes.length > 0 && !this.postTypes[0].name) {
          this.postTypes = this.postTypes.map(type => ({
            name: type.name || '',
            label: type.label || type.name || ''
          }))
        }
        
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