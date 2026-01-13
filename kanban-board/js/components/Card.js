import Label from "./Label.js";

const Card = {
    components: { Label },
    template: `
        <div class="bg-neutral-100 rounded-sm px-2 py-1 shadow-sm space-y-1">
            <div class="space-x-1">
                <Label v-for="label in labels" :name="label.name" :color="label.color" />
            </div>
            <p>{{ content }}</p>
        </div>
    `,

    props: {
        content: String,
        labels: Array
    },
}

export default Card;