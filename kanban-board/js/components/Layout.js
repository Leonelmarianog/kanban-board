const Layout = {
    template: `
        <div class="h-screen bg-linear-to-r from-cyan-500 to-blue-500 flex flex-col gap-8">
            <header class="bg-black/20 py-4 shadow-sm">
                <slot name="heading" />
            </header>
           
            <main class="h-full">
                <div class="h-full px-2 overflow-x-auto">
                    <slot name="default" />
                </div>
            </main>
        </div>
    `
}

export default Layout;