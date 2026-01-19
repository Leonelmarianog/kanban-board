<script lang="ts">
import BoardCard from './BoardCard.vue';
import CardCreateForm from './CardCreateForm.vue';

export default {
  components: { BoardCard, CardCreateForm },

  data() {
    return {
      isCardCreateFormVisible: false,
    };
  },

  props: {
    id: Number,
    title: String,
    cards: Array,
  },

  methods: {
    handleOpenCardCreateForm() {
      this.isCardCreateFormVisible = true;
    },

    handleCloseCardCreateForm() {
      this.isCardCreateFormVisible = false;
    },

    handleCreateCard(formData) {
      this.$emit('create-card', { ...formData, listId: this.id });
      this.handleCloseCardCreateForm();
    },

    handleUpdateCard(formData) {
      this.$emit('update-card', { ...formData, listId: this.id });
    },
  },
};
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
          @update-card="handleUpdateCard"
        />
      </li>
    </ul>

    <button
      class="w-full text-neutral-500 text-left text-sm font-bold cursor-pointer hover:text-neutral-700 hover:bg-black/10 py-2 pl-4 rounded-sm"
      @click="handleOpenCardCreateForm"
      v-show="!isCardCreateFormVisible"
    >
      + Add another card
    </button>

    <CardCreateForm
      v-if="isCardCreateFormVisible"
      @close="handleCloseCardCreateForm"
      @create-card="handleCreateCard"
    />
  </div>
</template>
