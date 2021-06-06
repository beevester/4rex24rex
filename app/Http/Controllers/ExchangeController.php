<?php

namespace App\Http\Controllers;

use App\Exceptions\ExchangeRateException;
use App\Exceptions\InvalidCurrencyException;
use App\Exceptions\InvalidDateException;
use App\Models\Rates;
use App\Models\Settings;
use App\Support\CacheRepository;
use App\Support\ConfigCache;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExchangeController extends Controller
{
    /**
     * The repository used for accessing the cache.
     *
     * @var CacheRepository
     */
    private $cacheRepository;

    /**
     * Whether of not the exchange rate should be cached
     * after being fetched from the API.
     *
     * @var bool
     */
    private bool $shouldCache = true;

    /**
     * Whether or not the cache should be busted and a new
     * value should be fetched from the API.
     *
     * @var bool
     */
    private bool $shouldBustCache = false;
    /**
    * The object used for making requests to the currency
    * conversion API.
    * @var FixerRequestController|null
    */
    private ?FixerRequestController $fixerApiClient;


    public function __construct(FixerRequestController $fixerApiClient = null, CacheRepository $cacheRepository = null)
    {
        $this->fixerApiClient = $fixerApiClient ?? new FixerRequestController();
        $this->cacheRepository = $cacheRepository ?? new CacheRepository();
    }

    /**
     * Return an array of available currencies that
     * can be used with this package.
     *
     * @return array
     * @throws GuzzleException
     */
    public function currencies(array $currencies = []): array
    {
        $cacheKey = 'currencies';

        if ($cachedExchangeRate = $this->attemptToResolveFromCache($cacheKey)) {
            return $cachedExchangeRate;
        }

        $response = $this->fixerApiClient->getData('/latest', []);

        $currencies[] = $response['base'];

        foreach ($response['rates'] as $currency => $rate) {
            $currencies[] = $currency;
        }

        if ($this->shouldCache) {
            $this->cacheRepository->storeInCache($cacheKey, $currencies);
        }

        return $currencies;
    }

    /**
     * Enables the change on the base currency that will be used
     * for the service.
     *
     * @param string $base
     * @return array
     * @throws GuzzleException
     */
    public function changeBaseCurrency(string $base): array
    {
         return $this->fixerApiClient->getData('/latest', ['base' => $base]);
    }

    /**
     * Get the users base currency that is store in the db
     *
     * @return mixed
     */
    public function gatBaseCurrency()
    {
        $result = Settings::findByUserId(Auth::id());

        return $result->base_currency;
    }


    public function updateSymbols()
    {
        $symbols = $this->fixerApiClient->getData('/symbols', []);
        $result = [];
        foreach($symbols['symbols'] as $key => $value) {
            $result[] = ['symbol' => $key, 'full_name' => $value];
        }
        Rates::insert($result);
    }


    public function latestRates(): array
    {
        $response = $this->fixerApiClient->getData('/latest', []);

        return $response['rates'];
    }

    /**
     * Return the exchange rate between the $from and $to
     * parameters. If no $date parameter is passed, we
     * use today's date instead. If $to is a string,
     * the exchange rate will be returned as a
     * string. If $to is an array, the rates
     * will be returned within an array.
     *
     * @param string $from
     * @param string|array $to
     * @param Carbon|null $date
     *
     * @return string|array
     *
     * @throws InvalidDateException
     * @throws InvalidCurrencyException
     * @throws ExchangeRateException
     * @throws GuzzleException
     */
    public function exchangeRate(string $from, $to, Carbon $date = null)
    {
        Validation::validateIsStringOrArray($to);

        if ($date) {
            Validation::validateDate($date);
        }

        Validation::validateCurrencyCode($from);

        is_string($to) ? Validation::validateCurrencyCode($to) : Validation::validateCurrencyCodes($to);

        if ($from === $to) {
            return 1.0;
        }

        $symbols = is_string($to) ? $to : implode(',', $to);
        $queryParams = ['base' => $from, 'symbols' => $symbols];

        $url = $date
            ? '/'.$date->format('Y-m-d')
            : '/latest';

        $response = $this->fixerApiClient->getData($url, $queryParams)['rates'];

        return is_string($to) ? $response[$to] : $response;
    }

    /**
     * Return the exchange rates between the given
     * date range.
     *
     * @param  string  $from
     * @param  string|array  $to
     * @param  Carbon  $date
     * @param  Carbon  $endDate
     * @param  array  $conversions
     *
     * @return array
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function exchangeRateBetweenDateRange(
        string $from,
        $to,
        Carbon $date,
        Carbon $endDate,
        array $conversions = []
    ): array {
        Validation::validateCurrencyCode($from);
        Validation::validateStartAndEndDates($date, $endDate);
        Validation::validateIsStringOrArray($to);

        is_string($to) ? Validation::validateCurrencyCode($to) : Validation::validateCurrencyCodes($to);

        return $from === $to
            ? $this->exchangeRateDateRangeResultWithSameCurrency($date, $endDate, $conversions)
            : $conversions = $this->makeRequestForExchangeRates($from, $to, $date, $endDate);
    }

    /**
     * Make a request to the Exchange Rates API to get the
     * exchange rates between a date range. If only one
     * currency is being used, we flatten the array
     * to remove currency symbol before returning
     * it.
     *
     * @param  string  $from
     * @param  string|array  $to
     * @param  Carbon  $date
     * @param  Carbon  $endDate
     *
     * @return array
     *
     * @throws GuzzleException
     */
    private function makeRequestForExchangeRates(string $from, $to, Carbon $date, Carbon $endDate): array
    {
        $symbols = is_string($to) ? $to : implode(',', $to);

        $result = $this->requestBuilder->makeRequest('/timeseries', [
            'base'     => $from,
            'start_date' => $date->format('Y-m-d'),
            'end_date'   => $endDate->format('Y-m-d'),
            'symbols'  => $symbols,
        ]);

        $conversions = $result['rates'];

        if (is_string($to)) {
            foreach ($conversions as $date => $rate) {
                $conversions[$date] = $rate[$to];
            }
        }

        ksort($conversions);

        return $conversions;
    }

    /**
     * Return the converted values between the $from and $to
     * parameters. If no $date parameter is passed, we
     * use today's date instead.
     *
     * @param  int  $value
     * @param  string  $from
     * @param  string|array  $to
     * @param  Carbon|null  $date
     *
     * @return float|array
     *
     * @throws InvalidDateException
     * @throws InvalidCurrencyException
     * @throws ExchangeRateException
     * @throws GuzzleException
     */
    public function convert(int $value, string $from, $to, Carbon $date = null)
    {
        if (is_string($to)) {
            return (float) $this->exchangeRate($from, $to, $date) * $value;
        }

        $exchangeRates = $this->exchangeRate($from, $to, $date);

        foreach ($exchangeRates as $currency => $exchangeRate) {
            $exchangeRates[$currency] = (float) $exchangeRate * $value;
        }

        return $exchangeRates;
    }

    /**
     * Return an array of the converted values between the
     * given date range.
     *
     * @param  int  $value
     * @param  string  $from
     * @param  string|array  $to
     * @param  Carbon  $date
     * @param  Carbon  $endDate
     * @param  array  $conversions
     *
     * @return array
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function convertBetweenDateRange(
        int $value,
        string $from,
        $to,
        Carbon $date,
        Carbon $endDate,
        array $conversions = []
    ): array {
        $exchangeRates = $this->exchangeRateBetweenDateRange($from, $to, $date, $endDate);

        if (is_array($to)) {
            foreach ($exchangeRates as $date => $exchangeRate) {
                foreach ($exchangeRate as $currency => $rate) {
                    $conversions[$date][$currency] = (float) $rate * $value;
                }
            }

            return $conversions;
        }

        foreach ($exchangeRates as $date => $exchangeRate) {
            $conversions[$date] = (float) $exchangeRate * $value;
        }

        return $conversions;
    }

    /**
     * If the 'from' and 'to' currencies are the same, we
     * don't need to make a request to the API. Instead,
     * we can build the response ourselves to improve
     * the performance.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @param  array  $conversions
     *
     * @return array
     */
    private function exchangeRateDateRangeResultWithSameCurrency(
        Carbon $startDate,
        Carbon $endDate,
        array $conversions = []
    ): array {
        for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekday()) {
                $conversions[$date->format('Y-m-d')] = 1.0;
            }
        }

        return $conversions;
    }

    /**
     * Determine whether if the exchange rate should be
     * cached after it is fetched from the API.
     *
     * @param  bool  $shouldCache
     *
     * @return $this
     */
    public function shouldCache(bool $shouldCache = true): self
    {
        $this->shouldCache = $shouldCache;

        return $this;
    }

    /**
     * Determine whether if the cached result (if it
     * exists) should be deleted. This will force
     * a new exchange rate to be fetched from
     * the API.
     *
     * @param  bool  $bustCache
     *
     * @return $this
     */
    public function shouldBustCache(bool $bustCache = true): self
    {
        $this->shouldBustCache = $bustCache;

        return $this;
    }

    /**
     * Attempt to fetch an item (more than likely an
     * exchange rate) from the cache. If it exists,
     * return it. If it has been specified, bust
     * the cache.
     *
     * @param  string  $cacheKey
     *
     * @return mixed
     */
    private function attemptToResolveFromCache(string $cacheKey)
    {
        if ($this->shouldBustCache) {
            $this->cacheRepository->forget($cacheKey);
            $this->shouldBustCache = false;
        } elseif ($cachedValue = $this->cacheRepository->getFromCache($cacheKey)) {
            return $cachedValue;
        }
    }

}
