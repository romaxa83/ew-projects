<?php

namespace App\Console\Commands\Helpers;

use Illuminate\Console\Command;
use Throwable;
use Wezom\Quotes\Models\Terminal;

class TestTermianl extends Command
{
    protected $signature = 'helpers:test-terminal';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $data = [
            [
                'name' => 'Port of Boston, MA',
                'address' => '700 Summer St, South Boston, MA 02127',
                'coords' => "[42.34116222701876, -71.03538353302352]",
                'link' => 'https://maps.app.goo.gl/ToFfvbAe3SFxguVV6',
            ],
            [
                'name' => 'Port of Charleston, SC',
                'address' => '196 Concord St, Charleston, SC 29401',
                'coords' => "[32.781615935895765, -79.9233621631887]",
                'link' => 'https://maps.app.goo.gl/QP6gBPpLzJbjpNqP8',
            ],
            [
                'name' => 'Port of Houston, TX',
                'address' => '1515 E Barbours Cut Blvd #146, La Porte, TX 77571',
                'coords' => "[29.678997829875613, -94.98952009413672]",
                'link' => 'https://maps.app.goo.gl/NbRatUJD2i7nLqMRA',
            ],
            [
                'name' => 'Port of Long Beach, CA',
                'address' => 'Pier F Ave, Long Beach, CA 90802',
                'coords' => "[33.75181322097151, -118.20900638764782]",
                'link' => 'https://maps.app.goo.gl/TU8FN6i7CE41ohN79',
            ],
            [
                'name' => 'Port of Los Angeles, CA',
                'address' => '389 Terminal Way, San Pedro, CA 90731',
                'coords' => "[33.74336964886068, -118.26498991250381]",
                'link' => 'https://maps.app.goo.gl/Zxc2VQR95hDYBuiy8',
            ],
            [
                'name' => 'Port of Miami, FL',
                'address' => '2650 Port Blvd, Miami, FL 33132',
                'coords' => "[25.76936996726194, -80.1396988190787]",
                'link' => 'https://maps.app.goo.gl/j9KDXRMvx6wrZNYh8',
            ],
            [
                'name' => 'Port of Mobile, AL',
                'address' => '250 N Water St, Mobile, AL 36602',
                'coords' => "[30.699324479389965, -88.03508794739898]",
                'link' => 'https://maps.app.goo.gl/Nw6sVEPM3zxGfkxo7',
            ],
            [
                'name' => 'Port of New York and New Jersey',
                'address' => 'Jersey City, NJ 07305',
                'coords' => "[40.675230142209244, -74.05907675719025]",
                'link' => 'https://maps.app.goo.gl/PqhZFa21uoqRBGFR6',
            ],
            [
                'name' => 'Port of Oakland, CA',
                'address' => '575 Maritime St, Oakland, CA 94607',
                'coords' => "[37.806481571240816, -122.31402952021612]",
                'link' => 'https://maps.app.goo.gl/DR8rCeWp6ZVa7UY49',
            ],
            [
                'name' => 'Port of Portland, OR',
                'address' => '7111 NE Alderwood Rd, Portland, OR 97218',
                'coords' => "[45.578816794855435, -122.56182754733113]",
                'link' => 'https://maps.app.goo.gl/u9wsm74VLCjY2zor6',
            ],
            [
                'name' => 'Port of Savannah, GA',
                'address' => 'Tomochichi Rd, Port Wentworth, GA 31407',
                'coords' => "[32.126023318467425, -81.11515353935378]",
                'link' => 'https://maps.app.goo.gl/AKKS1ZsF5j8JaQxt6',
            ],
            [
                'name' => 'Port of Tacoma',
                'address' => 'One Sitcum Plaza, Tacoma, WA 98421',
                'coords' => "[47.271950814056694, -122.35531242205515]",
                'link' => 'https://maps.app.goo.gl/HLrWohxQqpy5mGaZ7',
            ],
            [
                'name' => 'Port of Seattle',
                'address' => '2711 Alaskan Wy, Seattle, WA 98121',
                'coords' => "[47.62166245318395, -122.31725613833177]",
                'link' => 'https://maps.app.goo.gl/WDj4JcRCyPJDnSjD6',
            ],
            [
                'name' => 'Port of Virginia (Norfolk), VA',
                'address' => '1431 Terminal Blvd, Norfolk, VA 23505',
                'coords' => "[36.91846074572085, -76.29948758266278]",
                'link' => 'https://maps.app.goo.gl/B1WJiA6CE5x1G4vG9',
            ],
            [
                'name' => 'Barstow, CA',
                'address' => '200 N H and, Main St, Barstow, CA 92311',
                'coords' => "[34.89417439690593, -117.0771488023491]",
                'link' => 'https://maps.app.goo.gl/w57YeiCqPxo6kdcG9',
            ],
            [
                'name' => 'Chicago, IL',
                'address' => '7000 W 71st St, Bedford Park, IL 60638',
                'coords' => "[41.76444627676211, -87.79337899428494]",
                'link' => 'https://maps.app.goo.gl/RkywzFvEdd79LD2DA',
            ],
            [
                'name' => 'City of Industry, CA',
                'address' => '1722 Arenth Ave, City of Industry, CA 91748',
                'coords' => "[34.00378888565773, -117.90291626385395]",
                'link' => 'https://maps.app.goo.gl/9QFjBScmwcpmpZB78',
            ],
            [
                'name' => 'Conway, PA',
                'address' => '1100 1st Ave, Conway, PA 15027',
                'coords' => "[40.666410785216705, -80.2435077191878]",
                'link' => 'https://maps.app.goo.gl/2Jvz6gfekf3Yp9RLA',
            ],
            [
                'name' => 'Elkhart, IN',
                'address' => '2600 W Lusher Ave, Elkhart, IN 46517',
                'coords' => "[41.666182535636985, -86.0138927867727]",
                'link' => 'https://maps.app.goo.gl/q843SwYXNDEJknVd7',
            ],
            [
                'name' => 'Houston, TX',
                'address' => '6100 Kirkpatrick Blvd, Houston, TX 77028',
                'coords' => "[29.824198128492586, -95.32956420519119]",
                'link' => 'https://maps.app.goo.gl/6QToUUPx1A8NmoE69',
            ],
            [
                'name' => 'Joliet/Elwood, IL',
                'address' => '26664 S Elwood International Port Rd, Elwood, IL 60421',
                'coords' => "[41.40398346370686, -88.11792591962202]",
                'link' => 'https://maps.app.goo.gl/j3t6m6iU4r5RjU1f6',
            ],
            [
                'name' => 'Kansas City, MO',
                'address' => '1100 N 7th St, Kansas City, KS 66101',
                'coords' => "[39.073489694693286, -94.6230807558073]",
                'link' => 'https://maps.app.goo.gl/9u7fmMBrXctrMuv6A',
            ],
            [
                'name' => 'Los Angeles, CA',
                'address' => '731 Lamar St, Los Angeles, CA 90031',
                'coords' => "[34.05666132371713, -118.22830326250649]",
                'link' => 'https://maps.app.goo.gl/LDrFEq1ragFH7KRz5',
            ],
            [
                'name' => 'North Platte, NE',
                'address' => 'Superintendents Ave, North Platte, NE 69101',
                'coords' => "[41.14553070049434, -100.82654872359876]",
                'link' => 'https://maps.app.goo.gl/7DGbhSrrT4qTcwg87',
            ],
            [
                'name' => 'Roseville, CA',
                'address' => '12118 Louisiana 36, Roseville, CA 95747',
                'coords' => "[38.72714200063258, -121.30949400160046]",
                'link' => 'https://maps.app.goo.gl/KhCn2nDpoDPYHuJm9',
            ],
            [
                'name' => 'Stockton/French Camp, CA',
                'address' => '833 E 8th St, Stockton, CA 95206',
                'coords' => "[38.04382054018934, -121.25712462192121]",
                'link' => 'https://maps.app.goo.gl/2AA2yorRaBbidHcx7',
            ],
        ];


        if (Terminal::count() > 0) {
            return static::SUCCESS;
        }

        Terminal::insert($data);


        return static::SUCCESS;
    }
}
