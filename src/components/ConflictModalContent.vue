<template>
  <div class="conflict-modal-content">
    <div class="form-field">
      <label>{{ modalSettingsFields?.conflict_plugins?.label }} <span class="pro-badge" v-if="modalSettingsFields?.conflict_plugins.pro">{{proLabel}}</span></label>
      <div class="field-desc">{{ modalSettingsFields?.conflict_plugins?.description }}</div>
      
      <!-- Coming Soon feature placeholder -->
      <div class="coming-soon-placeholder">
        <div class="coming-soon-content">
          <div class="coming-soon-icon">
            <el-icon size="48"><Clock /></el-icon>
          </div>
          <h3>{{ lebelsText?.coming_soon?.title }}</h3>
          <p>{{ lebelsText?.coming_soon?.desc }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Clock, Trophy } from '@element-plus/icons-vue'
import { usePluginStore } from '../store/plugins'
import { ref } from 'vue'
const store = usePluginStore()
const props = defineProps({
  pluginSettings: {
    type: Object,
    required: true
  },
  modalSettingsFields: {
    type: Object,
    required: true
  },
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

const lebelsText = ref(store.labels_texts);
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

.coming-soon-placeholder {
  padding: 40px 20px;
  text-align: center;
  background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%);
  border-radius: 16px;
  border: 2px dashed #e6ebf5;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    
  &::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.03) 0%, rgba(99, 102, 241, 0) 70%);
    animation: pulse 3s ease-in-out infinite;
  }
    
  .coming-soon-content {
    position: relative;
    
    .coming-soon-icon {
      margin-bottom: 10px;
      color: #6366f1;
      opacity: 0.9;
      transform-origin: center;
      animation: float 3s ease-in-out infinite;
      
      .el-icon {
        filter: drop-shadow(0 4px 12px rgba(99, 102, 241, 0.2));
      }
    }
    
    h3 {
      margin: 0 0 16px 0;
      font-size: 28px;
      font-weight: 700;
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.02em;
      line-height: 1.3;
    }
    
    > p {
      margin: 0 0 32px 0;
      font-size: 16px;
      color: #64748b;
      line-height: 1.6;
      max-width: 400px;
      margin-left: auto;
      margin-right: auto;
    }
  }
  
  .el-button {
    font-size: 16px;
    font-weight: 600;
    padding: 12px 24px;
    height: auto;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border: none;
    transition: all 0.3s ease;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    
    .el-icon {
      margin-right: 8px;
    }
  }
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.02); opacity: 0.3; }
  100% { transform: scale(1); opacity: 0.5; }
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0px); }
}
</style>