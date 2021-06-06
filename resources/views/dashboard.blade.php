<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('We Are trading') }}
        </h2>
    </x-slot>

    <div class="py-12 flex justify-center">
            <div class="w-10/12 my-10 flex bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="w-3/12 border p2">
                    <livewire:search-currency-input />
                </div>
                <div class="w-8/12 mx2 border p-2">
                    <livewire:currency-list-data />
                </div>
            </div>
    </div>
</x-app-layout>
