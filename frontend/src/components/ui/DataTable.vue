<template>
  <div class="data-table">
    <!-- Table -->
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th v-for="column in columns" :key="column.key" :class="column.class">
              {{ column.label }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td :colspan="columns.length" class="text-center py-8">
              <div class="spinner-container">
                <span class="spinner"></span>
                <span class="ml-2 text-slate-400">Cargando...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="data.length === 0">
            <td :colspan="columns.length" class="text-center py-8">
              <div class="empty-state">
                <Inbox :size="40" class="empty-icon" />
                <p class="empty-text">No hay datos disponibles</p>
              </div>
            </td>
          </tr>
          <tr v-else v-for="(row, index) in data" :key="row.id || index" @click="$emit('row-click', row)">
            <td v-for="column in columns" :key="column.key">
              <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                {{ row[column.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="table-pagination">
      <div class="pagination-info">
        Mostrando {{ pagination.from || 0 }} - {{ pagination.to || 0 }} de {{ pagination.total || 0 }}
      </div>
      <div class="pagination-buttons">
        <button class="page-btn" :disabled="pagination.current_page === 1" @click="$emit('page-change', pagination.current_page - 1)">
          <ChevronLeft :size="16" />
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          class="page-btn"
          :class="{ 'is-active': page === pagination.current_page }"
          @click="$emit('page-change', page)"
        >
          {{ page }}
        </button>
        <button class="page-btn" :disabled="pagination.current_page === pagination.last_page" @click="$emit('page-change', pagination.current_page + 1)">
          <ChevronRight :size="16" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Inbox, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  pagination: { type: Object, default: null }
});

defineEmits(['row-click', 'page-change']);

const visiblePages = computed(() => {
  if (!props.pagination) return [];
  const current = props.pagination.current_page;
  const last = props.pagination.last_page;
  const range = 2;
  const pages = [];
  for (let i = Math.max(1, current - range); i <= Math.min(last, current + range); i++) {
    pages.push(i);
  }
  return pages;
});
</script>

<style scoped>
.table-container {
  @apply overflow-x-auto;
}

.table {
  @apply w-full text-sm;
}

.table th {
  @apply px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700;
}

.table td {
  @apply px-4 py-3 text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-700;
}

.table tbody tr {
  @apply hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer;
}

.spinner-container {
  @apply flex items-center justify-center;
}

.spinner {
  @apply w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin;
}

.empty-state {
  @apply flex flex-col items-center justify-center py-8;
}

.empty-icon {
  @apply text-slate-300 dark:text-slate-600 mb-2;
}

.empty-text {
  @apply text-slate-400 dark:text-slate-500;
}

.table-pagination {
  @apply flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-700;
}

.pagination-info {
  @apply text-sm text-slate-500 dark:text-slate-400;
}

.pagination-buttons {
  @apply flex items-center gap-1;
}

.page-btn {
  @apply flex items-center justify-center w-8 h-8 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed;
}

.page-btn.is-active {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}
</style>
