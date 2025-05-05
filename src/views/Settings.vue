<template>
  <div class="settings-page">
    <div class="card p-6 bg-white rounded-lg shadow-sm">
      <h1 class="text-2xl font-bold mb-6">Plugin Settings</h1>
      <div class="grid gap-6">
        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-2">Auto Updates</label>
          <div class="flex items-center">
            <label class="inline-flex items-center">
              <input type="checkbox" class="form-checkbox" checked>
              <span class="ml-2">Enable automatic updates</span>
            </label>
          </div>
        </div>

        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-2">Plugin Storage Location</label>
          <input type="text" class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="/wp-content/plugins">
        </div>

        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-2">Debug Mode</label>
          <div class="flex items-center">
            <label class="inline-flex items-center">
              <input type="checkbox" class="form-checkbox">
              <span class="ml-2">Enable debug logging</span>
            </label>
          </div>
        </div>

        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-2">Access Control</label>
          <select class="form-select block w-full mt-1 rounded-md border-gray-300 shadow-sm">
            <option>Administrator Only</option>
            <option>Editor and Above</option>
            <option>Author and Above</option>
          </select>
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
          Save Settings
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Component logic will go here
</script>
