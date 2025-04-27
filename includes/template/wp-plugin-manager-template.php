<?php
/**
 * WP Plugin Manager - Vue.js Template
 * 
 * This template file contains the HTML structure for the Vue.js application
 */

// If this file is called directly, abort
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap htpm-vue-wrapper">
    <?php do_action('htpm_admin_notices'); ?>
    
    <div id="htpm-vue-app">
        <!-- Vue.js app will be mounted here -->
        <div class="htpm-loading" v-if="!initialized">
            <div class="htpm-spinner"></div>
            <p><?php echo esc_html__('Loading Plugin Manager...', 'wp-plugin-manager'); ?></p>
        </div>
    </div>
</div>

<style>
    .htpm-loading {
        text-align: center;
        padding: 40px 0;
    }
    
    .htpm-spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        border-top-color: #4d5bf9;
        animation: htpm-spin 1s ease-in-out infinite;
        margin-bottom: 10px;
    }
    
    @keyframes htpm-spin {
        to { transform: rotate(360deg); }
    }
</style>


<!-- wp-plugin-manager-template.php -->

<div id="htpm-vue-app" class="htpm-vue-dashboard">
  <!-- Loading State -->
  <el-skeleton :loading="loading" animated :count="4" v-if="loading">
    <template #template>
      <div style="padding: 20px;">
        <el-skeleton-item variant="p" style="width: 100%; height: 60px; margin-bottom: 20px;"></el-skeleton-item>
        <el-skeleton-item variant="p" style="width: 100%; height: 120px;"></el-skeleton-item>
      </div>
    </template>
  </el-skeleton>

  <!-- Main Dashboard when loaded -->
  <template v-else>
    <!-- Top Navigation Bar -->
    <div class="htpm-top-navbar">
      <div class="htpm-nav-links">
        <a href="#" 
           :class="['htpm-nav-link', { 'htpm-active': currentTab === 'general' }]" 
           @click.prevent="currentTab = 'general'">
          <el-icon><setting /></el-icon> General
        </a>
        <a href="#" 
           :class="['htpm-nav-link', { 'htpm-active': currentTab === 'settings' }]" 
           @click.prevent="currentTab = 'settings'">
          <el-icon><setting /></el-icon> Settings
        </a>
        <a href="#" 
           :class="['htpm-nav-link', { 'htpm-active': currentTab === 'tools' }]" 
           @click.prevent="currentTab = 'tools'">
          <el-icon><tools /></el-icon> Tools
        </a>
        <a href="#" 
           :class="['htpm-nav-link', { 'htpm-active': currentTab === 'license' }]" 
           @click.prevent="currentTab = 'license'">
          <el-icon><key /></el-icon> License
        </a>
        <a href="#" 
           :class="['htpm-nav-link', { 'htpm-active': currentTab === 'documentation' }]" 
           @click.prevent="currentTab = 'documentation'">
          <el-icon><document /></el-icon> Documentation
        </a>
        <a href="#" 
           :class="['htpm-nav-link', { 'htpm-active': currentTab === 'support' }]" 
           @click.prevent="currentTab = 'support'">
          <el-icon><question-filled /></el-icon> Support
        </a>
      </div>
      
      <div class="htpm-nav-actions">
        <el-button type="primary" class="htpm-upgrade-btn">
          Upgrade to Pro
        </el-button>
        
        <div class="htpm-notification-wrapper">
          <el-badge :value="notifications.length" :hidden="notifications.length === 0">
            <el-button class="htpm-notification-btn" circle @click="toggleNotifications">
              <el-icon><bell /></el-icon>
            </el-button>
          </el-badge>
          
          <!-- Notifications Dropdown -->
          <div class="htpm-notifications-dropdown" v-if="notificationsVisible">
            <h3 class="htpm-notifications-title">Notifications</h3>
            <div v-if="notifications.length === 0" class="htpm-no-notifications">
              No new notifications
            </div>
            <ul v-else class="htpm-notifications-list">
              <li v-for="(notification, index) in notifications" :key="index" class="htpm-notification-item">
                <div class="htpm-notification-content">
                  <h4>{{ notification.title }}</h4>
                  <p>{{ notification.message }}</p>
                  <span class="htpm-notification-time">{{ notification.time }}</span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Stats Row -->
    <div class="htpm-stats-row">
      <el-card class="htpm-stat-card">
        <div class="htpm-stat-icon htpm-blue">
          <el-icon><document /></el-icon>
        </div>
        <div class="htpm-stat-info">
          <div class="htpm-stat-value">{{ stats.total }}</div>
          <div class="htpm-stat-label">Total Plugins</div>
        </div>
      </el-card>
      
      <el-card class="htpm-stat-card">
        <div class="htpm-stat-icon htpm-green">
          <el-icon><circle-check /></el-icon>
        </div>
        <div class="htpm-stat-info">
          <div class="htpm-stat-value">{{ stats.active }}</div>
          <div class="htpm-stat-label">Active Plugins</div>
        </div>
      </el-card>
      
      <el-card class="htpm-stat-card">
        <div class="htpm-stat-icon htpm-yellow">
          <el-icon><warning /></el-icon>
        </div>
        <div class="htpm-stat-info">
          <div class="htpm-stat-value">{{ stats.updates }}</div>
          <div class="htpm-stat-label">Update Available</div>
        </div>
      </el-card>
      
      <el-card class="htpm-stat-card">
        <div class="htpm-stat-icon htpm-red">
          <el-icon><close /></el-icon>
        </div>
        <div class="htpm-stat-info">
          <div class="htpm-stat-value">{{ stats.inactive }}</div>
          <div class="htpm-stat-label">Inactive Plugins</div>
        </div>
      </el-card>
    </div>
    
    <!-- Plugins Section -->
    <el-card class="htpm-plugins-section">
      <template #header>
        <div class="htpm-plugins-header">
          <h2 class="htpm-plugins-title">
            <el-icon><document /></el-icon>
            Manage Plugins
          </h2>
          
          <div class="htpm-plugins-actions">
            <el-input 
              v-model="searchQuery" 
              placeholder="Search plugins..." 
              class="htpm-search-input"
              prefix-icon="Search"
            />
            
            <el-dropdown trigger="click">
              <el-button type="default" class="htpm-filter-button">
                <el-icon><filter /></el-icon>
                Filter
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item @click="filterPlugins('all')">All Plugins</el-dropdown-item>
                  <el-dropdown-item @click="filterPlugins('active')">Active Plugins</el-dropdown-item>
                  <el-dropdown-item @click="filterPlugins('inactive')">Inactive Plugins</el-dropdown-item>
                  <el-dropdown-item @click="filterPlugins('updates')">Update Available</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
            
            <el-dropdown trigger="click">
              <el-button type="default" class="htpm-sort-button">
                <el-icon><arrow-down /></el-icon>
                Sort
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item @click="sortPlugins('name')">Sort by Name</el-dropdown-item>
                  <el-dropdown-item @click="sortPlugins('status')">Sort by Status</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </template>
      
      <!-- Plugins List -->
      <div class="htpm-plugins-list">
        <div v-for="plugin in filteredPlugins" :key="plugin.id" class="htpm-plugin-item">
          <div class="htpm-plugin-icon" :style="{ backgroundColor: plugin.iconColor || '#4d5bf9' }">
            <el-icon>
              <component :is="plugin.icon || 'Document'" />
            </el-icon>
          </div>
          
          <div class="htpm-plugin-info">
            <div class="htpm-plugin-name">{{ plugin.name }}</div>
            <div :class="['htpm-plugin-status', plugin.active ? 'htpm-status-active' : 'htpm-status-inactive']">
              <i class="htpm-status-dot"></i>
              {{ plugin.active ? 'Active' : 'Inactive' }}
            </div>
          </div>
          
          <div class="htpm-plugin-actions">
            <el-switch
              v-model="plugin.active"
              @change="togglePluginState(plugin)"
              active-color="#00a32a"
            />
            
            <el-button 
              class="htpm-settings-icon" 
              circle 
              @click="openSettings(plugin)"
            >
              <el-icon><setting /></el-icon>
            </el-button>
          </div>
        </div>
      </div>
    </el-card>
    
    <!-- Footer Help Section -->
    <el-card class="htpm-footer-help">
      <div class="htpm-help-icon">
        <el-icon><question-filled /></el-icon>
      </div>
      <div class="htpm-help-content">
        <h3 class="htpm-help-title">Need Help with Plugin Manager?</h3>
        <p class="htpm-help-text">Our comprehensive documentation provides detailed information on how to use Plugin Manager effectively to improve your website's performance.</p>
        <div class="htpm-help-links">
          <a href="#" class="htpm-help-link">
            <el-icon><document /></el-icon>
            Documentation
          </a>
          <a href="#" class="htpm-help-link">
            <el-icon><video-play /></el-icon>
            Video Tutorial
          </a>
          <a href="#" class="htpm-help-link">
            <el-icon><chat-dot-square /></el-icon>
            Support
          </a>
        </div>
      </div>
    </el-card>
  </template>
  
  <!-- Plugin Settings Dialog -->
  <el-dialog
    v-model="settingsDialogVisible"
    title="Plugin Settings"
    width="650px"
    :close-on-click-modal="false"
    custom-class="htpm-settings-dialog"
  >
    <template v-if="selectedPlugin">
      <el-form label-position="top">
        <!-- Enable Deactivation -->
        <el-form-item label="Disable This Plugin:">
          <el-switch
            v-model="selectedPlugin.enable_deactivation"
            active-value="yes"
            inactive-value="no"
            active-text="Yes"
            inactive-text="No"
            active-color="#00a32a"
          />
        </el-form-item>
        
        <template v-if="selectedPlugin.enable_deactivation === 'yes'">
          <!-- Device Type -->
          <el-form-item label="Disable Plugin on:">
            <el-tooltip
              content="Select the device(s) where this plugin should be disabled."
              placement="top"
            >
              <el-select v-model="selectedPlugin.device_type" class="htpm-full-width">
                <el-option
                  v-for="option in deviceTypes"
                  :key="option.value"
                  :label="option.label"
                  :value="option.value"
                />
              </el-select>
            </el-tooltip>
          </el-form-item>
          
          <!-- Condition Type -->
          <el-form-item label="Action:">
            <el-tooltip
              content="Disable on Selected Pages refers to the pages where the plugin will be disabled and enabled elsewhere."
              placement="top"
            >
              <el-select v-model="selectedPlugin.condition_type" class="htpm-full-width">
                <el-option
                  v-for="option in conditionTypes"
                  :key="option.value"
                  :label="option.label"
                  :value="option.value"
                />
              </el-select>
            </el-tooltip>
          </el-form-item>
          
          <!-- URI Type -->
          <el-form-item label="Page Type:">
            <el-tooltip
              content="Choose the types of pages. 'Custom' allows you to specify pages matching a particular URI pattern."
              placement="top"
            >
              <el-select v-model="selectedPlugin.uri_type" class="htpm-full-width">
                <el-option
                  v-for="option in uriTypes"
                  :key="option.value"
                  :label="option.label"
                  :value="option.value"
                />
              </el-select>
              <p class="htpm-settings-info" v-if="selectedPlugin.uri_type === 'page_post_cpt'">
                If you wish to select custom posts, please choose the custom post through the "Select Post Types" option.
              </p>
            </el-tooltip>
          </el-form-item>
          
          <!-- Post Types Selection -->
          <el-form-item label="Select Post Types:" v-if="canShowPostTypes">
            <el-checkbox-group v-model="selectedPlugin.post_types">
              <el-checkbox label="post">Post</el-checkbox>
              <el-checkbox label="page">Page</el-checkbox>
              <!-- Custom post types would be dynamically added here -->
            </el-checkbox-group>
          </el-form-item>
          
          <!-- Pages Selection -->
          <el-form-item label="Select Pages:" v-if="canShowPageSelection">
            <el-select 
              v-model="selectedPlugin.pages" 
              multiple 
              filterable 
              placeholder="Select pages"
              class="htpm-full-width"
            >
              <el-option
                key="all_pages,all_pages"
                label="All Pages"
                value="all_pages,all_pages"
              />
              <!-- Dynamic page options would be added here -->
            </el-select>
          </el-form-item>
          
          <!-- Posts Selection -->
          <el-form-item label="Select Posts:" v-if="canShowPostSelection">
            <el-select 
              v-model="selectedPlugin.posts" 
              multiple 
              filterable 
              placeholder="Select posts"
              class="htpm-full-width"
            >
              <el-option
                key="all_posts,all_posts"
                label="All Posts"
                value="all_posts,all_posts"
              />
              <!-- Dynamic post options would be added here -->
            </el-select>
          </el-form-item>
          
          <!-- Custom URI Conditions -->
          <el-form-item v-if="canShowCustomConditions">
            <el-table :data="selectedPlugin.condition_list.name.map((name, index) => ({
              name,
              value: selectedPlugin.condition_list.value[index],
              index
            }))" border class="htpm-conditions-table">
              <el-table-column label="URI Condition" width="250">
                <template #default="scope">
                  <el-select v-model="selectedPlugin.condition_list.name[scope.row.index]" class="htpm-full-width">
                    <el-option
                      v-for="option in uriConditionOptions"
                      :key="option.value"
                      :label="option.label"
                      :value="option.value"
                    />
                  </el-select>
                </template>
              </el-table-column>
              
              <el-table-column label="Value">
                <template #default="scope">
                  <el-input 
                    v-model="selectedPlugin.condition_list.value[scope.row.index]"
                    placeholder="e.g: contact-us"
                  >
                    <template #suffix>
                      <el-tooltip content="e.g: You can use 'contact-us' on URLs like https://example.com/contact-us or leave it blank for the homepage">
                        <el-icon><question-filled /></el-icon>
                      </el-tooltip>
                    </template>
                  </el-input>
                </template>
              </el-table-column>
              
              <el-table-column label="Action" width="170">
                <template #default="scope">
                  <el-button 
                    type="danger" 
                    @click="removeCondition(scope.row.index)"
                    :disabled="selectedPlugin.condition_list.name.length <= 1"
                  >
                    Remove
                  </el-button>
                  <el-button type="primary" @click="addCondition">
                    Add
                  </el-button>
                </template>
              </el-table-column>
            </el-table>
          </el-form-item>
        </template>
      </el-form>
    </template>
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="settingsDialogVisible = false">Cancel</el-button>
        <el-button type="primary" @click="savePluginSettings" :loading="savingSettings">
          Save
        </el-button>
      </span>
    </template>
  </el-dialog>
  
  <!-- Message Notifications -->
  <el-notification
    v-if="errorMessage"
    title="Error"
    :message="errorMessage"
    type="error"
    @close="errorMessage = ''"
  />
  
  <el-notification
    v-if="successMessage"
    title="Success"
    :message="successMessage"
    type="success"
    @close="successMessage = ''"
  />
</div>