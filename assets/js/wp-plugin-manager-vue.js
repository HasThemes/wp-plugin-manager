// wp-plugin-manager-vue.js

/**
 * WP Plugin Manager - Vue 3 Dashboard
 * 
 * Main Vue application for the WP Plugin Manager dashboard
 */

import { createApp, ref, reactive, computed, onMounted, watch } from 'vue';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import {
  Setting, Bell, Document, Tools, Key, QuestionFilled,
  Search, Refresh, Filter, ArrowDown, Plus, Minus,
  Check, Close, Edit, ArrowUp, CircleCheck, Warning
} from '@element-plus/icons-vue';

// Define the Vue app
const PluginManagerApp = {
  setup() {
    // ---------------
    // State management
    // ---------------
    const pluginsList = ref([]);
    const loading = ref(true);
    const stats = reactive({
      total: 0,
      active: 0,
      updates: 0,
      inactive: 0
    });
    const searchQuery = ref('');
    const selectedPlugin = ref(null);
    const settingsDialogVisible = ref(false);
    const notificationsVisible = ref(false);
    const notifications = ref([]);
    const savingSettings = ref(false);
    const errorMessage = ref('');
    const successMessage = ref('');
    const currentTab = ref('general');

    // Device types and condition types for settings
    const deviceTypes = [
      { label: 'All Devices', value: 'all' },
      { label: 'Desktop', value: 'desktop' },
      { label: 'Tablet', value: 'tablet' },
      { label: 'Mobile', value: 'mobile' },
      { label: 'Desktop + Tablet', value: 'desktop_plus_tablet' },
      { label: 'Tablet + Mobile', value: 'tablet_plus_mobile' }
    ];

    const conditionTypes = [
      { label: 'Disable on Selected Pages', value: 'disable_on_selected' },
      { label: 'Enable on Selected Pages', value: 'enable_on_selected' }
    ];

    const uriTypes = [
      { label: 'Page', value: 'page' },
      { label: 'Post', value: 'post' },
      { label: 'Page & Post', value: 'page_post' },
      { label: 'Post, Pages & Custom Post Type', value: 'page_post_cpt' },
      { label: 'Custom', value: 'custom' }
    ];

    // Options for the custom URI condition
    const uriConditionOptions = [
      { label: 'URI Equals', value: 'uri_equals' },
      { label: 'URI Not Equals', value: 'uri_not_equals' },
      { label: 'URI Contains', value: 'uri_contains' },
      { label: 'URI Not Contains', value: 'uri_not_contains' }
    ];

    // ---------------
    // Computed properties
    // ---------------
    const filteredPlugins = computed(() => {
      if (!searchQuery.value) return pluginsList.value;
      
      const query = searchQuery.value.toLowerCase();
      return pluginsList.value.filter(plugin => 
        plugin.name.toLowerCase().includes(query)
      );
    });

    const canShowPostTypes = computed(() => {
      if (!selectedPlugin.value) return false;
      return selectedPlugin.value.uri_type === 'page_post_cpt';
    });

    const canShowPageSelection = computed(() => {
      if (!selectedPlugin.value) return false;
      return ['page', 'page_post'].includes(selectedPlugin.value.uri_type) || 
             (selectedPlugin.value.uri_type === 'page_post_cpt' && 
              selectedPlugin.value.post_types.includes('page'));
    });

    const canShowPostSelection = computed(() => {
      if (!selectedPlugin.value) return false;
      return ['post', 'page_post'].includes(selectedPlugin.value.uri_type) || 
             (selectedPlugin.value.uri_type === 'page_post_cpt' && 
              selectedPlugin.value.post_types.includes('post'));
    });

    const canShowCustomConditions = computed(() => {
      if (!selectedPlugin.value) return false;
      return selectedPlugin.value.uri_type === 'custom';
    });

    // ---------------
    // Methods
    // ---------------
    
    // Load all plugin data from WordPress
    const loadPluginsData = async () => {
      loading.value = true;
      
      try {
        // Use WordPress ajax to get the plugins data
        const response = await fetch(ajaxurl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'htpm_get_plugins_data',
            nonce: htpmParams.nonce
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          pluginsList.value = data.plugins.map(plugin => ({
            ...plugin,
            showSettings: false,
            saving: false
          }));
          
          // Update stats
          stats.total = data.stats.total || 0;
          stats.active = data.stats.active || 0;
          stats.updates = data.stats.updates || 0;
          stats.inactive = data.stats.inactive || 0;
          
          // Load notifications
          if (data.notifications) {
            notifications.value = data.notifications;
          }
        } else {
          throw new Error(data.message || 'Failed to load plugins data');
        }
      } catch (error) {
        console.error('Error loading plugins data:', error);
        errorMessage.value = 'Failed to load plugins data. Please refresh the page.';
      } finally {
        loading.value = false;
      }
    };
    
    // Toggle plugin activation state
    const togglePluginState = async (plugin) => {
      try {
        const newState = !plugin.active;
        
        const response = await fetch(ajaxurl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'htpm_toggle_plugin_state',
            plugin: plugin.id,
            state: newState ? 'active' : 'inactive',
            nonce: htpmParams.nonce
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          plugin.active = newState;
          
          // Update stats
          stats.active = newState ? stats.active + 1 : stats.active - 1;
          stats.inactive = newState ? stats.inactive - 1 : stats.inactive + 1;
          
          successMessage.value = `Plugin ${newState ? 'activated' : 'deactivated'} successfully`;
          setTimeout(() => {
            successMessage.value = '';
          }, 3000);
        } else {
          throw new Error(data.message || 'Failed to toggle plugin state');
        }
      } catch (error) {
        console.error('Error toggling plugin state:', error);
        errorMessage.value = `Failed to ${plugin.active ? 'deactivate' : 'activate'} plugin`;
        setTimeout(() => {
          errorMessage.value = '';
        }, 3000);
      }
    };
    
    // Open plugin settings dialog
    const openSettings = (plugin) => {
      // Create a deep copy of the plugin data to prevent modifying the original
      selectedPlugin.value = JSON.parse(JSON.stringify(plugin));
      
      // Ensure the condition list has at least one entry
      if (!selectedPlugin.value.condition_list) {
        selectedPlugin.value.condition_list = {
          name: ['uri_equals'],
          value: ['']
        };
      } else if (!selectedPlugin.value.condition_list.name || selectedPlugin.value.condition_list.name.length === 0) {
        selectedPlugin.value.condition_list.name = ['uri_equals'];
        selectedPlugin.value.condition_list.value = [''];
      }
      
      settingsDialogVisible.value = true;
    };
    
    // Add a new condition row
    const addCondition = () => {
      selectedPlugin.value.condition_list.name.push('uri_equals');
      selectedPlugin.value.condition_list.value.push('');
    };
    
    // Remove a condition row
    const removeCondition = (index) => {
      selectedPlugin.value.condition_list.name.splice(index, 1);
      selectedPlugin.value.condition_list.value.splice(index, 1);
    };
    
    // Save plugin settings
    const savePluginSettings = async () => {
      if (!selectedPlugin.value) return;
      
      savingSettings.value = true;
      
      try {
        const response = await fetch(ajaxurl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'htpm_save_plugin_settings',
            plugin: selectedPlugin.value.id,
            settings: JSON.stringify(selectedPlugin.value),
            nonce: htpmParams.nonce
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          // Update the plugin data in the list
          const index = pluginsList.value.findIndex(p => p.id === selectedPlugin.value.id);
          if (index !== -1) {
            pluginsList.value[index] = {
              ...pluginsList.value[index],
              ...selectedPlugin.value
            };
          }
          
          successMessage.value = 'Plugin settings saved successfully';
          setTimeout(() => {
            successMessage.value = '';
          }, 3000);
          
          // Close the dialog
          settingsDialogVisible.value = false;
        } else {
          throw new Error(data.message || 'Failed to save plugin settings');
        }
      } catch (error) {
        console.error('Error saving plugin settings:', error);
        errorMessage.value = 'Failed to save plugin settings';
        setTimeout(() => {
          errorMessage.value = '';
        }, 3000);
      } finally {
        savingSettings.value = false;
      }
    };
    
    // Toggle notifications panel
    const toggleNotifications = () => {
      notificationsVisible.value = !notificationsVisible.value;
    };
    
    // Filter plugins
    const filterPlugins = (type) => {
      // Implementation for filtering plugins (active, inactive, updates)
    };
    
    // Sort plugins
    const sortPlugins = (criteria) => {
      // Implementation for sorting plugins
    };
    
    // Initialize the component
    onMounted(() => {
      loadPluginsData();
    });
    
    // Watch for changes in URI type to update the UI
    watch(() => selectedPlugin.value?.uri_type, (newValue) => {
      if (!selectedPlugin.value) return;
      
      // Reset post types selection if not using 'page_post_cpt' type
      if (newValue !== 'page_post_cpt' && selectedPlugin.value.post_types) {
        selectedPlugin.value.post_types = ['post', 'page'];
      }
    });

    // Return all data and methods to the template
    return {
      // State
      pluginsList,
      loading,
      stats,
      searchQuery,
      selectedPlugin,
      settingsDialogVisible,
      notificationsVisible,
      notifications,
      savingSettings,
      errorMessage,
      successMessage,
      currentTab,
      deviceTypes,
      conditionTypes,
      uriTypes,
      uriConditionOptions,
      
      // Computed
      filteredPlugins,
      canShowPostTypes,
      canShowPageSelection,
      canShowPostSelection,
      canShowCustomConditions,
      
      // Methods
      loadPluginsData,
      togglePluginState,
      openSettings,
      addCondition,
      removeCondition,
      savePluginSettings,
      toggleNotifications,
      filterPlugins,
      sortPlugins,
      
      // Icon components
      Setting, Bell, Document, Tools, Key, QuestionFilled,
      Search, Refresh, Filter, ArrowDown, Plus, Minus,
      Check, Close, Edit, ArrowUp, CircleCheck, Warning
    };
  }
};

// Initialize the Vue app when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const app = createApp(PluginManagerApp);
  
  // Register Element Plus
  app.use(ElementPlus);
  
  // Register all icon components
  app.component('el-icon-setting', Setting);
  app.component('el-icon-bell', Bell);
  app.component('el-icon-document', Document);
  app.component('el-icon-tools', Tools);
  app.component('el-icon-key', Key);
  app.component('el-icon-question-filled', QuestionFilled);
  app.component('el-icon-search', Search);
  app.component('el-icon-refresh', Refresh);
  app.component('el-icon-filter', Filter);
  app.component('el-icon-arrow-down', ArrowDown);
  app.component('el-icon-plus', Plus);
  app.component('el-icon-minus', Minus);
  app.component('el-icon-check', Check);
  app.component('el-icon-close', Close);
  app.component('el-icon-edit', Edit);
  app.component('el-icon-arrow-up', ArrowUp);
  app.component('el-icon-circle-check', CircleCheck);
  app.component('el-icon-warning', Warning);
  
  // Mount the app
  app.mount('#htpm-vue-app');
});