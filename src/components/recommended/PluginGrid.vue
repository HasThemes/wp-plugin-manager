<template>
    <div class="htpm-plugin-grid">
        <el-row :gutter="20">
            <el-skeleton v-if="isLoading" :rows="6" animated />
            <el-col v-else :span="12" :md="12" :sm="12" :xs="24" v-for="plugin in pluginList" :key="plugin.id">
                <el-card class="htpm-plugin-info-card mb-4" shadow="hover" style="box-shadow: none;">
                    <div class="htpm-plugin-content">
                        <div class="htpm-plugin-logo">
                            <el-image 
                                :src="getPluginIcon(plugin)" 
                                :alt="plugin.name"
                                class="htpm-plugin-image"
                                fit="cover"
                                :initial-index="1"
                                :preview-src-list="[getPluginIcon(plugin)]"
                            >
                                <template #placeholder>
                                    <div class="image-slot">
                                        <img :src="getPluginIcon(plugin)" :alt="plugin.name" style="width: 100%; height: 100%; object-fit: cover;" />
                                    </div>
                                </template>
                            </el-image>
                        </div>
                        
                        <div class="htpm-plugin-info">
                            <h3 class="htpm-plugin-title" v-html="decodeHtmlEntities(plugin.name)"></h3>
                            <p class="htpm-plugin-description" v-html="trimWords(plugin.short_description || plugin.description, 23)"></p>
                        </div>
                    </div>
                    <template #footer>
                        <div class="htpm-plugin-footer">
                            <div class="htpm-plugin-installation-info">
                                <el-icon v-if="plugin.active_installs"><InfoFilled /></el-icon>
                                <span v-if="plugin.active_installs">{{ `${activeInstallCount(plugin.active_installs)} Active Installations` }}</span>
                                <el-link v-if="plugin.pro" type="primary" class="htpm-plugin-more-details" :href="plugin?.link" target="_blank">More Details</el-link>
                                <el-link v-else type="primary" class="thickbox open-plugin-details-modal" :href="`${htpmLocalizeData.adminURL}plugin-install.php?tab=plugin-information&plugin=${plugin.slug}&TB_iframe=true&width=772&height=577`" >More Details</el-link>
                            </div>

                            <el-button 
                                type="primary"
                                :loading="plugin.isLoading"
                                :disabled="store.isButtonDisabled(plugin)"
                                @click="toggleActivation(plugin)"
                            >
                                {{ store.getButtonText(plugin) }}
                            </el-button>
                        </div>
                    </template>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { trimWords } from '@/utils/helpers';

const decodeHtmlEntities = (text) => {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = text;
    return textarea.value;
};
import { InfoFilled } from '@element-plus/icons-vue';
import { useRecommendedPluginsStore } from '@/store/modules/recommendedPlugins';

const store = useRecommendedPluginsStore();
const htpmLocalizeData = ref(window.HTPMM || {});

// Get assets URL from store if available
const getPluginIcon = (plugin) => {
    if (plugin.icon) return plugin.icon;
    if (plugin.icons && plugin.icons['2x']) return plugin.icons['2x'];
    if (plugin.icons && plugin.icons['1x']) return plugin.icons['1x'];
    if (plugin.icons && plugin.icons.default) return plugin.icons.default;
    // Try to get from assets URL if available
    if (htpmLocalizeData.value?.assetsURL) {
        return `${htpmLocalizeData.value.assetsURL}/images/extensions/${plugin.slug}.png`;
    }
    return `${htpmLocalizeData.value.assetsURL}/images/logo.jpg`;
};

const activeInstallCount = (count) => {
    if (!count) return '0';
    if (count >= 1000000) return Math.floor(count / 1000000) + 'M+';
    if (count >= 1000) return Math.floor(count / 1000) + 'K+';
    return count + '+';
};

const props = defineProps({
    pluginList: {
        type: Array,
        required: true,
        default: () => []
    },
    isLoading: {
        type: Boolean,
        default: false
    }
});

const toggleActivation = async (plugin) => {
    if (plugin.status?.toLowerCase() === 'not_installed' && plugin?.pro) {
        window.open(plugin.link, '_blank');
        return;
    }
    try {
        await store.handlePluginAction(plugin);
    } catch (error) {
        console.error('Error handling plugin action:', error);
    }
};


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
  line-height:24px;
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