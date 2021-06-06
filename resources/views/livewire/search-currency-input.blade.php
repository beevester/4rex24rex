<div class="py-1 rounded shadow max-w-1 my-1 mx-1">
    <div class="m-2">
        <h2 class="py-1">Select the base currency</h2>
        <input
            type="text" class="form-input"
            list="currencies"
            wire:model="selectedCurrency"
        />
        <datalist id="currencies">
            @foreach($currencies as $currency)
                <option>{{ $currency }}</option>
            @endforeach
        </datalist>
        <button wire:click="$emit('updateData' )">Calculate</button>
    </div>
</div>
