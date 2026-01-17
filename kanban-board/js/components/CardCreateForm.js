const CardCreateForm = {
    template: `
        <div class="bg-neutral-100 rounded-sm px-2 pb-2 pt-2 shadow-sm space-y-2">
            <form @submit.prevent="handleSubmit()">
                <textarea 
                    name="content" 
                    v-model="content" 
                    class="px-2 py-1 w-full" 
                    ref="textArea"
                    placeholder="Enter a title for this card..."
                />
                
                <div class="flex gap-2 text-sm">
                    <button 
                        type="submit" 
                        class="bg-purple-500 text-white text-sm hover:bg-purple-400 cursor-pointer rounded-md py-1 px-4"
                    >
                        Add
                    </button>
                    <button 
                        type="button"
                        class="bg-neutral-500 text-white text-sm hover:bg-neutral-400 cursor-pointer rounded-md py-1 px-4"
                        @click="handleCancel"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    `,

    mounted() {
        this.$refs.textArea.focus();
    },

    data() {
        return {
            content: ''
        }
    },

    methods: {
        handleSubmit() {
            const formData = { content: this.content }
            this.$emit('create-card', formData);
            this.content = '';
        },

        handleCancel() {
            this.$emit('close');
        }
    }
}

export default CardCreateForm;