import List from "./List.js";

const App = {
    components: {
        List,
    },
    template: `
        <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 flex flex-col gap-8">
            <header class="bg-black/20 py-4 shadow-sm">
                <h1 class="font-bold text-3xl text-center">My board</h1>
            </header>

            <main class="h-full">
                <div class="h-full px-2 overflow-x-auto">
                    <div id="board" class="grid auto-cols-[20em] grid-flow-col gap-2 items-start h-full">
                        <List title="Backlog" :cards="{}" />
                        <List title="To Do" :cards="{}" />
                        <List title="In Progress" :cards="{}" />
                        <List title="Review" :cards="{}" />
                        <List title="Done" :cards="{}" />
                        
                        <button class="bg-black/20 py-4 rounded-md shadow-sm cursor-pointer hover:bg-black/10 font-bold text-sm text-black">
                            + Add another list
                        </button>
                    </div>
                </div>
            </main>
        </div>
    `,

    data( ) {
        return {
            lists: [
                {
                    id: 1,
                    title: 'Backlog',
                    cards: [
                        {
                            id: 1,
                            content: 'Add Authentication',
                            labels: [
                                {
                                    name: 'Frontend',
                                    color: '#ffffff',
                                },
                                {
                                    name: 'Backend',
                                    color: '#ffffff',
                                }
                            ]
                        },
                        {
                            id: 1,
                            content: 'Add Authentication',
                            labels: [
                                {
                                    name: 'Frontend',
                                    color: '#ffffff',
                                },
                                {
                                    name: 'Backend',
                                    color: '#ffffff',
                                }
                            ]
                        },
                        {
                            id: 1,
                            content: 'Add Authentication',
                            labels: [
                                {
                                    name: 'Frontend',
                                    color: '#ffffff',
                                },
                                {
                                    name: 'Backend',
                                    color: '#ffffff',
                                }
                            ]
                        },
                    ]
                }
            ]
        }
    }
}

export default App;