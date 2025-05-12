<template>
  <div class="htpm-dashboard">
    <!-- Header -->
    <Header />
    <el-row :gutter="30" class="htpm-content">
      <el-col :span="19" :xs="24" :sm="24" :md="24" :lg="18" :xl="19" style="padding-bottom: 30px;">
        <slot></slot>
      </el-col>
      <el-col :span="5" :xs="24" :sm="24" :md="24" :lg="6" :xl="5">
        <!-- Sidebar -->
        <sidebar />
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Header from '../components/Header.vue'
import Sidebar from '../components/Sidebar.vue'

</script>

<style lang="scss">
div#htpm-app {
  width: 100%;
  display: flex;
}  
.htpm-dashboard {
  min-height: calc(100vh - 32px);
  padding: 10px 15px;
  width: 100%;
}
</style>
