import { defineStore } from 'pinia'

export const usePluginStore = defineStore('plugins', {
  state: () => ({
    plugins: [],
    loading: false,
    error: null,
    settings: {},
    postTypes: ['page', 'post', 'product', 'portfolio', 'testimonial']
  }),

  getters: {
    activePlugins: (state) => state.plugins.filter(p => p.active),
    inactivePlugins: (state) => state.plugins.filter(p => !p.active),
    updateAvailable: (state) => state.plugins.filter(p => p.hasUpdate),
    totalPlugins: (state) => state.plugins.length,
    pluginById: (state) => (id) => state.plugins.find(p => p.id === id),
    customPostTypes: (state) => state.postTypes.filter(type => !['page', 'post'].includes(type))
  },

  actions: {
    async fetchPlugins() {
      this.loading = true
      this.error = null
      
      try {
        // In a real app, this would be an API call to WordPress backend
        // For demo purposes, we're using a timeout to simulate API delay
        await new Promise(resolve => setTimeout(resolve, 800))
        
        // Sample data for testing
        this.plugins = [
          {
            id: 1,
            name: 'Query Monitor',
            version: '3.7.1',
            author: 'John Blackbourn',
            description: 'The Developer Tools Panel for WordPress',
            active: true,
            file: 'query-monitor/query-monitor.php',
            hasUpdate: false
          },
          {
            id: 2,
            name: 'Elementor',
            version: '3.6.0',
            author: 'Elementor.com',
            description: 'The WordPress Website Builder',
            active: true,
            file: 'elementor/elementor.php',
            hasUpdate: true
          },
          {
            id: 3,
            name: 'HT Mega - Absolute Addons for Elementor',
            version: '2.1.0',
            author: 'HasThemes',
            description: 'Ultimate addons for Elementor page builder',
            active: true,
            file: 'ht-mega-for-elementor/htmega.php',
            hasUpdate: false
          },
          {
            id: 4,
            name: 'WooCommerce',
            version: '6.3.1',
            author: 'Automattic',
            description: 'An eCommerce toolkit for WordPress',
            active: false,
            file: 'woocommerce/woocommerce.php',
            hasUpdate: true
          },
          {
            id: 5,
            name: 'Yoast SEO',
            version: '18.0',
            author: 'Team Yoast',
            description: 'The first true all-in-one SEO solution for WordPress',
            active: false,
            file: 'wordpress-seo/wp-seo.php',
            hasUpdate: false
          },
          {
            id: 6,
            name: 'New Plugin',
            version: '18.0',
            author: 'Team Yoast',
            description: 'The first true all-in-one SEO solution for WordPress',
            active: false,
            file: 'wordpress-seo/wp-seo.php',
            hasUpdate: false
          },
          {
            id: 7,
            name: 'Test Plugin',
            version: '19.0',
            author: 'Team Yoast',
            description: 'The first true all-in-one SEO solution for WordPress',
            active: false,
            file: 'wordpress-seo/wp-seo.php',
            hasUpdate: false
          }
        ]
        
        // Load initial settings for each plugin
        this.plugins.forEach(plugin => {
          this.fetchPluginSettings(plugin.id)
        })
        
      } catch (error) {
        this.error = error.message || 'Failed to fetch plugins'
        console.error('Error fetching plugins:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchPluginSettings(pluginId) {
      try {
        // In a real app, this would be an API call to get plugin settings
        // For now, we'll use simulated settings based on plugin ID
        const defaultSettings = {
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
        }
        
        let pluginSettings = { ...defaultSettings }
        
        // Sample settings for specific plugins
        if (pluginId === 1) {
          pluginSettings = {
            ...defaultSettings,
            enable_deactivation: 'yes',
            uri_type: 'page_post',
            pages: ['1,/home', '2,/about'],
            posts: ['all_posts,all_posts']
          }
        } else if (pluginId === 2) {
          pluginSettings = {
            ...defaultSettings,
            enable_deactivation: 'yes',
            uri_type: 'custom',
            condition_list: {
              name: ['uri_equals', 'uri_contains'],
              value: ['contact', 'product']
            }
          }
        } else if (pluginId === 3) {
          pluginSettings = {
            ...defaultSettings,
            enable_deactivation: 'yes',
            uri_type: 'page_post_cpt',
            post_types: ['page', 'post', 'product'],
            pages: ['all_pages,all_pages'],
            products: ['1,/product/1']
          }
        }
        
        // Store settings
        this.settings[pluginId] = pluginSettings
        
        // Update plugin active state based on settings
        const plugin = this.plugins.find(p => p.id === pluginId)
        if (plugin) {
          plugin.active = pluginSettings.enable_deactivation !== 'yes'
        }
        
        return pluginSettings
        
      } catch (error) {
        console.error(`Error fetching settings for plugin ${pluginId}:`, error)
        return null
      }
    },

    async togglePlugin(plugin) {
      try {
        // In a real app, this would be an API call to toggle plugin status
        
        // Find the plugin in the store
        const index = this.plugins.findIndex(p => p.id === plugin.id)
        if (index !== -1) {
          // Toggle the active state
          this.plugins[index].active = !this.plugins[index].active
          
          // Update settings to match new state
          if (this.settings[plugin.id]) {
            this.settings[plugin.id].enable_deactivation = this.plugins[index].active ? 'no' : 'yes'
          }
          
          // Simulate API call
          await new Promise(resolve => setTimeout(resolve, 300))
          
          return true
        }
        
        return false
      } catch (error) {
        this.error = error.message || 'Failed to toggle plugin'
        console.error('Error toggling plugin:', error)
        throw error
      }
    },

    async updatePluginSettings(pluginId, settings) {
      try {
        // In a real app, this would be an API call to update plugin settings
        await new Promise(resolve => setTimeout(resolve, 600))
        
        // Update settings in store
        this.settings[pluginId] = { ...settings }
        
        // Update plugin active state based on settings
        const index = this.plugins.findIndex(p => p.id === pluginId)
        if (index !== -1) {
          this.plugins[index].active = settings.enable_deactivation !== 'yes'
        }
        
        return true
      } catch (error) {
        this.error = error.message || 'Failed to update plugin settings'
        console.error('Error updating plugin settings:', error)
        throw error
      }
    },

    async updatePlugin(plugin) {
      try {
        // In a real app, this would be an API call to update a plugin
        await new Promise(resolve => setTimeout(resolve, 1500))
        
        // Find and update the plugin
        const index = this.plugins.findIndex(p => p.id === plugin.id)
        if (index !== -1) {
          // Increment version number
          const versionParts = this.plugins[index].version.split('.')
          versionParts[versionParts.length - 1] = parseInt(versionParts[versionParts.length - 1]) + 1
          const newVersion = versionParts.join('.')
          
          // Update plugin
          this.plugins[index] = {
            ...this.plugins[index],
            version: newVersion,
            hasUpdate: false
          }
          
          return true
        }
        
        return false
      } catch (error) {
        this.error = error.message || 'Failed to update plugin'
        console.error('Error updating plugin:', error)
        throw error
      }
    },
    
    // Fetch pages for selection in plugin settings
    async fetchPages() {
      try {
        // In a real app, this would be an API call to get pages
        await new Promise(resolve => setTimeout(resolve, 300))
        
        return [
          { id: 1, title: 'Home', url: '/home' },
          { id: 2, title: 'About', url: '/about' },
          { id: 3, title: 'Contact', url: '/contact' },
          { id: 4, title: 'Services', url: '/services' },
          { id: 5, title: 'Portfolio', url: '/portfolio' }
        ]
      } catch (error) {
        console.error('Error fetching pages:', error)
        return []
      }
    },
    
    // Fetch posts for selection in plugin settings
    async fetchPosts() {
      try {
        // In a real app, this would be an API call to get posts
        await new Promise(resolve => setTimeout(resolve, 300))
        
        return [
          { id: 1, title: 'First Post', url: '/posts/1' },
          { id: 2, title: 'Second Post', url: '/posts/2' },
          { id: 3, title: 'Third Post', url: '/posts/3' },
          { id: 4, title: 'Fourth Post', url: '/posts/4' },
          { id: 5, title: 'Fifth Post', url: '/posts/5' }
        ]
      } catch (error) {
        console.error('Error fetching posts:', error)
        return []
      }
    },
    
    // Fetch custom post type items
    async fetchCustomPostTypeItems(postType) {
      try {
        // In a real app, this would be an API call to get custom post type items
        await new Promise(resolve => setTimeout(resolve, 400))
        
        return [
          { id: 1, title: `${postType.charAt(0).toUpperCase() + postType.slice(1)} 1`, url: `/${postType}/1` },
          { id: 2, title: `${postType.charAt(0).toUpperCase() + postType.slice(1)} 2`, url: `/${postType}/2` },
          { id: 3, title: `${postType.charAt(0).toUpperCase() + postType.slice(1)} 3`, url: `/${postType}/3` }
        ]
      } catch (error) {
        console.error(`Error fetching ${postType} items:`, error)
        return []
      }
    }
  }
})