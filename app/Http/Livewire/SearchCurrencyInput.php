<?php

namespace App\Http\Livewire;

use App\Http\Controllers\Currency;
use App\Http\Controllers\ExchangeController;
use Livewire\Component;

class SearchCurrencyInput extends Component
{
    public array $currencies = [];

    public string $selectedCurrency = '';

    protected $listeners = ['updateData'];
    /**
     * @var Currency|mixed
     */
    private $currencyService;
    /**
     * @var ExchangeController|mixed
     */
    private $exchangeRate;


    public function updateData()
    {
        dump('test');
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function mount()
    {
        $this->currencyService = new Currency();
        $this->exchangeRate = new ExchangeController();

        $this->currencies = $this->exchangeRate->currencies($this->currencyService->allowableCurrencies);
    }

    public function render()
    {
        return view('livewire.search-currency-input', [
            'test' => 'test ins'
        ]);
    }
}
