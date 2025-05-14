<template>
  <div class="plugin-settings-skeleton">
    <div class="header-skeleton">
      <el-skeleton animated>
        <el-skeleton-item variant="h3" style="width: 40%; margin-bottom: 20px;" />
      </el-skeleton>
    </div>

    <!-- Sections -->
    <el-skeleton animated>
      <!-- Disable Plugin Section -->
      <div class="form-field-skeleton">
        <el-skeleton-item variant="text" style="width: 30%; margin-bottom: 8px;" />
        <el-skeleton-item variant="button" style="width: 100%; height: 40px;" />
        <el-skeleton-item variant="text" style="width: 70%; margin-top: 5px; height: 35px;" />
      </div>

      <!-- Action Section -->
      <div class="form-field-skeleton">
        <div class="label-with-badge">
          <el-skeleton-item variant="text" style="width: 20%; margin-bottom: 8px;" />
          <el-skeleton-item variant="button" style="width: 40px; height: 20px; border-radius: 10px;" />
        </div>
        <el-skeleton-item variant="button" style="width: 100%; height: 40px;" />
        <el-skeleton-item variant="text" style="width: 85%; margin-top: 5px; height: 16px;" />
      </div>

      <!-- Page Type Section -->
      <div class="form-field-skeleton">
        <el-skeleton-item variant="text" style="width: 25%; margin-bottom: 8px;" />
        <el-skeleton-item variant="button" style="width: 100%; height: 40px;" />
        <el-skeleton-item variant="text" style="width: 90%; margin-top: 5px; height: 16px;" />
      </div>

      <!-- Select Pages Section -->
      <div class="form-field-skeleton">
        <el-skeleton-item variant="text" style="width: 28%; margin-bottom: 8px;" />
        <div class="select-tags-skeleton">
          <div class="tag-skeleton">
            <el-skeleton-item variant="button" style="width: 80px; height: 24px; border-radius: 4px;" />
          </div>
          <div class="tag-skeleton">
            <el-skeleton-item variant="button" style="width: 60px; height: 24px; border-radius: 4px;" />
          </div>
          <div class="tag-skeleton">
            <el-skeleton-item variant="button" style="width: 100px; height: 24px; border-radius: 4px;" />
          </div>
          <el-skeleton-item variant="button" style="flex: 1; height: 40px;" />
        </div>
      </div>

      <!-- Select Posts Section -->
      <div class="form-field-skeleton">
        <el-skeleton-item variant="text" style="width: 25%; margin-bottom: 8px;" />
        <el-skeleton-item variant="button" style="width: 100%; height: 40px;" />
      </div>

      <!-- Bottom Actions -->
      <div class="actions-skeleton">
        <el-skeleton-item variant="button" style="width: 80px; height: 36px; margin-right: 10px;" />
        <el-skeleton-item variant="button" style="width: 120px; height: 36px;" />
      </div>
    </el-skeleton>
  </div>
</template>

<script setup>
import { ElSkeleton, ElSkeletonItem } from 'element-plus'
</script>

<style scoped>

.form-field-skeleton {
  margin-bottom: 24px;
}

.header-skeleton {
  margin-bottom: 24px;
}

:deep(.el-skeleton) {
  display: block;
}

:deep(.el-skeleton-item) {
  display: block;
  margin-bottom: 8px;
}

.label-with-badge {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.select-tags-skeleton {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  padding: 5px 10px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  min-height: 40px;
}

.tag-skeleton {
  display: inline-flex;
}

.actions-skeleton {
  display: flex;
  justify-content: flex-end;
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid #ebeef5;
}
</style>