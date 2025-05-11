<template>
  <el-dialog 
    v-model="dialogVisible"
    :show-close="false"
    :append-to-body="true"
    :destroy-on-close="false"
    class="htpm-admin-popup-inner"
    @close="handleClose"
    style="padding: 26px 30px 40px 30px; width: 95%; max-width: 500px; border: none; text-align: center;"
    :show-header="false"
    >     
    <button class="htpm-admin-popup-close" @click="handleClose">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.08366 1.73916L8.26116 0.916656L5.00033 4.17749L1.73949 0.916656L0.916992 1.73916L4.17783 4.99999L0.916992 8.26082L1.73949 9.08332L5.00033 5.82249L8.26116 9.08332L9.08366 8.26082L5.82283 4.99999L9.08366 1.73916Z" fill="currentColor"></path>
        </svg>
    </button>
    <div class="htpm-admin-popup-icon">
        <img :src="proBadgeImg" alt="pro">
    </div>
    <h3 class="htpm-admin-popup-title">{{ HTPMM.i18n.pro.modal.title }}</h3>
    <p class="htpm-admin-popup-text">{{ HTPMM.i18n.pro.modal.desc }}</p>
    <a :href="HTPMM.upgradeLink" class="htpm-admin-btn htpm-opt-submit htpm-admin-btn-primary" target="_blank">{{ HTPMM.i18n.pro.modal.buynow || 'Buy Now' }}</a>
</el-dialog>
</template>

<script setup>
import proBadgeImg from '../assets/images/pro-badge.png'
import { ref } from 'vue'

const dialogVisible = ref(true)

const show = () => {
  dialogVisible.value = true
}

const handleClose = () => {
  dialogVisible.value = false
}

defineExpose({
  show
})
</script>

<style scoped>
:deep(.el-dialog) {
    margin: 0 auto !important;
    background: transparent !important;
    box-shadow: none !important;
}

.el-dialog__header {
    display: none !important;
}

:deep(.el-dialog__body) {
    padding: 0 !important;
    margin: 0 !important;
}

:deep(.el-overlay) {
    background-color: transparent !important;
}

.htpm-admin-popup-title {
    font-size: 48px;
    line-height: 1!important;
    margin-bottom: 15px;
    margin-top: 0;
    color: transparent;
    text-shadow: 3px 3px 0 #007EFF;
    -webkit-text-stroke: 2px rgba(0,126,255,.2)
}

.htpm-admin-popup-text {
    font-size: 14px;
    margin-bottom: 22px
}

.htpm-admin-popup .htpm-admin-btn {
    padding: 10px 35px
}

.el-dialog__header {
    display: none !important;
}

.htpm-admin-popup-inner {
    background: #fff;
    border-radius: 8px;
    padding: 40px;
    position: relative;
    width: 100%;
    max-width: 500px;
    text-align: center;
}

.htpm-admin-popup-close {
    position: absolute;
    right: 15px;
    top: 15px;
    background: #f3f4f7;
    border: none;
    padding: 8px;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 3px;
    transition: all .3s ease;
    z-index: 9999;
}

.htpm-admin-popup-close:hover {
    background: #e4e6eb;
    color: #2271b1
}

.htpm-admin-popup-close svg {
    width: 12px;
    height: 12px
}

.htpm-admin-popup-icon {
    margin-bottom: 20px
}

.htpm-admin-popup-icon img {
    max-width: 100px;
    height: auto
}

.htpm-admin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 24px;
    border-radius: 4px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    transition: all .3s ease
}

.htpm-admin-btn-primary {
    background: #3489fe;
    color: #fff;
    border: none
}

.htpm-admin-btn-primary:hover {
    background: #2271b1;
    color: #fff
}

@media (max-width: 480px) {
    .htpm-admin-popup-inner {
        padding:30px 20px
    }

    .htpm-admin-popup-title {
        font-size: 20px
    }

    .htpm-admin-popup-text {
        font-size: 14px
    }

    .htpm-admin-btn {
        padding: 10px 20px;
        font-size: 14px
    }
}
</style>