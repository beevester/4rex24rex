<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    {{ $exchangeRate }}

    <div class="m-4 max-w-md py-4 px-8 bg-white shadow-lg rounded-lg my-1 mx-1">
        <select wire:model="firstSelection">
            @foreach($currencies as $currency)
                <option>{{ $currency }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <h2>{{ $exchangeRate }}</h2>
        <button wire:click="updateRate">test</button>
    </div>
    <div class="m-4 max-w-md py-4 px-8 bg-white shadow-lg rounded-lg my-20">
        <select wire:model="secondSelection">
            @foreach($currencies as $currency)
                <option>{{ $currency }}</option>
            @endforeach
        </select>
    </div>
</div>
