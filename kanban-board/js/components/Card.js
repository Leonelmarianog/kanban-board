import Label from "./Label.js";

const Card = {
    components: { Label },
    template: `
        <div class="bg-neutral-100 rounded-sm px-2 py-1 shadow-sm space-y-1 relative group">
            <div class="space-x-1 pr-12">
                <Label v-for="label in labels" :name="label.name" :color="label.color" />
            </div>
            
            <p>{{ content }}</p>
            
            <button class="text-sm font-bold cursor-pointer absolute top-[0.5em] right-[0.5em] bg-neutral-300 rounded-full p-1 hidden group-hover:block hover:bg-neutral-200">
                <img class="w-4 h-4" src="https://unpkg.com/lucide-static@latest/icons/pencil.svg" alt="Edit" />
            </button>
        </div>
    `,

    props: {
        content: String,
        labels: Array
    },
}

export default Card;