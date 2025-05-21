<template>
  <main-layout>
    <router-view v-slot="{ Component }">
      <keep-alive>
        <component :is="Component" />
      </keep-alive>
    </router-view>
  </main-layout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue'
</script>
