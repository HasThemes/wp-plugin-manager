<template>
    <div class="login-status-modal-content">
      <!-- Status Field -->
      <!-- <div class="form-field">
        <label>{{ modalSettingsFields?.status?.label || 'Status' }}</label>
        <el-switch
          v-model="pluginSettings.login_status"
          class="status-switch"
          :active-text="'Enabled'"
          :inactive-text="'Disabled'"
        />
        <div class="field-desc">{{ modalSettingsFields?.status?.description }}</div>
      </div> -->
      <div class="form-field">
        <label>Login Requirement <span v-if="!isPro" class="pro-badge">{{proLabel}}</span></label>
        <div class="field-desc">Configure when the plugin should be active based on user login status.</div>
        
        <!-- Pro feature placeholder - will be implemented in future -->
        <div class="pro-feature-placeholder" v-if="!isPro">
          <el-empty 
            :image-size="100"
            description="This feature is available in the Pro version"
          >
            <el-button type="primary" @click="openProModal">
              Upgrade to Pro
            </el-button>
          </el-empty>
        </div>
        
        <!-- Pro version content -->
        <div v-else class="login-status-options">
          <el-radio-group v-model="loginRequirement" class="login-options">
            <el-radio label="always" size="large">
              <div class="radio-content">
                <div class="radio-title">Always Active</div>
                <div class="radio-desc">Plugin is active regardless of user login status</div>
              </div>
            </el-radio>
            
            <el-radio label="logged_in_only" size="large">
              <div class="radio-content">
                <div class="radio-title">Logged In Users Only</div>
                <div class="radio-desc">Plugin is only active for logged in users</div>
              </div>
            </el-radio>
            
            <el-radio label="logged_out_only" size="large">
              <div class="radio-content">
                <div class="radio-title">Logged Out Users Only</div>
                <div class="radio-desc">Plugin is only active for visitors who are not logged in</div>
              </div>
            </el-radio>
            
            <el-radio label="specific_roles" size="large">
              <div class="radio-content">
                <div class="radio-title">Specific User Roles</div>
                <div class="radio-desc">Plugin is active only for users with specific roles</div>
              </div>
            </el-radio>
          </el-radio-group>
          
          <!-- User Roles Selection -->
          <div v-if="loginRequirement === 'specific_roles'" class="form-field role-selection">
            <label>Select User Roles:</label>
            <el-checkbox-group v-model="selectedRoles" class="role-checkboxes">
              <el-checkbox v-for="role in userRoles" :key="role.value" :label="role.value">
                <div class="role-option">
                  <span class="role-name">{{ role.label }}</span>
                  <span class="role-desc">{{ role.description }}</span>
                </div>
              </el-checkbox>
            </el-checkbox-group>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  const props = defineProps({
    proLabel: {
      type: String,
      required: true
    },
    isPro: {
      type: Boolean,
      required: true
    }
  })
  
  const emit = defineEmits(['openProModal'])
  
  const loginRequirement = ref('always')
  const selectedRoles = ref([])
  
  // Common WordPress user roles
  const userRoles = ref([
    {
      value: 'administrator',
      label: 'Administrator',
      description: 'Full access to all features'
    },
    {
      value: 'editor',
      label: 'Editor',
      description: 'Can publish and manage posts and pages'
    },
    {
      value: 'author',
      label: 'Author',
      description: 'Can publish and manage own posts'
    },
    {
      value: 'contributor',
      label: 'Contributor',
      description: 'Can write and manage own posts but cannot publish'
    },
    {
      value: 'subscriber',
      label: 'Subscriber',
      description: 'Can only manage profile and read content'
    }
  ])
  
  const openProModal = () => {
    emit('openProModal')
  }
  </script>
  
  <style lang="scss" scoped>
  .pro-badge {
    background-color: rgba(214, 54, 56, 0.1);
    border: 1px solid #d636386b;
    color: #d63638;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 600;
    line-height: 1;
    text-transform: uppercase;
  }
  
  .form-field {
    margin-bottom: 20px;
    position: relative;
  
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #606266;
    }
  
    .field-desc {
      margin-top: 4px;
      margin-bottom: 16px;
      font-size: 12px;
      color: #909399;
    }
  }
  
  .pro-feature-placeholder {
    padding: 40px 20px;
    text-align: center;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #dee2e6;
  }
  
  .login-status-options {
    .login-options {
      display: flex;
      flex-direction: column;
      gap: 16px;
      
      :deep(.el-radio) {
        margin: 0;
        align-items: flex-start;
        
        .el-radio__input {
          margin-top: 4px;
        }
      }
    }
    
    .radio-content {
      margin-left: 8px;
      
      .radio-title {
        font-weight: 500;
        color: #303133;
        margin-bottom: 4px;
      }
      
      .radio-desc {
        font-size: 12px;
        color: #909399;
        line-height: 1.4;
      }
    }
  }
  
  .role-selection {
    margin-top: 20px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #409eff;
    
    .role-checkboxes {
      display: flex;
      flex-direction: column;
      gap: 12px;
      
      :deep(.el-checkbox) {
        margin: 0;
        align-items: flex-start;
        
        .el-checkbox__input {
          margin-top: 2px;
        }
      }
    }
    
    .role-option {
      margin-left: 8px;
      
      .role-name {
        font-weight: 500;
        color: #303133;
        display: block;
      }
      
      .role-desc {
        font-size: 11px;
        color: #909399;
        line-height: 1.3;
      }
    }
  }
  </style>