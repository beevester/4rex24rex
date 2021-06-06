<div class="m-4 w-72 border-collapse border">
    <h1>{{ $baseCurrency['name'] }} Exchange Rates Table</h1>

    <table class="table-fixed w-12/12 container w-full">
        <thead>
            <tr>
                <th class="w-1/2">{{ $baseCurrency['name'] ?? '' }}</th>
                <th class="w-1/4">{{ $convertAmount }} {{ $baseCurrency['symbol'] }}</th>
                <th class="w-1/4">inv. {{ $baseCurrency['rate']  ?? ''}} {{ $baseCurrency['symbol']  ?? ''}}</th>
            </tr>
        </thead>
        @if($currencies != [])
            <tbody >
            @foreach($currencies as $currency)
                <tr>
                    <td>{{ $currency['name']  ?? ''}}</td>
                    <td class="rtRates">{{ number_format($convertAmount / $currency['rate'], 3) }}</td>
                    <td class="rtRates">{{ number_format($currency['rate'], 3)  ?? ''}}</td>
                </tr>
            @endforeach
            </tbody>
        @endif
    </table>
</div>
