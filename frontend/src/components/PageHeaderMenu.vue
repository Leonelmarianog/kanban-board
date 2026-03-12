<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useClickOutside } from '@/composables/shared/useClickOutside';
import { useToggle } from '@/composables/shared/useToggle';
import { useMeQuery } from '@/composables/queries/useMeQuery';
import { useLogout } from '@/composables/mutations/useLogout';
import { useAuthStore } from '@/stores/auth';
import { useToast } from 'vue-toastification';

const router = useRouter();
const authStore = useAuthStore();
const { data: member } = useMeQuery();
const { logout, isLoading } = useLogout();
const toast = useToast();
const { isVisible: isDropdownOpen, toggle: toggleDropdown, hide: closeDropdown } = useToggle();
const dropdownRef = useClickOutside(closeDropdown);

function handleLogout() {
  logout(undefined, {
    onSuccess: () => {
      authStore.clearAuth();
      router.push({ name: 'Login' });
    },

    onError: () => {
      toast.error(
        'An issue occurred while performing this action, please try again or contact support.',
      );
    },
  });
}

const initials = computed(() => member.value?.initials ?? 'JD');
const avatarUrl = computed(() => member.value?.avatar_url ?? null);
const fullName = computed(() => member.value?.full_name ?? '');
</script>

<template>
  <div class="relative" ref="dropdownRef">
    <button
      data-test="page-header-menu.avatar-button"
      @click="toggleDropdown"
      :class="[
        'w-10 h-10 rounded-full flex items-center justify-center',
        avatarUrl ? 'bg-white/20 hover:bg-white/30' : 'bg-purple-500 hover:bg-purple-600',
        'text-white transition-colors overflow-hidden',
      ]"
    >
      <img
        v-if="avatarUrl"
        :src="avatarUrl"
        :alt="fullName"
        data-test="page-header-menu.avatar-image"
        class="w-full h-full object-cover"
      />
      <span v-else data-test="page-header-menu.avatar-initials" class="font-semibold text-sm">
        {{ initials }}
      </span>
    </button>

    <div
      v-if="isDropdownOpen"
      data-test="page-header-menu.dropdown"
      class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg overflow-hidden z-50"
    >
      <div class="px-4 py-2 border-b border-gray-200">
        <p
          data-test="page-header-menu.user-name"
          class="text-sm font-medium text-gray-900 truncate"
        >
          {{ fullName }}
        </p>
      </div>

      <button
        data-test="page-header-menu.profile-button"
        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
        @click="closeDropdown"
        disabled
      >
        Profile
      </button>

      <button
        data-test="page-header-menu.logout-button"
        @click="handleLogout"
        :disabled="isLoading"
        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 transition-colors disabled:opacity-50"
      >
        {{ isLoading ? 'Logging out...' : 'Logout' }}
      </button>
    </div>
  </div>
</template>
