<script lang="ts">
import lists from '@/../data/db.js';

import BoardList from '@/components/BoardList.vue';
import PageLayout from '@/components/PageLayout.vue';
import BoardContainer from '@/components/BoardContainer.vue';

export default {
  components: {
    PageLayout,
    BoardList,
    BoardContainer,
  },

  data() {
    return {
      lists,
    };
  },

  methods: {
    handleCreateCard(formData) {
      const list = this.lists.find((list) => list.id === formData.listId);

      list.cards.push({
        id: new Date().getTime(),
        content: formData.content,
        labels: [],
      });
    },

    handleUpdateCard(formData) {
      const list = this.lists.find((list) => list.id === formData.listId);

      const card = list.cards.find((card) => card.id === formData.cardId);

      Object.assign(card, { content: formData.content });
    },
  },
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
            v-for="list in lists"
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
