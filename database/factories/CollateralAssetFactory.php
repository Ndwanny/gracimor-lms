<?php

namespace Database\Factories;

use App\Models\Borrower;
use App\Models\CollateralAsset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollateralAssetFactory extends Factory
{
    use ZambianNames;

    protected $model = CollateralAsset::class;

    private static array $vehicleMakes  = ['Toyota', 'Nissan', 'Isuzu', 'Honda', 'Mazda', 'Mitsubishi', 'Ford'];
    private static array $vehicleModels = [
        'Toyota'      => ['Hilux', 'Land Cruiser', 'Vigo', 'Corolla', 'Avensis'],
        'Nissan'      => ['Hardbody', 'Navara', 'X-Trail', 'Sunny', 'Tiida'],
        'Isuzu'       => ['D-Max', 'KB250', 'MU-X'],
        'Honda'       => ['CRV', 'Fit', 'Accord', 'Civic'],
        'Mazda'       => ['BT-50', 'CX-5', '3 Sedan'],
        'Mitsubishi'  => ['Pajero', 'L200', 'Outlander'],
        'Ford'        => ['Ranger', 'Everest', 'Focus'],
    ];
    private static array $colours = ['Silver', 'White', 'Black', 'Blue', 'Red', 'Grey', 'Maroon', 'Green'];
    private static array $zambiaRegPrefixes = ['ABD', 'ABC', 'ABJ', 'ABA', 'ABL', 'ABR', 'ABS', 'ACF'];

    public function definition(): array
    {
        $type = fake()->randomElement(['vehicle', 'vehicle', 'land']); // 2:1 vehicle:land
        $assetDef = $type === 'vehicle' ? $this->vehicleDef() : $this->landDef();

        return array_merge($assetDef, [
            'borrower_id' => Borrower::factory(),
            'created_by'  => User::factory(),
        ]);
    }

    private function vehicleDef(): array
    {
        $make   = static::$vehicleMakes[array_rand(static::$vehicleMakes)];
        $model  = static::$vehicleModels[$make][array_rand(static::$vehicleModels[$make])];
        $year   = fake()->numberBetween(2010, 2024);
        $colour = static::$colours[array_rand(static::$colours)];
        $prefix = static::$zambiaRegPrefixes[array_rand(static::$zambiaRegPrefixes)];
        $regNo  = $prefix . fake()->numerify(' ####');
        $value  = fake()->numberBetween(80000, 600000);
        $valDate= fake()->dateTimeBetween('-5 months', 'now');

        return [
            'asset_type'           => 'vehicle',
            'vehicle_registration' => $regNo,
            'vehicle_make'         => $make,
            'vehicle_model'        => $model,
            'vehicle_year'         => $year,
            'vehicle_color'        => $colour,
            'estimated_value'      => $value,
            'valuation_date'       => $valDate->format('Y-m-d'),
            'valuation_firm'       => fake()->randomElement([
                'Galaxy Motors Zambia', 'Central Autos', 'Drive Zambia Valuers',
                'Automark Zambia', 'ZANACO Motors', null,
            ]),
            'plot_number'          => null,
            'title_deed_number'    => null,
            'land_address'         => null,
            'status'               => 'available',
        ];
    }

    private function landDef(): array
    {
        $town    = static::$zambianTowns[array_rand(static::$zambianTowns)];
        $plot    = fake()->numerify('Plot #####');
        $value   = fake()->numberBetween(150000, 2000000);
        $valDate = fake()->dateTimeBetween('-5 months', 'now');

        return [
            'asset_type'           => 'land',
            'plot_number'          => $plot,
            'title_deed_number'    => 'LUS/' . fake()->numerify('####') . '/' . fake()->numberBetween(2010, 2024),
            'land_address'         => "{$plot}, off {$town} central business district, {$town}",
            'estimated_value'      => $value,
            'valuation_date'       => $valDate->format('Y-m-d'),
            'valuation_firm'       => fake()->randomElement([
                'Zambia Institute of Estate Agents', 'Knight Frank Zambia',
                'Savills Zambia', 'Real Estate Zambia', null,
            ]),
            'vehicle_registration' => null,
            'vehicle_make'         => null,
            'vehicle_model'        => null,
            'vehicle_year'         => null,
            'vehicle_color'        => null,
            'status'               => 'available',
        ];
    }

    // ── Named states ──────────────────────────────────────────────────────────

    public function vehicle(): static
    {
        return $this->state(fn () => $this->vehicleDef());
    }

    public function land(): static
    {
        return $this->state(fn () => $this->landDef());
    }

    public function pledged(): static
    {
        return $this->state(['status' => 'pledged']);
    }

    public function recentValuation(): static
    {
        return $this->state(['valuation_date' => now()->subDays(30)->format('Y-m-d')]);
    }
}
