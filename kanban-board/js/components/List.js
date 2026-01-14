import Card from "./Card.js";

const List = {
    components: { Card },

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
                        <div class="bg-neutral-100 rounded-sm px-2 pb-2 pt-2 shadow-sm space-y-2" v-show="canAddCard">
                            <form @submit.prevent="handleAddNewCard()">
                                <textarea name="content" v-model="content" class="px-2 py-1 w-full" />
                                <div class="flex gap-2 text-sm">
                                    <button 
                                        type="submit" 
                                        class="bg-purple-500 text-white text-sm hover:bg-purple-400 cursor-pointer rounded-md py-1 px-4"
                                    >
                                        Add
                                    </button>
                                    <button 
                                        type="reset"
                                        class="bg-neutral-500 text-white text-sm hover:bg-neutral-400 cursor-pointer rounded-md py-1 px-4"
                                        @click="closeCreateNewCardForm()"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </li>    
                 </ul>
    
                 <button 
                    class="font-bold text-sm text-neutral-500 cursor-pointer hover:text-neutral-700"
                    @click="openCreateNewCardForm()"
                    v-show="!canAddCard"
                  >
                    + Add another card
                 </button>
             </div>
        </div>
    `,

    data() {
        return {
            content: '',
            canAddCard: false
        }
    },

    props: {
        id: Number,
        title: String,
        cards: Array
    },

    methods: {
        openCreateNewCardForm () {
            this.canAddCard = true;
        },

        closeCreateNewCardForm () {
            this.canAddCard = false;
        },

        handleAddNewCard() {
            this.$emit('add-card', {
                listId: this.id,
                content: this.content
            });
            this.canAddCard = !this.canAddCard;
            this.content = '';
        }
    }
}

export default List;
