<script setup lang="ts">
import BoardList from '@/components/BoardList.vue';
import PageLayout from '@/components/PageLayout.vue';
import BoardContainer from '@/components/BoardContainer.vue';
import { useCardStore } from '@/stores/card';
import { useListStore } from '@/stores/list';
import type { Card } from '@/types';

const cardStore = useCardStore();
const listStore = useListStore();

const handleCreateCard = (formData: Partial<Card>): void => {
  cardStore.storeCard({
    id: new Date().getTime(),
    listId: formData.listId as number,
    content: formData.content as string,
    labels: [],
  });
};

const handleUpdateCard = (formData: Partial<Card>): void => {
  cardStore.updateCard({
    id: formData.id as number,
    listId: formData.listId as number,
    content: formData.content as string,
  });
};
</script>

<template>
  <PageLayout>
    <template v-slot:heading>
      <h1 class="font-bold text-3xl text-center">My board</h1>
    </template>

    <template v-slot:default>
      <BoardContainer>
        <template v-slot:default>
          <BoardList
            v-for="list in listStore.lists"
            :key="list.id"
            :id="list.id"
            :title="list.title"
            :cards="list.cards"
            @create-card="handleCreateCard"
            @update-card="handleUpdateCard"
          />
        </template>
      </BoardContainer>
    </template>
  </PageLayout>
</template>
