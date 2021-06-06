<?php

namespace App\Http\Livewire;

use App\Http\Controllers\Currency;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\FixerRequestController;
use GuzzleHttp\Client;
use Livewire\Component;

class ExchangeRate extends Component
{
    public string $baseCurrency;

    public float $exchangeRate = 1;

    public array $currencies = [];

    public string $firstSelection = '';

    public string $secondSelection = '';

    public function mount()
    {
        $currencyService = new Currency();
        $exchangeRate = new ExchangeController();

        $this->currencies = $exchangeRate->currencies($currencyService->allowableCurrencies);
        $this->firstSelection = $this->currencies[0];
        $this->secondSelection = $this->currencies[0];
    }

    public function render()
    {
        return view('livewire.exchange-rate');
    }

    public function updateRate()
    {
        $exRate = new ExchangeController();

        $exRate->changeBaseCurrency('ZAR');

        $this->exchangeRate = $exRate->exchangeRate($this->firstSelection, $this->secondSelection);
    }
}
