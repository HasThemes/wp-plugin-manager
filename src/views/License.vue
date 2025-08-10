<template>
  <div class="license-management-wrapper htpm-inner-page-wrapper">
      <el-card class="license-card" shadow="never">
          <template #header>
              <div class="card-header">
                  <el-skeleton :rows="1" animated v-if="isLoading" />
                  <h3 v-else>{{ pageTitle }}</h3>
              </div>
          </template>

          <!-- Loading State -->
          <div v-if="isLoading" class="loading-state">
              <el-skeleton :rows="4" animated />
          </div>

          <!-- License Activation Form -->
          <div v-else-if="!isValid" class="license-form">
              <el-alert
                  v-if="successMessage"
                  :title="successMessage"
                  type="success"
                  :closable="true"
                  class="mb-4"
                  show-icon
                  @close="clearMessages"
              />
              <el-alert
                  v-if="error"
                  :title="error"
                  type="error"
                  :closable="true"
                  class="mb-4"
                  show-icon
                  @close="clearError"
              />
              <p class="form-description">Enter your license key here, to activate Hashbar Pro and get future updates and premium support.</p>
              <el-form 
                  ref="licenseForm"
                  :model="formData"
                  :rules="formRules"
                  @submit.prevent="handleActivation"
              >
                  <el-form-item prop="licenseKey">
                      <el-input
                          v-model="formData.licenseKey"
                          placeholder="Enter your license key"
                          :disabled="isActivating"
                          size="large"
                          :validate-event="false"
                      />
                  </el-form-item>
                  <el-form-item prop="email">
                      <el-input
                          v-model="formData.email"
                          placeholder="Enter your email"
                          :disabled="isActivating"
                          size="large"
                          :validate-event="false"
                      />
                  </el-form-item>
                  <p class="form-description">We will send update news of this product by this email address, don't worry, we hate spam</p>
                  <el-form-item>
                      <el-button 
                          type="primary" 
                          @click="handleActivation"
                          :loading="isActivating"
                          size="large"
                      >
                          Activate License
                      </el-button>
                  </el-form-item>
              </el-form>
          </div>

          <!-- License Information -->
          <div v-else class="license-info">
              <el-alert
                  v-if="successMessage"
                  :title="successMessage"
                  type="success"
                  :closable="true"
                  class="mb-4"
                  show-icon
                  @close="clearMessages"
              />
              <el-alert
                  v-if="error"
                  :title="error"
                  type="error"
                  :closable="true"
                  class="mb-4"
                  show-icon
                  @close="clearError"
              />
              <ul class="license-details">
                  <li>
                      <strong>Status:</strong>
                      <el-tag type="success">Active</el-tag>
                  </li>
                  <li>
                      <strong>License Type:</strong>
                      <span>{{ licenseData?.license_title }}</span>
                  </li>
                  <li>
                      <strong>License Key:</strong>
                      <span>{{ licenseData?.license_key }}</span>
                  </li>
                  <li>
                      <strong>Expiry Date:</strong>
                      <span>{{ licenseData?.expire_date }}</span>
                  </li>
                  <li>
                      <strong>Support Until:</strong>
                      <span>{{ licenseData?.support_end }}</span>
                  </li>
              </ul>

              <div class="action-buttons">
                  <el-button 
                      type="danger" 
                      @click="confirmDeactivation"
                      :loading="isDeactivating"
                      size="large"
                  >
                      Deactivate License
                  </el-button>
              </div>
          </div>
      </el-card>

      <!-- No dialog needed as we're using ElMessageBox.confirm -->
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { ElMessage, ElNotification, ElAlert, ElMessageBox } from 'element-plus';
import { useLicenseStore } from '../store/modules/license';

export default {
  name: 'License',
  setup() {
      const licenseStore = useLicenseStore();
      const licenseForm = ref(null);
      const successMessage = ref('');

      const formData = ref({
          licenseKey: '',
          email: window.HTPMM?.licenseEmail
      });

      const formRules = {
          licenseKey: [
              { required: true, message: 'Please enter your license key', trigger: 'blur' }
          ],
          email: [
              { required: true, message: 'Please enter your email', trigger: 'blur' },
              { type: 'email', message: 'Please enter a valid email address', trigger: 'blur' }
          ]
      };

      const pageTitle = computed(() => licenseStore.isValid ? 'License Information' : 'License Activation');

      // Fetch license info on component mount
      onMounted(async () => {
          await licenseStore.fetchLicenseInfo();
      });

      const handleActivation = async () => {
          if (!licenseForm.value) return;
          
          try {
              // Clear any previous messages
              licenseStore.error = null;
              successMessage.value = '';
              
              await licenseForm.value.validate();
              const response = await licenseStore.activateLicense(formData.value);
              
              if(response?.status) {
                  successMessage.value = response?.message || 'License activated successfully';
                  // Add a small delay to ensure the success message is visible
                  await new Promise(resolve => setTimeout(resolve, 100));
              } else {
                  // Set error in store for alert display
                  licenseStore.error = response?.message || 'License activation failed';
              }
          } catch (error) {
              // Handle validation errors differently
              if (error.name === 'ValidationError') {
                  licenseStore.error = error.message;
                  return;
              }
              
              // Set error message in store for alert display
              if (error?.response?.message) {
                  licenseStore.error = error.response.message;
              } else if (error?.message) {
                  licenseStore.error = error.message;
              } else {
                  licenseStore.error = 'License activation failed';
              }
          }
      };

      const confirmDeactivation = () => {
          ElMessageBox.confirm(
              'Are you sure you want to deactivate your license? This will disable premium features.',
              'Confirm Deactivation',
              {
                  confirmButtonText: 'Deactivate',
                  cancelButtonText: 'Cancel',
                  type: 'warning',
                  confirmButtonClass: 'el-button--danger'
              }
          ).then(() => {
              handleDeactivation();
          }).catch(() => {
              // User cancelled the deactivation
          });
      };

      const handleDeactivation = async () => {
          try {
              // Clear any previous messages
              licenseStore.error = null;
              successMessage.value = '';
              
              const response = await licenseStore.deactivateLicense();
              if(response?.status) {
                  formData.value = {
                      licenseKey: '',
                      email: window.HTPMM?.licenseEmail
                  };
                  successMessage.value = response?.message || 'License deactivated successfully';
              } else {
                  // Set error in store for alert display
                  licenseStore.error = response?.message || 'License deactivation failed';
              }
          } catch (error) {
              // Set error message in store for alert display
              if (error?.response?.data?.message) {
                  licenseStore.error = error.response.data.message;
              } else if (error?.message) {
                  licenseStore.error = error.message;
              } else {
                  licenseStore.error = 'License deactivation failed';
              }
          }
      };

      return {
          formData,
          formRules,
          licenseForm,
          pageTitle,
          isLoading: computed(() => licenseStore.isLoading),
          isActivating: computed(() => licenseStore.isActivating),
          isDeactivating: computed(() => licenseStore.isDeactivating),
          isValid: computed(() => licenseStore.isValid),
          licenseData: computed(() => licenseStore.licenseData),
          error: computed(() => licenseStore.error),
          successMessage,
          clearError: () => licenseStore.error = null,
          clearMessages: () => {
              licenseStore.error = null;
              successMessage.value = '';
          },
          handleActivation,
          confirmDeactivation,
          handleDeactivation
      };
  }
};
</script>

<style scoped>
.license-management-wrapper {
    height: 100vh;
    padding-top: 60px;
}

.mb-4 {
  margin-bottom: 16px;
}

.license-card {
  max-width: 800px;
  margin: 0 auto;
  box-shadow: none;
}

.card-header h3 {
  margin: 0;
  font-size: 1.5rem;
  color: var(--el-text-color-primary);
}

.license-form, .license-info {
  padding: 0;
}

.form-description {
  color: var(--el-text-color-secondary);
  margin-bottom: 20px;
}

.license-details {
  list-style: none;
  padding: 0;
  margin: 0;
}

.license-details li {
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #e5e7eb;
    padding: 12px;
    font-size: 16px;
}

.license-details li strong {
  min-width: 120px;
  color: var(--el-text-color-primary);
}

.action-buttons {
  margin-top: 30px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
