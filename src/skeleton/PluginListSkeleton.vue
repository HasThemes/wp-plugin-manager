<template>
    <div class="plugin-list-skeleton">
      <el-skeleton v-for="n in 7" :key="n" animated class="plugin-row">
        <template #template>
          <div class="plugin-item">
            <!-- Plugin Icon -->
            <el-skeleton-item
              variant="circle"
              style="width: 40px; height: 40px;"
            />
  
            <!-- Plugin Info -->
            <div class="plugin-info">
              <el-skeleton-item variant="text" style="width: 200px;" />
              <el-skeleton-item variant="text" style="width: 120px; margin-top: 6px;" />
            </div>
  
            <!-- Toggle + Settings Button -->
            <div class="plugin-actions">
              <el-skeleton-item variant="button" style="width: 48px; height: 20px;" />
              <el-skeleton-item
                variant="circle"
                style="width: 28px; height: 28px; margin-left: 10px;"
              />
            </div>
          </div>
        </template>
      </el-skeleton>
    </div>
  </template>
  
  <script setup>
  import { ElSkeleton, ElSkeletonItem } from 'element-plus'
  </script>
  
  <style scoped>

  .plugin-row {
    margin-bottom: 20px;
  }
  
  .plugin-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #ddd;
    padding: 15px 15px 0;
}
  
  .plugin-info {
    flex: 1;
    margin-left: 15px;
    flex-direction: column;
    display: flex;
  }
  
  .plugin-actions {
    display: flex;
    align-items: center;
  }
  </style>
  