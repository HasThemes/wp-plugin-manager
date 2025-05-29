<template>
    <div class="conflict-modal-content">
       <!-- Status Field -->
       <!-- <div class="form-field">
        <label>{{ modalSettingsFields?.status?.label || 'Status' }}</label>
        <el-switch
          v-model="pluginSettings.conflict_status"
          class="status-switch"
          :active-text="'Enabled'"
          :inactive-text="'Disabled'"
        />
        <div class="field-desc">{{ modalSettingsFields?.status?.description }}</div>
      </div> -->
      <div class="form-field">
        <label>Conflicting Plugins <span v-if="!isPro" class="pro-badge">{{proLabel}}</span></label>
        <div class="field-desc">Select plugins that conflict with this plugin. The plugin will be disabled if any of the selected plugins are active.</div>
        
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
        <div v-else class="conflict-selection">
          <el-select 
            v-model="conflictingPlugins" 
            multiple 
            filterable 
            class="w-full"
            placeholder="Select conflicting plugins..."
          >
            <el-option 
              v-for="plugin in availablePlugins" 
              :key="plugin.id" 
              :label="plugin.name" 
              :value="plugin.id"
            />
          </el-select>
          
          <div class="selected-conflicts" v-if="conflictingPlugins.length > 0">
            <h4>Selected Conflicting Plugins:</h4>
            <div class="conflict-list">
              <el-tag 
                v-for="pluginId in conflictingPlugins" 
                :key="pluginId"
                closable
                @close="removeConflict(pluginId)"
              >
                {{ getPluginName(pluginId) }}
              </el-tag>
            </div>
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
    },
    availablePlugins: {
      type: Array,
      default: () => []
    }
  })
  
  const emit = defineEmits(['openProModal'])
  
  const conflictingPlugins = ref([])
  
  const openProModal = () => {
    emit('openProModal')
  }
  
  const removeConflict = (pluginId) => {
    const index = conflictingPlugins.value.indexOf(pluginId)
    if (index > -1) {
      conflictingPlugins.value.splice(index, 1)
    }
  }
  
  const getPluginName = (pluginId) => {
    const plugin = props.availablePlugins.find(p => p.id === pluginId)
    return plugin ? plugin.name : `Plugin ${pluginId}`
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
  
  .conflict-selection {
    .selected-conflicts {
      margin-top: 20px;
      
      h4 {
        margin: 0 0 12px 0;
        font-size: 14px;
        font-weight: 500;
        color: #606266;
      }
      
      .conflict-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        
        .el-tag {
          margin: 0;
        }
      }
    }
  }
  
  .w-full {
    width: 100%;
  }
  </style>