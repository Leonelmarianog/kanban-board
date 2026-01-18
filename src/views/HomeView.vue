<script>
import lists from '@/../data/db.js';

import List from "@/components/List.vue";
import Layout from "@/components/Layout.vue";
import Board from "@/components/Board.vue";

export default {
  components: {
    Layout, List, Board
  },

  data( ) {
    return {
      lists
    }
  },

  methods: {
    handleCreateCard(formData) {
      const list = this.lists.find((list) => list.id === formData.listId);

      list.cards.push({
        id: new Date().getTime(),
        content: formData.content,
        labels: []
      });
    },

    handleUpdateCard(formData) {
      const list = this.lists.find((list) => list.id === formData.listId);

      const card = list.cards.find(card => card.id === formData.cardId);

      Object.assign(card, { content: formData.content });
    }
  }
}
</script>

<template>
  <Layout>
    <template v-slot:heading>
      <h1 class="font-bold text-3xl text-center">My board</h1>
    </template>

    <template v-slot:default>
      <Board>
        <template v-slot:default>
          <List
            v-for="list in lists"
            :id="list.id"
            :title="list.title"
            :cards="list.cards"
            @create-card="handleCreateCard"
            @update-card="handleUpdateCard"
          />
        </template>
      </Board>
    </template>
  </Layout>
</template>
