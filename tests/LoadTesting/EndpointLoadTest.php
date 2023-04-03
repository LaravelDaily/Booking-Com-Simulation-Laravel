<?php

namespace Tests\LoadTesting;

use App\Models\Apartment;
use App\Models\City;
use App\Models\Country;
use App\Models\Geoobject;
use App\Models\Property;
use Illuminate\Support\Benchmark;

class EndpointLoadTest extends LoadTestingBase
{
    protected int $timeTargetInMS = 1000;

    public function test_benchmark_empty_property_search(): void
    {
        $time = Benchmark::measure(function () {
            $this->getJson('/api/search');
        });
        $this->assertLessThan($this->timeTargetInMS, $time, 'Search endpoint took too long to respond:');
    }

    public function test_benchmark_filled_property_search(): void
    {
        $city = City::inRandomOrder()->first(['id']);
        $country = Country::inRandomOrder()->first(['id']);
        $geoobject = Geoobject::inRandomOrder()->first(['id']);

        $timeForCitySearch = Benchmark::measure(function () use ($city) {
            $this->getJson(sprintf("/api/search?city=%s", $city->id));
        });
        $this->assertLessThan($this->timeTargetInMS, $timeForCitySearch, 'Search by city took too long to respond:');

        $timeForCountrySearch = Benchmark::measure(function () use ($country) {
            $this->getJson(sprintf("/api/search?country=%s", $country->id));
        });
        $this->assertLessThan($this->timeTargetInMS, $timeForCountrySearch, 'Search by country took too long to respond:');

        $timeForGeoobjectSearch = Benchmark::measure(function () use ($geoobject) {
            $this->getJson(sprintf("/api/search?geoobject=%s", $geoobject->id));
        });
        $this->assertLessThan($this->timeTargetInMS, $timeForGeoobjectSearch, 'Search by geoobject took too long to respond:');

        $timeForAdultsSearch = Benchmark::measure(function () {
            $this->getJson(sprintf("/api/search?adults=%s&children=%s", 2, 5));
        });
        $this->assertLessThan($this->timeTargetInMS, $timeForAdultsSearch, 'Search by adults and children took too long to respond:');

        $timeForPriceSearch = Benchmark::measure(function () {
            $this->getJson(sprintf("/api/search?price_from=%s", 5.00));
        });
        $this->assertLessThan(1000, $timeForPriceSearch, 'Search by price from took too long to respond:');

        $timeForPriceSearch = Benchmark::measure(function () {
            $this->getJson(sprintf("/api/search?price_to=%s", 100.00));
        });
        $this->assertLessThan(1000, $timeForPriceSearch, 'Search by price to took too long to respond:');

        $timeForPriceSearch = Benchmark::measure(function () {
            $this->getJson(sprintf("/api/search?price_from=%s&price_to=%s", 5.00, 100.00));
        });
        $this->assertLessThan(1000, $timeForPriceSearch, 'Search by price from and price to took too long to respond:');
    }

    public function test_load_property_information(): void
    {
        $property = Property::inRandomOrder()->first(['id']);

        $timeForPropertyLoad = Benchmark::measure(function () use ($property) {
            $this->getJson(sprintf("/api/properties/%s", $property->id));
        });
        $this->assertLessThan($this->timeTargetInMS, $timeForPropertyLoad, 'Property information took too long to respond:');
    }

    public function test_load_apartment_information(): void
    {
        $apartment = Apartment::inRandomOrder()->first(['id']);

        $timeForApartmentLoad = Benchmark::measure(function () use ($apartment) {
            $this->getJson(sprintf("/api/apartments/%s", $apartment->id));
        });
        $this->assertLessThan($this->timeTargetInMS, $timeForApartmentLoad, 'Apartment information took too long to respond:');
    }
}