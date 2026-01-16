import lists from '../../data/db.js';
import List from "./List.js";
import Layout from "./Layout.js";
import Board from "./Board.js";

const App = {
    components: {
        Layout, List, Board
    },

    template: `
        <Layout>
            <template v-slot:heading>
                <h1 class="font-bold text-3xl text-center">My board</h1>
            </template>
        
            <template v-slot:default>
                <Board>
                    <template v-slot:default>
                        <List v-for="list in lists" :id="list.id" :title="list.title" :cards="list.cards" @create-card="handleCreateCard"/>
                    </template>
                </Board>
            </template>
        </Layout>
    `,

    data( ) {
        return {
            lists
        }
    },

    methods: {
        handleCreateCard(formData) {
            const list = this.lists.find((list) => list.id === formData.listId);

            list.cards.push({
                id: new Date().getTime(),
                content: formData.content,
                labels: []
            });
        }
    }
}

export default App;