<template>
  <div class="card" :class="{ 'card-compact': compact }">
    <div v-if="$slots.header || title" class="card-header">
      <div>
        <h3 v-if="title" class="card-title">{{ title }}</h3>
        <p v-if="subtitle" class="card-subtitle">{{ subtitle }}</p>
      </div>
      <slot name="actions"></slot>
    </div>
    <div class="card-body" :class="{ 'no-padding': noPadding }">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  subtitle: String,
  compact: Boolean,
  noPadding: Boolean
});
</script>

<style scoped>
.card {
  @apply bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden;
}

.card-compact {
  @apply rounded-lg;
}

.card-header {
  @apply flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700;
}

.card-title {
  @apply text-base font-semibold text-slate-800 dark:text-white;
}

.card-subtitle {
  @apply text-sm text-slate-500 dark:text-slate-400 mt-0.5;
}

.card-body {
  @apply p-5;
}

.card-body.no-padding {
  @apply p-0;
}

.card-footer {
  @apply px-5 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50;
}
</style>
