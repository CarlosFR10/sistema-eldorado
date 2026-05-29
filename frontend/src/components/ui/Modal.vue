<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modelValue" class="modal-overlay" @click.self="closeIfAllowed">
        <div class="modal" :class="modalSize">
          <div class="modal-header">
            <h3 class="modal-title">{{ title }}</h3>
            <button class="modal-close" @click="close">
              <X :size="20" />
            </button>
          </div>
          <div class="modal-body">
            <slot></slot>
          </div>
          <div v-if="$slots.footer" class="modal-footer">
            <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { watch } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
  modelValue: Boolean,
  title: String,
  size: { type: String, default: 'md' },
  persistent: Boolean
});

const emit = defineEmits(['update:modelValue', 'close']);

function close() {
  if (props.persistent) return;
  emit('update:modelValue', false);
  emit('close');
}

function closeIfAllowed() {
  if (!props.persistent) close();
}

const modalSize = {
  sm: 'max-w-sm',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl'
}[props.size] || 'max-w-lg';

watch(() => props.modelValue, (val) => {
  document.body.style.overflow = val ? 'hidden' : '';
});
</script>

<style scoped>
.modal-overlay {
  @apply fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm;
}

.modal {
  @apply w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl;
}

.modal-header {
  @apply flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700;
}

.modal-title {
  @apply text-lg font-semibold text-slate-800 dark:text-white;
}

.modal-close {
  @apply p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors;
}

.modal-body {
  @apply px-6 py-4 max-h-[70vh] overflow-y-auto;
}

.modal-footer {
  @apply flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-700;
}

.modal-enter-active,
.modal-leave-active {
  @apply transition-all duration-200;
}

.modal-enter-from,
.modal-leave-to {
  @apply opacity-0;
}

.modal-enter-from .modal,
.modal-leave-to .modal {
  @apply scale-95 translate-y-4;
}
</style>
