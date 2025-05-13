import { defineStore } from 'pinia'
import api from '@/utils/axios'
import { ElNotification } from 'element-plus'

export const useRecommendedPluginsStore = defineStore('recommendedPlugins', {
  state: () => ({
    tabs: [],
    installedPlugins: [],
    assetsUrl: '',
    loading: false,
    error: null
  }),

  getters: {
    getButtonText: () => (plugin) => {
      // Check for pro plugins first
      if (plugin.is_pro || plugin.pro) {
        return 'Buy Now';
      }

      if (plugin.isLoading) {
        switch(plugin.status?.toLowerCase()) {
          case 'not_installed':
            return 'Installing...';
          case 'inactive':
            return 'Activating...';
          default:
            return 'Processing...';
        }
      }

      switch(plugin.status?.toLowerCase()) {
        case 'not_installed':
          return 'Install';
        case 'inactive':
          return 'Activate';
        case 'active':
          return 'Activated';
        default:
          return 'Install';
      }
    },

    isButtonDisabled: () => (plugin) => {
      // Pro plugins should never be disabled
      if (plugin.is_pro || plugin.pro) {
        return false;
      }

      const status = plugin.status?.toLowerCase();
      return status === 'active' || 
             status === 'installing' || 
             status === 'activating' || 
             plugin.isLoading;
    }
  },

  actions: {
    async fetchTabs() {
      try {
        this.loading = true
        this.error = null

        // Get initial tabs structure
        const recommendations_plugins = window.HTPMM?.adminSettings?.recommendations_plugins || [];

        // Fetch plugin data for each tab
        const tabsWithData = await Promise.all(recommendations_plugins.map(async (tab) => {
          if (!tab.plugins || !tab.plugins.length) return tab;
          
          // Get plugin info for all plugins in the tab
          const slugs = tab.plugins.map(p => p.slug);
          
          // Get both plugin info and status in parallel
          const [pluginsResponse, statusResponse] = await Promise.all([
            api.get('/htpm/v1/plugins-info', {
              params: { slugs: slugs.join(',') }
            }),
            api.get('/htpm/v1/plugins-status', {
              params: {
                plugins: slugs.join(','),
                nonce: window.HTPMM?.nonce
              }
            })
          ]);

          if (pluginsResponse.data.success && pluginsResponse.data.plugins) {
            // Create a map of plugin statuses
            const statusMap = {};
            if (statusResponse.data.success && statusResponse.data.plugins) {
              statusResponse.data.plugins.forEach(p => {
                statusMap[p.slug] = p.status;
              });
            }

            // Merge WordPress.org data with our plugin data and status
            tab.plugins = tab.plugins.map(plugin => ({
              ...plugin,
              ...pluginsResponse.data.plugins[plugin.slug],
              status: statusMap[plugin.slug] || 'not_installed',
              isLoading: false
            }));
          }
          
          return tab;
        }));

        this.tabs = tabsWithData
      } catch (error) {
        console.error('Error fetching recommended plugins:', error)
        this.error = error.message
      } finally {
        this.loading = false
      }
    },

    async installPlugin(plugin) {
      try {
        // Update plugin loading state
        this.updatePluginState(plugin.slug, { isLoading: true });

        // Call install API
        const response = await api.post('/htpm/v1/install-plugin', {
          slug: plugin.slug,
          nonce: window.HTPMM?.nonce
        });

        if (response.data.success) {
          // Update plugin status
          this.updatePluginState(plugin.slug, { 
            status: 'inactive',
            isLoading: false 
          });
          return true;
        }
        return false;
      } catch (error) {
        console.error('Error installing plugin:', error);
        this.updatePluginState(plugin.slug, { isLoading: false });
        throw error;
      }
    },

    async activatePlugin(plugin) {
      try {
        // Update plugin loading state
        this.updatePluginState(plugin.slug, { isLoading: true });

        // Call activate API
        const response = await api.post('/htpm/v1/activate-plugin', {
          slug: plugin.slug,
          nonce: window.HTPMM?.nonce
        });

        if (response.data.success) {
          // Update plugin status
          this.updatePluginState(plugin.slug, { 
            status: 'active',
            isLoading: false 
          });
          return true;
        }
        return false;
      } catch (error) {
        console.error('Error activating plugin:', error);
        this.updatePluginState(plugin.slug, { isLoading: false });
        throw error;
      }
    },

    async handlePluginAction(plugin) {
      try {
        // Check if it's a pro plugin first
        if (plugin.is_pro && plugin.status?.toLowerCase() === 'not_installed') {
          window.open(plugin.link, '_blank');
          return;
        }

        // Handle free plugin installation/activation
        if (plugin.status?.toLowerCase() === 'not_installed') {
          const installed = await this.installPlugin(plugin);
          if (installed) {
            await this.activatePlugin(plugin);
          }
        } else if (plugin.status?.toLowerCase() === 'inactive') {
          await this.activatePlugin(plugin);
        }
      } catch (error) {
        console.error('Error handling plugin action:', error);
        // Show notification
        ElNotification({
          title: 'Error',
          message: error.message || 'Failed to perform plugin action',
          type: 'error',
          duration: 3000
        });
        throw error;
      }
    },

    updatePluginState(slug, updates) {
      this.tabs = this.tabs.map(tab => ({
        ...tab,
        plugins: tab.plugins?.map(plugin => {
          if (plugin.slug === slug) {
            return { ...plugin, ...updates };
          }
          return plugin;
        })
      }));
    },

    // async installPlugin(plugin) {
    //   try {
    //     const formData = new FormData()
    //     formData.append('action', 'htpm_ajax_plugin_activation')
    //     formData.append('location', plugin.location)
    //     formData.append('slug', plugin.slug)
    //     formData.append('nonce', window.HTPMM.nonce)

    //     const response = await axios.post(window.HTPMM.ajaxUrl, formData)
    //     console.log(response.data);
    //     if (response.data.success) {
    //       this.installedPlugins.push(plugin.slug)
    //     }
    //     return response.data
    //   } catch (error) {
    //     console.error('Error installing plugin:', error)
    //     throw error
    //   }
    // }
  }
})
