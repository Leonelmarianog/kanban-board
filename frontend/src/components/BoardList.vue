<script setup lang="ts">
import { ref } from 'vue';
import BoardCard from './BoardCard.vue';
import CardForm from '@/components/CardForm.vue';
import CustomButton from '@/components/CustomButton.vue';
import type { Card, List } from '@/types';
import { useToggle } from '@/composables';
import { useCardStore } from '@/stores/card';
import QuickCardEditorMenuOverlay from '@/components/QuickCardEditorMenuOverlay.vue';
import { Plus } from 'lucide-vue-next';

const props = defineProps<{
  list: List;
}>();

const cardStore = useCardStore();

const {
  isVisible: isCardCreateFormVisible,
  show: showCardCreateForm,
  hide: hideCardCreateForm,
} = useToggle();

const handleCreateCard = (formData: Partial<Card>) => {
  cardStore.storeCard({
    id: new Date().getTime(),
    listId: props.list.id,
    content: formData.content as string,
    labels: [],
  });
  hideCardCreateForm();
};

const cardRefs = ref<Record<Card['id'], HTMLElement>>({});
const currentCard = ref<Card | null>(null);
const currentCardRef = ref<HTMLElement | null>(null);

const {
  isVisible: isQuickCardEditorMenuVisible,
  show: showQuickCardEditorMenu,
  hide: hideQuickCardEditorMenu,
} = useToggle();

function openQuickCardEditorMenu(card: Card) {
  const cardId = card.id;
  const cardRef = cardRefs.value[cardId]!;
  currentCard.value = card;
  currentCardRef.value = cardRef;
  showQuickCardEditorMenu();
}

function closeQuickCardEditorMenu() {
  currentCard.value = null;
  currentCardRef.value = null;
  hideQuickCardEditorMenu();
}
</script>

<template>
  <div class="bg-neutral-300 rounded-md shadow-md p-2">
    <h2 class="font-bold capitalize pl-2">{{ list.title }}</h2>

    <ul class="space-y-2 mt-3 mb-2">
      <li
        v-for="card in list.cards"
        :key="card.id"
        :ref="(element) => (cardRefs[card.id] = element as HTMLElement)"
      >
        <BoardCard :card="card" @edit="openQuickCardEditorMenu" />
      </li>
    </ul>

    <CardForm
      v-if="isCardCreateFormVisible"
      @save="handleCreateCard"
      @cancel="hideCardCreateForm"
    />

    <CustomButton v-else variant="clear" width="full" @click="showCardCreateForm">
      <span class="flex items-center gap-1 font-bold text-sm">
        <Plus :size="16" />
        Add another card
      </span>
    </CustomButton>

    <QuickCardEditorMenuOverlay
      v-if="isQuickCardEditorMenuVisible"
      :cardRef="currentCardRef!"
      :card="currentCard!"
      :listId="list.id"
      @close="closeQuickCardEditorMenu"
    />
  </div>
</template>
