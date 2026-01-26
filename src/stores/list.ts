import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { List } from '@/types';
import listsData from '@/../data/db.js';

export const useListStore = defineStore('list', () => {
  const lists = ref<List[]>(listsData as List[]);

  return {
    lists,
  };
});
