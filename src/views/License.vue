<template>
  <div class="license-page">
    <div class="card p-6 bg-white rounded-lg shadow-sm">
      <h1 class="text-2xl font-bold mb-6">License Management</h1>
      
      <div class="grid gap-6">
        <div class="license-status p-4 bg-yellow-50 border border-yellow-200 rounded-md">
          <h2 class="text-lg font-semibold text-yellow-800 mb-2">License Status</h2>
          <p class="text-yellow-700">Your license needs to be activated</p>
        </div>

        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-2">License Key</label>
          <input 
            type="text" 
            class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm" 
            placeholder="Enter your license key"
          >
        </div>

        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
          <input 
            type="email" 
            class="form-input mt-1 block w-full rounded-md border-gray-300 shadow-sm" 
            placeholder="Enter your email address"
          >
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
          Activate License
        </button>

        <div class="license-info mt-6">
          <h3 class="text-lg font-semibold mb-4">License Information</h3>
          <div class="grid gap-3 text-sm">
            <div class="flex justify-between py-2 border-b">
              <span class="text-gray-600">Plan Type</span>
              <span class="font-medium">Professional</span>
            </div>
            <div class="flex justify-between py-2 border-b">
              <span class="text-gray-600">Expiration Date</span>
              <span class="font-medium">Not activated</span>
            </div>
            <div class="flex justify-between py-2 border-b">
              <span class="text-gray-600">Support Status</span>
              <span class="font-medium">Not activated</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Component logic will go here
</script>
