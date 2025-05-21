<template>
  <div class="settings-skeleton">
    <!-- Post Types Settings Card -->
    <el-card shadow="never" class="mb-6">
      <el-skeleton :rows="4" animated />

      <!-- Custom field (slider) simulation -->
      <div class="custom-skeleton-row">
        <el-skeleton-item variant="text" style="width: 160px" />
        <div class="number-control">
          <el-skeleton-item variant="button" style="width: 32px; height: 32px" />
          <el-skeleton-item variant="text" style="width: 60px" />
          <el-skeleton-item variant="button" style="width: 32px; height: 32px" />
        </div>
      </div>

      <!-- Note -->
      <el-skeleton-item variant="text" style="width: 90%; height: 20px; margin-top: 20px;" />
    </el-card>

    <!-- Display Settings Card -->
    <el-card shadow="never" class="mb-6">
      <el-skeleton :rows="2" animated />

      <div class="display-toggle">
        <el-skeleton-item variant="text" style="width: 180px" />
        <el-skeleton-item variant="circle" style="width: 22px; height: 22px;" />
      </div>

      <el-skeleton-item variant="text" style="width: 220px; margin-top: 12px;" />
      <el-skeleton-item variant="rect" style="width: 180px; height: 32px; margin-top: 10px;" />
    </el-card>

    <!-- Save Button Skeleton -->
    <div class="save-button-skeleton">
      <el-skeleton-item variant="button" style="width: 130px; height: 36px;" />
    </div>
  </div>
</template>

<script setup>
import { ElSkeleton, ElSkeletonItem, ElCard } from 'element-plus'
</script>

<style scoped>

.custom-skeleton-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 20px;
}

.number-control {
  display: flex;
  align-items: center;
  gap: 10px;
}

.display-toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 20px;
}

.save-button-skeleton {
  display: flex;
  justify-content: flex-end;
  margin-top: 30px;
}
.mb-6 {
  margin-bottom: 24px;
}
</style>
