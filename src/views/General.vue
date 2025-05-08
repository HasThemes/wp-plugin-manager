<template>
    <div v-loading="isLoading">
        <!-- Stats Cards -->
        <stats-cards />
        <!-- Plugin List -->
        <plugin-list/>
        <HelpSection />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePluginStore } from '../store/plugins'
import StatsCards from '../components/StatsCards.vue'
import PluginList from '../components/PluginList.vue'
import HelpSection from '../components/HelpSection.vue'

const store = usePluginStore()
const isLoading = ref(false)

// Load plugins when component mounts
onMounted(async () => {
    try {
        await store.fetchPlugins()
    } finally {
        isLoading.value = false
    }
})
</script>

