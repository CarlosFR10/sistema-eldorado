<template>
  <div>
    <VendedorLayout v-if="auth.role === 'vendedor'">
      <RouterView />
    </VendedorLayout>
    <VbenAdminLayout v-else>
      <RouterView />
    </VbenAdminLayout>
  </div>
</template>

<script setup>
import VendedorLayout from './VendedorLayout.vue';
import VbenAdminLayout from './VbenAdminLayout.vue';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
</script>
