<x-filament::page>
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.5rem;">
        
        <!-- Card 1 -->
        <section class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="padding: 20px">
            <div class="p-6">
                <div class="flex items-center gap-x-3">
                    <x-filament::icon icon="heroicon-o-cube" class="h-6 w-6 text-primary-500" />
                    <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Products
                    </h2>
                </div>
                <p class="mt-2 text-sm text-gray-500">Manage your inventory items.</p>
                <div class="mt-4">
                    <x-filament::link :href="route('filament.admin.resources.products.index')">
                        Open Products
                    </x-filament::link>
                </div>
            </div>
        </section>

        <!-- Card 2 -->
        <section class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="padding: 20px">
            <div class="p-6">
                <div class="flex items-center gap-x-3">
                    <x-filament::icon icon="heroicon-o-building-storefront" class="h-6 w-6 text-success-500" />
                    <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Warehouses
                    </h2>
                </div>
                <p class="mt-2 text-sm text-gray-500">View storage locations.</p>
                <div class="mt-4">
                    <x-filament::link :href="route('filament.admin.resources.warehouses.index')">
                        Open Warehouses
                    </x-filament::link>
                </div>
            </div>
        </section>

        <!-- Card 3 -->
        <section class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="padding: 20px">
            <div class="p-6">
                <div class="flex items-center gap-x-3">
                    <x-filament::icon icon="heroicon-o-arrow-path" class="h-6 w-6 text-warning-500" />
                    <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Movements
                    </h2>
                </div>
                <p class="mt-2 text-sm text-gray-500">Track stock history.</p>
                <div class="mt-4">
                    <x-filament::link :href="route('filament.admin.resources.stock-movements.index')">
                        Open Movements
                    </x-filament::link>
                </div>
            </div>
        </section>

    </div>
</x-filament::page>