<template>
    <div class="htpm-plugin-grid">
        <el-row :gutter="20">
            <el-skeleton v-if="isLoading" :rows="6" animated />
            <el-col v-else :span="12" :md="12" :sm="12" :xs="24" v-for="plugin in pluginList" :key="plugin.id">
                <el-card class="htpm-plugin-info-card mb-4" shadow="hover">
                    <!-- Rest of the template stays the same, just change 'plugins' to 'pluginList' -->
                    <div class="htpm-plugin-content">
                        <div class="htpm-plugin-logo">
                            <el-image 
                                :src="plugin.icon" 
                                :alt="plugin.name"
                                class="htpm-plugin-image"
                            />
                        </div>
                        
                        <div class="htpm-plugin-info">
                            <h3 class="htpm-plugin-title">{{ plugin.name }}</h3>
                            <p class="htpm-plugin-description" v-html="trimWords(plugin.description, 23)"></p>
                        </div>
                    </div>
                    <template #footer>
                        <div class="htpm-plugin-footer">
                            <div class="htpm-plugin-installation-info">
                                <el-icon v-if="plugin.active_installs"><InfoFilled /></el-icon>
                                <span v-if="plugin.active_installs">{{ `${activeInstallCount(plugin.active_installs)} Active Installations` }}</span>
                                <el-link v-if="plugin.is_pro" type="primary" class="htpm-plugin-more-details" :href="plugin?.link" target="_blank">More Details</el-link>
                                <!-- <el-link v-else type="primary" class="thickbox open-plugin-details-modal" :href="`${this.htpmLocalizeData.adminUrl}plugin-install.php?tab=plugin-information&plugin=${plugin.slug}&TB_iframe=true&width=772&height=577`" >More Details</el-link> -->
                            </div>

                            <el-button 
                                type="primary"
                                :loading="plugin.isLoading"
                                :disabled="plugin.status === pluginStates.ACTIVE || 
                                        plugin.status === pluginStates.INSTALLING || 
                                        plugin.status === pluginStates.ACTIVATING"
                                @click="toggleActivation(plugin)"
                            >
                                {{ plugin.status === pluginStates.NOT_INSTALLED && plugin?.is_pro ? 'Buy Now' : getPluginButtonText(plugin.status, plugin.isLoading) }}
                            </el-button>
                        </div>
                    </template>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script>
import { trimWords } from '@/utils/helpers';
import { InfoFilled } from '@element-plus/icons-vue';

export default {
    name: 'PluginGrid',
    components: {
        InfoFilled
    },
    props: {
        pluginList: {
            type: Array,
            required: true,
            default: () => []
        },
        isLoading: {
            type: Boolean,
            default: false
        },
        pluginStates: {
            type: Object,
            default: () => {}
        },
        getPluginButtonText: {
            type: Function,
            default: (status, isLoading) => ''
        },
    },
    methods: {
        toggleActivation(plugin) {
            if( plugin.status === this.pluginStates.NOT_INSTALLED && plugin?.is_pro ){
                window.open(plugin.link, '_blank');
                return;
            }else{
                this.$emit('plugin-toggled', plugin)
            }
        },

        trimWords(text, limit) {
            return trimWords(text, limit);
        },

        activeInstallCount(activeInstalls) {
            let activeInstallsText;

            if (activeInstalls >= 1000000) {
                const activeInstallsMillions = Math.floor(activeInstalls / 1000000);
                activeInstallsText = `${activeInstallsMillions}+ Million`;
            } else if (activeInstalls === 0) {
                activeInstallsText = 'Less Than 10';
            } else {
                activeInstallsText = `${activeInstalls.toLocaleString()}+`;
            }

            return activeInstallsText;
        },
    }
}
</script>

<style scoped>
.htpm-plugin-info-card {
  margin-bottom: 20px;
  border: 1px solid #ebeef5;
}

.htpm-plugin-content {
  display: flex;
  gap: 20px;
}

.htpm-plugin-logo {
  flex-shrink: 0;
}

.htpm-plugin-image {
  width: 80px;
  height: 80px;
  border-radius: 8px;
}

.htpm-plugin-info {
  flex: 1;
}

.htpm-plugin-title {
  font-size: 18px;
  font-weight: 500;
  margin: 0 0 10px 0;
  color: #303133;
}

.htpm-plugin-description {
  font-size: 14px;
  color: #606266;
  margin-bottom: 15px;
  line-height: 1.5;
}

.htpm-plugin-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.htpm-plugin-installation-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #909399;
}

.htpm-plugin-more-details {
  margin-left: 8px;
}

:deep(.el-card__body) {
  padding: 20px;
}
</style>