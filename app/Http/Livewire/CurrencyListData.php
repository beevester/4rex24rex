<?php

namespace App\Http\Livewire;

use App\Http\Controllers\Currency;
use App\Http\Controllers\ExchangeController;
use App\Models\Rates;
use Livewire\Component;

class CurrencyListData extends Component
{
    public array $baseCurrency = [
        'symbol' => 'ZAR',
        'name' => 'South African Rand',
        'rate' => 1
    ];

    public float $convertAmount = 1;

    public array $currencies = [];

    protected $listeners = ['updateData'];

    /**
     * @var ExchangeController|mixed
     */
    private $exchangeRate;
    /**
     * @var Currency|mixed
     */
    private $currencyService;


    public function updateData()
    {
        $exc = new ExchangeController();

        $cur = $exc->latestRates();
        $rates = Rates::all();
        $this->currencies = [];

        foreach ($rates as $rate) {
            $this->currencies[] = ['symbol' => $rate->symbol, 'name' => $rate->full_name, 'rate' => $cur[$rate->symbol]];
        }
    }

    public function mount()
    {
        $this->updateData();
        $this->baseCurrency = [
            'symbol' => 'ZAR',
            'name' => 'South African Rand',
            'rate' => 1
        ];
    }

    public function render()
    {
        return view('livewire.currency-list-data');
    }
}
