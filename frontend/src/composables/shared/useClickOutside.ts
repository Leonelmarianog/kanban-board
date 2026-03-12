import { ref, onMounted, onUnmounted, type Ref } from 'vue';

/**
 * Composable for detecting clicks outside a referenced element.
 * @param callback - Function to call when a click outside occurs
 * @returns A ref to attach to the element you want to detect clicks outside of
 */
export function useClickOutside(callback: () => void): Ref<HTMLElement | null> {
  const elementRef = ref<HTMLElement | null>(null);

  function attemptToInvokeCallback(event: MouseEvent) {
    const element = elementRef.value;

    if (element && !element.contains(event.target as Node)) {
      callback();
    }
  }

  onMounted(() => {
    document.addEventListener('click', attemptToInvokeCallback);
  });

  onUnmounted(() => {
    document.removeEventListener('click', attemptToInvokeCallback);
  });

  return elementRef;
}
