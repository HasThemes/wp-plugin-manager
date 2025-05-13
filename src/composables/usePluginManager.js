// composables/usePluginManager.js
import Api from '@/utils/axios'
import { ElNotification } from 'element-plus'

export const usePluginManager = () => {
    // Plugin states
    const PLUGIN_STATES = {
        NOT_INSTALLED: 'not_installed',
        INSTALLING: 'installing',
        INACTIVE: 'inactive',
        ACTIVATING: 'activating',
        ACTIVE: 'active'
    }

    // Get button text based on plugin status and loading state
    const getPluginButtonText = (status, isLoading = false) => {
        if (isLoading) {
            switch(status) {
                case PLUGIN_STATES.NOT_INSTALLED:
                case PLUGIN_STATES.INSTALLING:
                    return 'Installing...'
                case PLUGIN_STATES.INACTIVE:
                case PLUGIN_STATES.ACTIVATING:
                    return 'Activating...'
                default:
                    return 'Processing...'
            }
        }

        switch(status) {
            case PLUGIN_STATES.NOT_INSTALLED:
                return 'Install'
            case PLUGIN_STATES.INACTIVE:
                return 'Activate'
            case PLUGIN_STATES.ACTIVE:
                return 'Activated'
            default:
                return 'Install'
        }
    }

    // Install plugin
    const installPlugin = async (plugin, onStatusChange) => {
        try {
            // Update status to installing
            if (onStatusChange) {
                onStatusChange(plugin.slug, PLUGIN_STATES.INSTALLING)
            }

            const response = await Api.post('/htpm/v1/install-plugin', {
                slug: plugin.slug,
                nonce: HTPMM?.nonce
            })

            if (response.data.success) {
                showNotification('Success', 'Plugin installed successfully', 'success')
                return PLUGIN_STATES.INACTIVE
            }
        } catch (error) {
            handleError(error)
            throw error
        }
    }

    // Activate plugin
    const activatePlugin = async (plugin, onStatusChange) => {
        try {
            // Update status to activating
            if (onStatusChange) {
                onStatusChange(plugin.slug, PLUGIN_STATES.ACTIVATING)
            }

            const response = await Api.post('/htpm/v1/activate-plugin', {
                slug: plugin.slug,
                nonce: HTPMM?.nonce
            })

            if (response.data.success) {
                showNotification('Success', 'Plugin activated successfully', 'success')
                return PLUGIN_STATES.ACTIVE
            }
        } catch (error) {
            handleError(error)
            throw error
        }
    }

    // Handle plugin actions (install/activate)
    const handlePluginAction = async (plugin, onStatusChange) => {

        try {
            let finalStatus = null

            // If not installed, install first
            if (plugin.status === PLUGIN_STATES.NOT_INSTALLED) {
                const installStatus = await installPlugin(plugin, onStatusChange)
                if (installStatus === PLUGIN_STATES.INACTIVE) {
                    // Then activate
                    finalStatus = await activatePlugin(plugin, onStatusChange)
                }
            } 
            // If inactive, just activate
            else if (plugin.status === PLUGIN_STATES.INACTIVE) {
                finalStatus = await activatePlugin(plugin, onStatusChange)
            }

            // Update final status if callback provided
            if (finalStatus && onStatusChange) {
                onStatusChange(plugin.slug, finalStatus)
            }

            return finalStatus
        } catch (error) {
            handleError(error)
            // Reset to previous state on error
            if (onStatusChange) {
                onStatusChange(plugin.slug, plugin.status)
            }
            return null
        }
    }

    // Fetch plugin status
    const fetchPluginStatus = async (pluginSlugs) => {
        try {
            const response = await Api.get('/htpm/v1/plugins-status', {
                params: {
                    plugins: Array.isArray(pluginSlugs) ? pluginSlugs.join(',') : pluginSlugs,
                    nonce: HTPMM?.nonce
                }
            })

            if (response.data.success) {
                return response.data.plugins
            }
            return []
        } catch (error) {
            handleError(error)
            return []
        }
    }

    // Show notification
    const showNotification = (title, message, type = 'success') => {
        ElNotification({
            title,
            message,
            type,
            duration: 3000,
            offset: 30
        })
    }

    // Handle errors
    const handleError = (error) => {
        let errorMessage = 'An error occurred'
        
        if (error.response) {
            errorMessage = error.response.data.message || errorMessage
        }
        
        showNotification('Error', errorMessage, 'error')
    }

    // Check if plugin is being managed
    const isPluginManaged = (plugin) => {
        return plugin.status === PLUGIN_STATES.ACTIVE || 
               plugin.status === PLUGIN_STATES.INSTALLING || 
               plugin.status === PLUGIN_STATES.ACTIVATING || 
               plugin.isLoading
    }

    // Fetch plugin data from WordPress.org
    const fetchPluginsData = async (plugins) => {
        try {
            const slugs = plugins.map(p => p.slug)
            const response = await Api.get('/htpm/v1/plugins-info', {
                params: {
                    slugs: Array.isArray(slugs) ? slugs.join(',') : slugs
                }
            })

            if (response.data.success && response.data.plugins) {
                return plugins.map(plugin => {
                    const pluginData = response.data.plugins[plugin.slug]
                    if (pluginData) {
                        return {
                            ...plugin,
                            description: pluginData.short_description || plugin.description,
                            version: pluginData.version,
                            rating: pluginData.rating,
                            num_ratings: pluginData.num_ratings,
                            active_installs: pluginData.active_installs,
                            last_updated: pluginData.last_updated,
                            author: pluginData.author,
                            author_profile: pluginData.author_profile,
                            download_link: pluginData.download_link,
                            homepage: pluginData.homepage,
                            icons: pluginData.icons || {},
                            banners: pluginData.banners || {},
                            icon: pluginData.icons?.['2x'] || 
                                  pluginData.icons?.['1x'] || 
                                  pluginData.icons?.default || 
                                  `${HTPMM?.assetsUrl}/images/plugins/${plugin.slug}.png`
                        }
                    }
                    return plugin
                })
            }
            return plugins
        } catch (error) {
            console.error('Error fetching plugins data:', error)
            return plugins
        }
    }

    // Initialize plugins with data
    const initializePluginsWithData = async (plugins, source = 'wordpress') => {
        try {
            // First, fetch WordPress.org data for all plugins in one call
            const enrichedPlugins = source === 'local' ? plugins : await fetchPluginsData(plugins)
            
            // Then, fetch installation status for all plugins in one call
            const slugs = enrichedPlugins.map(p => p.slug)
            const statuses = await fetchPluginStatus(slugs)
            
            // Merge the data
            return enrichedPlugins.map(plugin => {
                const statusInfo = statuses.find(s => s.slug === plugin.slug)
                return {
                    ...plugin,
                    status: statusInfo ? statusInfo.status : 'not_installed'
                }
            })
        } catch (error) {
            console.error('Error initializing plugins:', error)
            return plugins
        }
    }

    const formatNumber = (num) => {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num;
    }

    return {
        PLUGIN_STATES,
        getPluginButtonText,
        handlePluginAction,
        fetchPluginStatus,
        isPluginManaged,
        fetchPluginsData,
        initializePluginsWithData,
        formatNumber
    }
}