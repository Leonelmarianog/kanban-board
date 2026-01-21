<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import CardLabel from './CardLabel.vue';
import CardForm from '@/components/CardForm.vue';
import FocusOverlay from '@/components/FocusOverlay.vue';

export default defineComponent({
  components: { FocusOverlay, CardForm, CardLabel },

  data() {
    return {
      isCardUpdateFormVisible: false,
    };
  },

  props: {
    id: {
      type: Number,
      required: true,
    },
    content: {
      type: String,
      default: '',
    },
    labels: {
      type: Array as PropType<{ name: string; color: string }[]>,
      default: () => [],
    },
  },

  methods: {
    handleOpenCardUpdateForm() {
      this.isCardUpdateFormVisible = true;
    },

    handleCloseCardUpdateForm() {
      this.isCardUpdateFormVisible = false;
    },

    handleUpdateCard(formData: { content: string }) {
      this.$emit('update-card', { ...formData, cardId: this.id });
      this.handleCloseCardUpdateForm();
    },
  },
});
</script>

<template>
  <div class="bg-neutral-100 rounded-sm shadow-sm px-2 py-1 space-y-1 group relative">
    <ul class="flex flex-wrap gap-x-1">
      <li v-for="label in labels" :key="label.name + '_' + new Date().getTime()">
        <CardLabel :name="label.name" :color="label.color" />
      </li>
    </ul>

    <p v-show="!isCardUpdateFormVisible" class="truncate">{{ content }}</p>

    <FocusOverlay v-if="isCardUpdateFormVisible">
      <div class="relative">
        <CardForm
          @cancel="handleCloseCardUpdateForm"
          @save="handleUpdateCard"
          :initial-values="{ content }"
        />

        <div class="absolute top-0 -right-27">
          <ul class="space-y-1">
            <li>
              <button
                class="bg-black/70 py-2 px-4 rounded-sm cursor-pointer hover:bg-black/90 font-bold text-sm text-white text-nowrap"
              >
                Open card
              </button>
            </li>
            <li>
              <button
                class="bg-black/70 py-2 px-4 rounded-sm cursor-pointer hover:bg-black/90 font-bold text-sm text-white text-nowrap"
              >
                Edit labels
              </button>
            </li>
          </ul>
        </div>
      </div>
    </FocusOverlay>

    <button
      v-show="!isCardUpdateFormVisible"
      class="bg-neutral-100 text-sm font-bold p-1 rounded-sm cursor-pointer hidden group-hover:block hover:bg-neutral-300 absolute top-[0.2em] right-[0.2em]"
      @click="handleOpenCardUpdateForm"
    >
      <img
        class="w-5 h-5"
        src="https://unpkg.com/lucide-static@latest/icons/pencil.svg"
        alt="Edit"
      />
    </button>
  </div>
</template>
