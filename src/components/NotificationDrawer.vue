<template>
    <el-drawer
        v-model="visible"
        title="Changelog"
        direction="rtl"
        size="500px"
        :before-close="handleClose"
        class="htpm-notification-drawer"
        style="top: 32px !important; height: calc(100vh - 32px) !important;"
    >
        <template #header>
            <h3 class="drawer-title">What's New in Plugin Manager</h3>
        </template>

        <el-scrollbar height="calc(100vh - 150px)">
            <div class="changelog-content" v-loading="store.changelogLoading">
                <template v-if="store.changelog && store.changelog.length">
                    <div v-for="(item, index) in store.changelog" :key="index" class="changelog-item">
                        <div class="version-header">
                            <h4>Version {{ item.version }}</h4>
                            <span class="release-date">{{ item.date }}</span>
                        </div>
                        <div class="changelog-details">
                            <template v-if="item.changes">
                                <div v-for="(changes, type) in item.changes" :key="type" class="change-type">
                                    <h5>{{ type }}:</h5>
                                    <ul>
                                        <li v-for="(change, idx) in changes" :key="idx">
                                            {{ change }}
                                        </li>
                                    </ul>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                <el-empty v-else description="No changelog available" />
            </div>
        </el-scrollbar>
        
    </el-drawer>
</template>

<script setup>
import { computed, watch } from 'vue'
import { usePluginStore } from '../store/plugins'

const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true
    }
})

const emit = defineEmits(['update:modelValue'])

const store = usePluginStore()

const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

// Watch for drawer opening
watch(visible, async (newValue) => {
    if (newValue) {
        // Only fetch if we don't have changelog data
        if (!store.changelog?.length) {
            await store.fetchChangelog()
            // Only mark as read if we just loaded new data
            if (!store.changelogRead) {
                store.markChangelogRead()
            }
        }
    }
})

const handleClose = (done) => {
    done()
}
</script>

<style lang="scss" scoped>
.htpm-notification-drawer {
    .drawer-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .changelog-content {
        padding: 20px;

        .changelog-item {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;

            &:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }

            .version-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;

                h4 {
                    margin: 0;
                    font-size: 16px;
                    font-weight: 600;
                }

                .release-date {
                    color: #666;
                    font-size: 14px;
                }
            }

            .changelog-details {
                .change-type {
                    margin-bottom: 15px;

                    h5 {
                        margin: 0 0 10px;
                        font-size: 14px;
                        font-weight: 600;
                        color: #333;
                    }

                    ul {
                        margin: 0;
                        padding-left: 20px;

                        li {
                            margin-bottom: 5px;
                            color: #666;
                            font-size: 14px;

                            &:last-child {
                                margin-bottom: 0;
                            }
                        }
                    }
                }
            }
        }
    }
}
</style>