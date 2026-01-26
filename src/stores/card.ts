import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { List, Card } from '@/types';
import listsData from '@/../data/db.js';

export const useCardStore = defineStore('card', () => {
  const lists = ref<List[]>(listsData as List[]);

  const storeCard = (card: Card) => {
    const list = lists.value.find((list) => list.id === card.listId);
    list?.cards.push(card);
  };

  const updateCard = (cardUpdate: Partial<Card>) => {
    const list = lists.value.find((list) => list.id === cardUpdate.listId);
    const card = list?.cards.find((card) => card.id === cardUpdate.id);

    if (card) {
      Object.assign(card, cardUpdate);
    }
  };

  return {
    storeCard,
    updateCard,
  };
});
