<script lang="ts">
import { type CSSProperties, defineComponent } from 'vue';
import type { PropType } from 'vue';
import BoardCard from './BoardCard.vue';
import CardForm from '@/components/CardForm.vue';
import FocusOverlay from '@/components/FocusOverlay.vue';
import CardMenu from '@/components/CardMenu.vue';
import CustomButton from '@/components/CustomButton.vue';

export default defineComponent({
  components: { CustomButton, CardMenu, FocusOverlay, CardForm, BoardCard },

  data() {
    return {
      activeCard: null as Record<string, unknown> | null,
      activeCardRect: null as DOMRect | null,
      isCardCreateFormVisible: false,
      isCardUpdateFormVisible: false,
    };
  },

  props: {
    id: Number,
    title: String,
    cards: Array as PropType<
      { id: number; content: string; labels: { name: string; color: string }[] }[]
    >,
  },

  methods: {
    handleOpenCardCreateForm() {
      this.isCardCreateFormVisible = true;
    },

    handleCloseCardCreateForm() {
      this.isCardCreateFormVisible = false;
    },

    handleOpenCardUpdateForm(card: Record<string, unknown>, cardRect: DOMRect) {
      this.activeCard = card;
      this.activeCardRect = cardRect;
      this.isCardUpdateFormVisible = true;
    },

    handleCloseCardUpdateForm() {
      this.activeCard = null;
      this.activeCardRect = null;
      this.isCardUpdateFormVisible = false;
    },

    handleCreateCard(formData: Record<string, unknown>) {
      this.$emit('create-card', { ...formData, listId: this.id });
      this.handleCloseCardCreateForm();
    },

    handleUpdateCard(formData: Record<string, unknown>) {
      this.$emit('update-card', { ...formData, listId: this.id });
      this.handleCloseCardUpdateForm();
    },
  },

  computed: {
    activeCardPosition(): CSSProperties | null {
      if (!this.activeCardRect) {
        return null;
      }

      return {
        top: `${this.activeCardRect.top}px`,
        left: `${this.activeCardRect.left}px`,
      };
    },

    activeCardSize(): CSSProperties | null {
      if (!this.activeCardRect) {
        return null;
      }

      return {
        height: `${this.activeCardRect.height}px`,
        width: `${this.activeCardRect.width}px`,
      };
    },
  },
});
</script>

<template>
  <div class="bg-neutral-300 rounded-md shadow-md p-2">
    <h2 class="font-bold capitalize pl-2">{{ title }}</h2>

    <ul class="space-y-2 mt-3 mb-2">
      <li v-for="card in cards" :key="card.id">
        <BoardCard
          :id="card.id"
          :content="card.content"
          :labels="card.labels"
          @edit="handleOpenCardUpdateForm"
        />
      </li>
    </ul>

    <CardForm
      v-if="isCardCreateFormVisible"
      @save="handleCreateCard"
      @cancel="handleCloseCardCreateForm"
    />

    <CustomButton v-else variant="clear" :fullWidth="true" @click="handleOpenCardCreateForm">
      <span class="text-neutral-500 hover:text-neutral-700 text-sm font-bold text-left block">
        + Add another card
      </span>
    </CustomButton>

    <FocusOverlay v-if="isCardUpdateFormVisible">
      <div class="flex gap-2 absolute" :style="activeCardPosition">
        <CardForm
          :style="activeCardSize"
          :initial-values="activeCard"
          @save="handleUpdateCard"
          @cancel="handleCloseCardUpdateForm"
        />
        <CardMenu />
      </div>
    </FocusOverlay>
  </div>
</template>
