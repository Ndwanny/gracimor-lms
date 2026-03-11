<?php

namespace Database\Factories;

/**
 * Trait ZambianNames
 *
 * Provides realistic Zambian names, NRC numbers, phone numbers, and addresses
 * for use in database factories and seeders.
 */
trait ZambianNames
{
    private static array $firstNames = [
        'Mwansa', 'Chanda', 'Bwalya', 'Mutale', 'Temwa', 'Luyando', 'Mulenga',
        'Kasonde', 'Nkandu', 'Chilufya', 'Kayumba', 'Sichone', 'Kapembwa',
        'Mwamba', 'Chitalu', 'Kabwe', 'Lungu', 'Mubanga', 'Chibwe', 'Nsama',
        'Mwape', 'Kabunda', 'Chipimo', 'Sikazwe', 'Mwila', 'Chisanga',
        'Kaputa', 'Namukonda', 'Lombe', 'Mirriam', 'Charity', 'Grace',
        'Moses', 'Aaron', 'David', 'James', 'John', 'Mary', 'Ruth',
        'Esther', 'Peter', 'Paul', 'Joseph', 'Daniel', 'Emmanuel',
        'Felicity', 'Patricia', 'Florence', 'Margaret', 'Catherine',
    ];

    private static array $lastNames = [
        'Banda', 'Phiri', 'Tembo', 'Mwale', 'Zulu', 'Ngoma', 'Mumba',
        'Kapata', 'Musonda', 'Chisanga', 'Mulenga', 'Bwalya', 'Chanda',
        'Lungu', 'Mutale', 'Kasonde', 'Chipimo', 'Sikazwe', 'Sichone',
        'Kaunda', 'Nkonde', 'Kabwe', 'Mwansa', 'Chilufya', 'Chulu',
        'Sakala', 'Nyirenda', 'Phiri', 'Mwamba', 'Chibanda', 'Kamanga',
        'Zamba', 'Ngandu', 'Mwape', 'Chifwepa', 'Mpundu', 'Kabunda',
        'Mwenye', 'Siame', 'Chikwanda', 'Nkemba', 'Mulenga', 'Kanyanta',
    ];

    public static array $zambianTowns = [
        'Lusaka', 'Kitwe', 'Ndola', 'Livingstone', 'Kabwe', 'Chingola',
        'Mufulira', 'Luanshya', 'Kasama', 'Chipata', 'Solwezi', 'Mongu',
        'Mazabuka', 'Kafue', 'Choma', 'Mansa', 'Nakonde', 'Mbala',
        'Petauke', 'Lundazi', 'Kapiri Mposhi', 'Mkushi',
    ];

    private static array $streets = [
        'Cairo Road', 'Church Road', 'Independence Avenue', 'Nationalist Road',
        'Great East Road', 'Great North Road', 'Los Angeles Boulevard',
        'Alick Nkhata Road', 'Addis Ababa Drive', 'Freedom Way',
        'Lumumba Road', 'Katimamulilo Road', 'Nangwenya Road',
    ];

    private static array $mobileNetworks = [
        '097', '096', '095', '077', '076', '075',
    ];

    public function zambianFirst(): string
    {
        return static::$firstNames[array_rand(static::$firstNames)];
    }

    public function zambianLast(): string
    {
        return static::$lastNames[array_rand(static::$lastNames)];
    }

    public function zambianNrc(): string
    {
        $serial = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $year   = random_int(60, 99);
        $gender = random_int(1, 2);
        return "{$serial}/{$year}/{$gender}";
    }

    public function zambianPhone(): string
    {
        $prefix = static::$mobileNetworks[array_rand(static::$mobileNetworks)];
        $number = str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        return '+260' . ltrim($prefix, '0') . $number;
    }

    public function zambianAddress(): string
    {
        $plot   = random_int(1000, 99999);
        $street = static::$streets[array_rand(static::$streets)];
        $town   = static::$zambianTowns[array_rand(static::$zambianTowns)];
        return "Plot {$plot}, {$street}, {$town}";
    }
}
