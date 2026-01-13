const Label = {
    template: `
        <div
         class="text-xs capitalize text-white py-px px-2 rounded-sm inline-block"
         :class="parseLabelColor(color)"
        >
             {{ name }}
        </div>
    `,

    props: {
        name: String,
        color: String
    },

    methods: {
        parseLabelColor(color) {
            return [
                `bg-[${color}]`,
            ];
        }
    }
}

export default Label;