<x-filament::page>
    <div class="space-y-6">
        <!-- Scanner Card -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-camera" class="h-5 w-5" />
                    <span>Barcode Scanner</span>
                </div>
            </x-slot>
            
            <div class="flex flex-col items-center text-center space-y-6">
                <div class="rounded-full bg-primary-50 p-4 dark:bg-primary-950">
                    {{-- <x-filament::icon icon="heroicon-o-qr-code" class="h-16 w-16 text-primary-600 dark:text-primary-400" /> --}}
                </div>
                
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Position your cursor in the field below and scan a barcode
                </p>
                
                {{ $this->form }}
            </div>
        </x-filament::section>
        
        <!-- Product Results -->
        @if($product)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-cube" class="h-5 w-5" />
                        <span>Product Found</span>
                    </div>
                </x-slot>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Product Name:</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $product->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">SKU:</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $product->sku }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Barcode:</p>
                        <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $product->barcode ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Current Stock:</p>
                        <p class="text-xl font-bold {{ $product->stock_status === 'Low Stock' ? 'text-warning-600' : ($product->stock_status === 'Out of Stock' ? 'text-danger-600' : 'text-success-600') }}">
                            {{ $product->current_stock }} {{ $product->unit }}
                        </p>
                    </div>
                </div>
                
                <div class="mt-6 flex flex-wrap gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                    <x-filament::button wire:click="addStock" tag="button" color="primary">
                        <x-filament::icon icon="heroicon-o-plus-circle" class="h-5 w-5 mr-1" />
                        Add/Remove Stock
                    </x-filament::button>
                    
                    <x-filament::button wire:click="viewProduct" tag="button" color="gray">
                        <x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5 mr-1" />
                        Edit Product
                    </x-filament::button>
                    
                    <x-filament::button wire:click="$set('product', null)" tag="button" color="gray" outlined>
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5 mr-1" />
                        Scan Another
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('focus-barcode', () => {
                setTimeout(() => {
                    const input = document.querySelector('input[name="barcode"]');
                    if (input) input.focus();
                }, 100);
            });
            
            setTimeout(() => {
                const input = document.querySelector('input[name="barcode"]');
                if (input) input.focus();
            }, 500);
        });
    </script>
    @endpush
</x-filament::page>