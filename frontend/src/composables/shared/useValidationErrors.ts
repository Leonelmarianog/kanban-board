import { ref, computed } from 'vue';

/**
 * Composable for managing validation errors.
 */
export function useValidationErrors() {
  const errors = ref<Record<string, string[]>>({});

  /**
   * Validate if the provided value is a valid validation errors object.
   */
  function isValidValidationErrors(value: unknown): value is Record<string, string[]> {
    if (typeof value !== 'object' || value === null) {
      return false;
    }

    return Object.values(value).every(
      (v) => Array.isArray(v) && v.every((item) => typeof item === 'string'),
    );
  }

  /**
   * Set validation errors.
   */
  function setErrors(validationErrors: Record<string, string[]> | undefined) {
    if (isValidValidationErrors(validationErrors)) {
      errors.value = validationErrors;
    }
  }

  /**
   * Clear all validation errors.
   */
  function clearErrors() {
    errors.value = {};
  }

  /**
   * Check if there are any validation errors.
   */
  function hasErrors(): boolean {
    return Object.keys(errors.value).length > 0;
  }

  /**
   * Get validation errors for a specific field.
   */
  function getErrors(field: string): string[] | undefined {
    return errors.value[field];
  }

  /**
   * Check if a specific field has validation errors.
   */
  function hasFieldError(field: string): boolean {
    return field in errors.value;
  }

  return {
    errors: computed(() => errors.value),
    setErrors,
    clearErrors,
    hasErrors,
    getErrors,
    hasFieldError,
  };
}
