import { defineStore } from 'pinia';
import Api, { ApiPostUrl } from '@/utils/axios';
import { ElNotification } from 'element-plus';

export const useLicenseStore = defineStore('license', {
    state: () => ({
        licenseInfo: null,
        isLoading: false,
        isActivating: false,
        isDeactivating: false,
        error: null
    }),

    getters: {
        isValid: (state) => state.licenseInfo?.data?.is_valid || false,
        licenseData: (state) => state.licenseInfo?.data || null
    },

    actions: {
        async fetchLicenseInfo() {
            this.isLoading = true;
            this.error = null;
            
            try {
                const formData = new FormData();
                formData.append('action', 'htpmpro_el_license_info');
                formData.append('_wpnonce', window.HTPMM.licenseNonce);

                const response = await ApiPostUrl.post('/admin-post.php', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })

                this.licenseInfo = response?.data;
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch license info';
                console.error('License info fetch error:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async activateLicense(payload) {
            this.isActivating = true;
            this.error = null;

            try {
                const formData = new FormData();
                formData.append('action', 'WPPluginManagerPro_el_activate_license');
                formData.append('_wpnonce', window.HTPMM.licenseNonce);
                formData.append('el_license_key', payload.licenseKey);
                formData.append('el_license_email', payload.email);

                const response = await ApiPostUrl.post('/admin-post.php', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    }
                });
                if (response?.data?.status) {
                    this.licenseInfo = response?.data;
                    return {
                        status: true,
                        message: response?.data?.message == 'Invalid license code'? 'License activated successfully' : response?.data?.message || 'License activated successfully',
                        data: response?.data?.data
                    };
                } else {
                    this.error = response?.data?.message || 'License activation failed';
                    return {
                        status: false,
                        message: response?.data?.message || 'License activation failed'
                    };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'License activation failed catch';
                //throw error;
            } finally {
                this.isActivating = false;
            }
        },

        async deactivateLicense() {
            this.isDeactivating = true;
            this.error = null;

            try {
                const formData = new FormData();
                formData.append('action', 'WPPluginManagerPro_el_deactivate_license');
                formData.append('_wpnonce', window.HTPMM.licenseNonce);

                const response = await ApiPostUrl.post('/admin-post.php', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    }
                });

                if (response?.data?.status) {
                    this.licenseInfo = {};
                    return {
                        status: true,
                        message: response?.data?.message || 'License deactivated successfully'
                    };
                } else {
                    this.error = response?.data?.message || 'License deactivation failed';
                    return {
                        status: false,
                        message: response?.data?.message || 'License deactivation failed'
                    };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'License deactivation failed';
                console.error('License deactivation error:', error);
            } finally {
                this.isDeactivating = false;
            }
        }
    }
});