<template>
  <el-card class="plugin-recommendation-skeleton" shadow="never">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="Recommended" name="recommended" style="color: #2271b1;" />
      <el-tab-pane label="You May Also Like" name="you-may-also-like" />
    </el-tabs>

    <el-row :gutter="20" class="plugin-grid">
      <el-col :xs="24" :sm="12" v-for="n in 4" :key="n">
        <el-card class="plugin-card-skeleton" shadow="hover">
          <el-skeleton animated>
            <template #template>
              <div class="card-body">
                <div class="header">
                  <el-skeleton-item variant="image" style="width: 50px; height: 50px; border-radius: 6px;" />
                  <div class="plugin-info">
                    <el-skeleton-item variant="text" style="width: 80%;" />
                    <el-skeleton-item variant="text" style="width: 100%;" />
                  </div>
                </div>

                <div class="description">
                  <el-skeleton-item variant="text" style="width: 100%;" />
                  <el-skeleton-item variant="text" style="width: 95%;" />
                  <el-skeleton-item variant="text" style="width: 85%;" />
                </div>

                <div class="footer">
                  <el-skeleton-item variant="text" style="width: 40%;" />
                  <el-skeleton-item variant="button" style="width: 80px; height: 32px;" />
                </div>
              </div>
            </template>
          </el-skeleton>
        </el-card>
      </el-col>
    </el-row>
  </el-card>
</template>

<script setup>
import { ref } from 'vue'
import {
  ElCard,
  ElRow,
  ElCol,
  ElSkeleton,
  ElSkeletonItem,
  ElTabs,
  ElTabPane
} from 'element-plus'

const activeTab = ref('recommended')
</script>

<style scoped>
.plugin-grid {
  margin-top: 20px;
}
.plugin-card-skeleton {
  margin-bottom: 20px;
}

.card-body .header {
  display: flex;
  gap: 15px;
  align-items: center;
  margin-bottom: 15px;
}

.card-body .plugin-info {
  flex: 1;
}

.card-body .description {
  margin-bottom: 15px;
}

.card-body .footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.el-card__body {
    padding: 0px; box-shadow: none; border: none;
}
</style>
