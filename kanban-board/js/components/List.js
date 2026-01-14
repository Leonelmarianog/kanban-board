import Card from "./Card.js";
import CardCreateForm from "./CardCreateForm.js";

const List = {
    components: { Card, CardCreateForm },

    template: `
        <div class="bg-neutral-300 rounded-md shadow-sm">
             <div class="px-4 py-2">
                 <h2 class="font-bold capitalize">{{ title }}</h2>
             </div>
    
             <div class="px-2 pb-2 space-y-4">
                 <ul class="space-y-2">
                     <li v-for="card in cards">
                        <Card :content="card.content" :labels="card.labels" />
                     </li>
                     
                     <li>
                        <CardCreateForm  v-show="isCardCreateFormVisible" @close="handleCloseCardCreateForm" @create-card="handleCreateCard"/>
                    </li>    
                 </ul>
    
                 <button 
                    class="font-bold text-sm text-neutral-500 cursor-pointer hover:text-neutral-700"
                    @click="handleOpenCardCreateForm"
                    v-show="!isCardCreateFormVisible"
                  >
                    + Add another card
                 </button>
             </div>
        </div>
    `,

    data() {
        return {
            isCardCreateFormVisible: false
        }
    },

    props: {
        id: Number,
        title: String,
        cards: Array
    },

    methods: {
        handleOpenCardCreateForm () {
            this.isCardCreateFormVisible = true;
        },

        handleCloseCardCreateForm () {
            this.isCardCreateFormVisible = false;
        },

        handleCreateCard(formData) {
            this.$emit('create-card', { ...formData, listId: this.id });
            this.handleCloseCardCreateForm();
        }
    }
}

export default List;
