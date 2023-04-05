<?php

namespace App\Console\Commands\LoadTesting;

use App\Models\City;
use App\Models\Country;
use App\Models\Geoobject;
use Http;
use Illuminate\Console\Command;
use Illuminate\Support\Benchmark;

class HitEndpointsCommand extends Command
{
    protected $signature = 'load-testing:hit-endpoints';

    protected $description = 'Command description';

    public function handle(): void
    {
        $city = City::inRandomOrder()->first(['id']);
        $country = Country::inRandomOrder()->first(['id']);
        $geoobject = Geoobject::inRandomOrder()->first(['id']);
        $urlBase = config('app.url');

        $timeForAdultsSearch = Benchmark::measure(function () use ($urlBase) {
            Http::get(sprintf("%s/api/search?adults=%s&children=%s", $urlBase, 2, 5));
        });
        dump(sprintf('Search by adults and children took %s ms', $timeForAdultsSearch));

        $timeForAdultsAndCountrySearch = Benchmark::measure(function () use ($urlBase, $country) {
            Http::get(sprintf("%s/api/search?adults=%s&children=%s&country=%s", $urlBase, 2, 5, $country->id));
        });
        dump(sprintf('Search by adults and children and country took %s ms', $timeForAdultsAndCountrySearch));

        $timeForAdultsAndCitySearch = Benchmark::measure(function () use ($urlBase, $city) {
            Http::get(sprintf("%s/api/search?adults=%s&children=%s&city=%s", $urlBase, 2, 5, $city->id));
        });
        dump(sprintf('Search by adults and children and city took %s ms', $timeForAdultsAndCitySearch));

        $timeForAdultsAndGeoObjectSearch = Benchmark::measure(function () use ($urlBase, $geoobject) {
            Http::get(sprintf("%s/api/search?adults=%s&children=%s&geoobject=%s", $urlBase, 2, 5, $geoobject->id));
        });
        dump(sprintf('Search by adults and children and geoobject took %s ms', $timeForAdultsAndGeoObjectSearch));

        $timeForAdultsAndGeoObjectAndPriceSearch = Benchmark::measure(function () use ($urlBase, $geoobject) {
            Http::get(sprintf("%s/api/search?adults=%s&children=%s&geoobject=%s&price_from=%s&price_to=%s", $urlBase, 2, 5, $geoobject->id, 5.00, 100.00));
        });
        dump(sprintf('Search by adults and children and geoobject and price took %s ms', $timeForAdultsAndGeoObjectAndPriceSearch));
    }
}
