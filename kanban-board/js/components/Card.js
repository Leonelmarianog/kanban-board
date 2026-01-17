import Label from "./Label.js";
import CardUpdateForm from "./CardUpdateForm.js";

const Card = {
    components: { Label, CardUpdateForm },

    template: `
        <div :class="{
            'bg-neutral-100 rounded-sm shadow-sm px-2 py-1 space-y-1 group relative': true,
            'z-2': isCardUpdateFormVisible
        }">
            <ul class="flex flex-wrap gap-x-1">
                <li v-for="label in labels">
                    <Label  :name="label.name" :color="label.color" />
                </li>
            </ul>
            
            <p v-show="!isCardUpdateFormVisible" class="truncate">{{ content }}</p>
            
            <CardUpdateForm v-if="isCardUpdateFormVisible" @close="handleCloseCardUpdateForm" @update-card="handleUpdateCard" :initial-content="content" />
            
            <button 
                v-show="!isCardUpdateFormVisible"
                class="bg-neutral-100 text-sm font-bold p-1 rounded-sm cursor-pointer hidden group-hover:block hover:bg-neutral-300 absolute top-[0.2em] right-[0.2em]"
                @click="handleOpenCardUpdateForm"
            >
                <img class="w-5 h-5" src="https://unpkg.com/lucide-static@latest/icons/pencil.svg" alt="Edit" />
            </button>
        </div>
    `,

    data() {
        return {
            isCardUpdateFormVisible: false
        }
    },

    props: {
        id: Number,
        content: String,
        labels: Array
    },

    methods: {
        handleOpenCardUpdateForm () {
            this.isCardUpdateFormVisible = true;
            this.toggleLayoutOverlay();
        },

        handleCloseCardUpdateForm () {
            this.isCardUpdateFormVisible = false;
            this.toggleLayoutOverlay();
        },

        handleUpdateCard(formData) {
            this.$emit('update-card', { ...formData, cardId: this.id });
            this.handleCloseCardUpdateForm();
        }
    },

    inject: ['toggleLayoutOverlay'],
}

export default Card;