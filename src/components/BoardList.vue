<script setup lang="ts">
import { type CSSProperties, computed, ref } from 'vue';
import BoardCard from './BoardCard.vue';
import CardForm from '@/components/CardForm.vue';
import FocusOverlay from '@/components/FocusOverlay.vue';
import CardMenu from '@/components/CardMenu.vue';
import CustomButton from '@/components/CustomButton.vue';

const props = defineProps<{
  id?: number;
  title?: string;
  cards?: { id: number; content: string; labels: { name: string; color: string }[] }[];
}>();

const emit = defineEmits<{
  (e: 'create-card', payload: Record<string, unknown>): void;
  (e: 'update-card', payload: Record<string, unknown>): void;
}>();

const activeCard = ref<Record<string, unknown> | null>(null);
const activeCardRect = ref<DOMRect | null>(null);
const isCardCreateFormVisible = ref(false);
const isCardUpdateFormVisible = ref(false);

const handleOpenCardCreateForm = () => {
  isCardCreateFormVisible.value = true;
};

const handleCloseCardCreateForm = () => {
  isCardCreateFormVisible.value = false;
};

const handleOpenCardUpdateForm = (card: Record<string, unknown>, cardRect: DOMRect) => {
  activeCard.value = card;
  activeCardRect.value = cardRect;
  isCardUpdateFormVisible.value = true;
};

const handleCloseCardUpdateForm = () => {
  activeCard.value = null;
  activeCardRect.value = null;
  isCardUpdateFormVisible.value = false;
};

const handleCreateCard = (formData: Record<string, unknown>) => {
  emit('create-card', { ...formData, listId: props.id });
  handleCloseCardCreateForm();
};

const handleUpdateCard = (formData: Record<string, unknown>) => {
  emit('update-card', { ...formData, listId: props.id });
  handleCloseCardUpdateForm();
};

const activeCardPosition = computed((): CSSProperties | null => {
  if (!activeCardRect.value) {
    return null;
  }

  return {
    top: `${activeCardRect.value.top}px`,
    left: `${activeCardRect.value.left}px`,
  };
});

const activeCardSize = computed((): CSSProperties | null => {
  if (!activeCardRect.value) {
    return null;
  }

  return {
    height: `${activeCardRect.value.height}px`,
    width: `${activeCardRect.value.width}px`,
  };
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
      <div class="flex gap-2 absolute" :style="activeCardPosition || undefined">
        <CardForm
          :style="activeCardSize || undefined"
          :initial-values="activeCard"
          @save="handleUpdateCard"
          @cancel="handleCloseCardUpdateForm"
        />
        <CardMenu />
      </div>
    </FocusOverlay>
  </div>
</template>
