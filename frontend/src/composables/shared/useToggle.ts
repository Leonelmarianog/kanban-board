import { ref } from 'vue';

/**
 * Composable for managing visibility state.
 */
export function useToggle(initialValue = false) {
  const isVisible = ref(initialValue);

  /**
   * Set visibility to true.
   */
  const show = () => {
    isVisible.value = true;
  };

  /**
   * Set visibility to false.
   */
  const hide = () => {
    isVisible.value = false;
  };

  /**
   * Toggle visibility state.
   */
  const toggle = () => {
    isVisible.value = !isVisible.value;
  };

  return {
    isVisible,
    show,
    hide,
    toggle,
  };
}
