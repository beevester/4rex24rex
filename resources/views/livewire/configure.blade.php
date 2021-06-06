<div>
    <div>
        <h2>Set Unit</h2>
        <input value="1"/>
    </div>
    <div>
        <h2>Set Base Currency</h2>
        <select wire:model="firstSelection">
            @foreach($currencies as $currency)
                <option>{{ $currency }}</option>
            @endforeach
        </select>
    </div>
</div>
