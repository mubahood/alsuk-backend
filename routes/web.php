<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\MainController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\Gen;
use App\Models\Order;
use App\Models\Utils;
use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;



Route::get('mail-test', function () {

    $lastOrder = Order::orderBy('id', 'desc')->first();
    $lastOrder->order_state = 0;
    $lastOrder->stripe_url .= '1';
    $lastOrder->save();
    die('mail-test');
    Order::send_mails($lastOrder);

    $data['body'] = 'This should be the body of the <b>email</b>.';
    $data['data'] = $data['body'];
    $data['name'] = 'Hohn peter';
    $data['email'] = 'mubahood360@gmail.com';
    $data['subject'] = 'TEST UGANDA ' . ' - M-Omulimisa';

    Utils::mail_sender($data);
    die("success");
});

Route::get('test', function () {

    return;

    $stripe = env('STRIPE_KEY');
    $stripe = new \Stripe\StripeClient(
        env('STRIPE_KEY')
    );

    $name = 'Order payment for ' . date('Y-m-d H:i:s') . " " . rand(1, 100000);

    $resp = null;
    try {
        $resp = $stripe->products->create([
            'name' => $name,
            'default_price_data' => [
                'currency' => 'cad',
                'unit_amount' => 1 * 100,
            ],
        ]);
    } catch (\Throwable $th) {
        throw $th;
    }
    if ($resp == null) {
        throw new \Exception("Error Processing Request", 1);
    }
    if ($resp->default_price == null) {
        throw new \Exception("Error Processing Request", 1);
    }
    $linkResp = null;
    try {
        $linkResp = $stripe->paymentLinks->create([
            'currency' => 'cad',
            'line_items' => [
                [
                    'price' => $resp->default_price,
                    'quantity' => 1,
                ]
            ]
        ]);
    } catch (\Throwable $th) {
        throw $th;
    }
    if ($linkResp == null) {
        throw new \Exception("Error Processing Request", 1);
    }
});



Route::get('migrate', function () { 
    Artisan::call('migrate', ['--force' => true]); 
    return Artisan::output();
});

Route::get('clear', function () {
    $output = [];
    
    // Clear all caches and configs
    $commands = [
        'config:clear' => 'Configuration cache cleared',
        'cache:clear' => 'Application cache cleared',
        'route:clear' => 'Route cache cleared',
        'view:clear' => 'View cache cleared',
        'optimize:clear' => 'Optimize cache cleared',
        'optimize' => 'Application optimized'
    ];
    
    foreach ($commands as $command => $message) {
        try {
            Artisan::call($command);
            $output[] = "✅ {$message}";
        } catch (Exception $e) {
            $output[] = "❌ Failed to run {$command}: " . $e->getMessage();
        }
    }
    
    // Dump autoload
    try {
        exec('composer dump-autoload -o 2>&1', $composerOutput, $returnCode);
        if ($returnCode === 0) {
            $output[] = "✅ Composer autoload dumped";
        } else {
            $output[] = "❌ Failed to dump composer autoload: " . implode("\n", $composerOutput);
        }
    } catch (Exception $e) {
        $output[] = "❌ Composer dump-autoload failed: " . $e->getMessage();
    }
    
    $output[] = "\n🎉 All caches and configs cleared successfully!";
    $output[] = "Timestamp: " . now()->format('Y-m-d H:i:s');
    
    return response()->json([
        'success' => true,
        'message' => 'Cache clearing completed',
        'details' => $output,
        'raw_output' => Artisan::output()
    ])->header('Content-Type', 'application/json');
});

Route::get('clear-cors', function () {
    $output = [];
    
    // Clear specific caches related to CORS and API
    $commands = [
        'config:clear' => 'Configuration cache cleared (includes CORS config)',
        'route:clear' => 'Route cache cleared (includes API routes)',
        'cache:clear' => 'Application cache cleared'
    ];
    
    foreach ($commands as $command => $message) {
        try {
            Artisan::call($command);
            $output[] = "✅ {$message}";
        } catch (Exception $e) {
            $output[] = "❌ Failed to run {$command}: " . $e->getMessage();
        }
    }
    
    $output[] = "\n🌐 CORS and API caches cleared successfully!";
    $output[] = "Your CORS configuration should now be active.";
    $output[] = "Timestamp: " . now()->format('Y-m-d H:i:s');
    
    return response()->json([
        'success' => true,
        'message' => 'CORS cache clearing completed',
        'details' => $output,
        'cors_config' => config('cors'),
        'raw_output' => Artisan::output()
    ])->header('Content-Type', 'application/json');
});

Route::get('artisan', function (Request $request) {
    // Artisan::call('migrate');
    //do run laravel migration command
    //php artisan l5-swagger:generate
    Artisan::call($request->command, ['--force' => true]);
    //returning the output
    return Artisan::output();
});




Route::match(['get', 'post'], '/pay', function () {
    $id = 1;
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    } else {
        $order = \App\Models\Order::first();
        $id = $order->id;
    }
    $order = \App\Models\Order::find($id);
    $customer = $order->customer;
    //dd($customer);
    // $order->amount = 1;
    // $order->save();

    $task = null;
    if (isset($_GET['task'])) {
        $task = $_GET['task'];
    }
    if ($task == "success") {
        $order->payment_confirmation = 1;
        $data['get'] = $_GET;
        $data['post'] = $_POST;
        $order->stripe_id = json_encode($data);
        $order->save();
        die("Payment was successful");
    } else if ($task == "canceled") {
        $data['get'] = $_GET;
        $data['post'] = $_POST;
        $order->stripe_url = json_encode($data);
        $order->save();
        die("Payment was canceled");
    } else if ($task == "update") {
        $data['task'] = $task;
        $data['get'] = $_GET;
        $data['post'] = $_POST;
        $order->order_details = json_encode($data);
        $order->save();
        //return 200 response
        return response()->json(['status' => 'success', 'message' => 'Payment was updated.']);
    }

    $base_link = url('/pay?id=' . $id);
    return view('pay', [
        'order' => $order,
        'base_link' => $base_link
    ]);
});
Route::get('/process', function () {

    //set_time_limit(0);
    set_time_limit(-1);
    //ini_set('memory_limit', '1024M');
    ini_set('memory_limit', '-1');

    $folderPath2 = base_path('public/temp/pics/final');
    $folderPath = base_path('public/temp/pics/');
    $biggest = 0;
    $tot = 0;

    // Check if the folder exists
    if (is_dir($folderPath)) {
        // Get the list of items in the folder
        $items = scandir($folderPath);
        $items_1 = scandir($folderPath2);

        $i = 0;


        // Loop through the items
        foreach ($items as $item) {

            // Exclude the current directory (.) and parent directory (..)
            if ($item != '.' && $item != '..') {


                $ext = pathinfo($item, PATHINFO_EXTENSION);
                if ($ext == null) {
                    continue;
                }
                $ext = strtolower($ext);


                if (!in_array($ext, [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                ])) {
                    continue;
                }

                $target = $folderPath . $item;
                $target_file_size = filesize($target);

                $target_file_size_to_mb = $target_file_size / (1024 * 1024);
                $target_file_size_to_mb = round($target_file_size_to_mb, 2);
                /* if($target_file_size_to_mb > 2){
                    $source = $target;
                    $dest = $folderPath . "final/" . $item;
                    Utils::create_thumbail([
                        'source' => $source,
                        'target' => $dest
                    ]);
                    unlink($source); 
                } */


                if ($target_file_size > $biggest) {
                    $biggest = $target_file_size;
                }
                $tot += $target_file_size;


                continue;
                //echo $i.". ".$item . "<br>";
                $i++;
                continue;

                $i++;
                print_r($i . "<br>");



                $fileSize = filesize($folderPath . "/" . $item);
                $fileSize = $fileSize / (1024 * 1024);
                $fileSize = round($fileSize, 2);
                $fileSize = $fileSize . " MB";
                $url = "http://localhost:8888/ham/public/temp/pics-1/" . $item;

                $source = $folderPath . "/" . $item;
                $target = $folderPath . "/thumb/" . $item;
                Utils::create_thumbail([
                    'source' => $source,
                    'target' => $target
                ]);

                echo "<img src='$url' alt='$item' width='550'/>";
                $target_file_size = filesize($target);
                $target_file_size = $target_file_size / (1024 * 1024);
                $target_file_size = round($target_file_size, 2);
                $target_file_size = $target_file_size . " MB";
                $url_2 = "http://localhost:8888/ham/public/temp/pics-1/thumb/" . $item;
                echo "<img src='$url_2' alt='$item' width='550' />";



                // Print the item's name
                echo "<b>" . $fileSize . "<==>" . $target_file_size . "<b><br>";
            }
        }
    } else {
        echo "The specified folder does not exist.";
    }

    $biggest = $biggest / (1024 * 1024);
    $biggest = round($biggest, 2);
    $biggest = $biggest . " MB";
    $tot = $tot / (1024 * 1024);
    $tot = round($tot, 2);
    $tot = $tot . " MB";
    echo "Biggest: " . $biggest . "<br>";
    echo "Total: " . $tot . "<br>";
    die("=>done<=");
});
Route::get('/sync', function () {
    Utils::sync_products();
    Utils::sync_orders();
})->name("sync");
Route::get('/gen', function () {
    die(Gen::find($_GET['id'])->do_get());
})->name("gen");
Route::get('/gen-form', function () {
    die(Gen::find($_GET['id'])->make_forms());
})->name("gen-form");
Route::get('generate-class', [MainController::class, 'generate_class']);
Route::get('/gen-products', function () {
    $lastPro = \App\Models\Product::orderBy('id', 'desc')->first();

    $images_folder = base_path('public/storage/images/');
    //check if folder exists
    if (!is_dir($images_folder)) {
        die("Folder does not exist");
    }
    $images_in_folder = scandir($images_folder);
    $images_in_folder = array_filter($images_in_folder, function ($v) {
        return !in_array($v, ['.', '..', '.gitignore']);
    });
    $images_in_folder = array_values($images_in_folder);
    $images_in_folder = array_map(function ($v) {
        return $v;
    }, $images_in_folder);

    $productTitles = [
        // Segment 1: Phones & Accessories (40 items)
        "Apple iPhone 13 Pro Max",
        "Apple iPhone 13 Pro",
        "Apple iPhone 13 Mini",
        "Apple iPhone 13",
        "Samsung Galaxy S21 Ultra 5G",
        "Samsung Galaxy S21+ 5G",
        "Samsung Galaxy S21 5G",
        "Samsung Galaxy Note 20 Ultra",
        "Google Pixel 6 Pro",
        "Google Pixel 6",
        "OnePlus 9 Pro 5G",
        "OnePlus 9 5G",
        "Xiaomi Mi 11 Ultra",
        "Xiaomi Redmi Note 11 Pro",
        "Huawei P40 Pro",
        "Huawei Mate 40 Pro",
        "Sony Xperia 1 III",
        "Sony Xperia 5 III",
        "LG Velvet 5G",
        "Nokia 8.3 5G",
        "Motorola Edge 20 Pro",
        "Motorola Moto G Power",
        "Oppo Find X3 Pro",
        "Oppo Reno 6 Pro",
        "Realme GT 5G",
        "Vivo X60 Pro",
        "Lenovo Legion Phone Duel",
        "Asus ROG Phone 5",
        "Black Shark 4 Pro",
        "ZTE Axon 30 Ultra",
        "Apple AirPods Pro",
        "Samsung Galaxy Buds Pro",
        "Google Pixel Buds A-Series",
        "OnePlus Buds Z",
        "Sony WF-1000XM4",
        "JBL Tune 130TWS",
        "Xiaomi Mi True Wireless Earphones",
        "Huawei FreeBuds Pro",
        "Motorola VerveBuds 400",
        "Oppo Enco X",

        // Segment 2: Audio & Headphones (30 items)
        "Sony WH-1000XM4 Wireless Headphones",
        "Bose QuietComfort 35 II",
        "Sennheiser Momentum 3",
        "JBL Live 650BTNC",
        "Beats Studio3 Wireless",
        "Bang & Olufsen Beoplay H9",
        "Apple AirPods Max",
        "Anker Soundcore Life Q30",
        "Plantronics BackBeat Go 810",
        "AKG N700NC M2",
        "Skullcandy Crusher ANC",
        "Bowers & Wilkins PX7",
        "Marshall Monitor II ANC",
        "Audio-Technica ATH-M50x",
        "Jabra Elite 85h",
        "Creative SXFI L100",
        "Bose Sport Earbuds",
        "JBL Charge 5 Portable Speaker",
        "Ultimate Ears BOOM 3",
        "Sonos Roam",
        "Bose SoundLink Revolve+",
        "Marshall Stanmore II",
        "LG XBOOM Go PL7",
        "Sony SRS-XB43",
        "JBL PartyBox 310",
        "Pioneer DJ DM-40",
        "Beats Pill+",
        "Harman Kardon Onyx Studio 6",
        "Bose Portable Smart Speaker",
        "Bowers & Wilkins Formation Wedge",

        // Segment 3: Computers & Laptops (30 items)
        "Dell XPS 13 Ultrabook",
        "Dell XPS 15 Touch Laptop",
        "Apple MacBook Pro 16-inch",
        "Apple MacBook Air M1",
        "HP Spectre x360 Convertible",
        "HP Envy 15",
        "Lenovo ThinkPad X1 Carbon",
        "Lenovo Yoga Slim 7",
        "Asus ZenBook 14",
        "Asus ROG Zephyrus G14",
        "Acer Swift 3",
        "Acer Predator Helios 300",
        "Microsoft Surface Laptop 4",
        "Microsoft Surface Pro 7",
        "Razer Blade 15",
        "MSI GS66 Stealth",
        "Gigabyte Aero 15",
        "Alienware m15 R6",
        "HP Omen 15",
        "Dell Inspiron 15 7000",
        "Apple iMac 24-inch",
        "Dell Precision 5550",
        "Lenovo ThinkStation P340",
        "HP ZBook Fury 15 G7",
        "Asus ProArt StudioBook Pro 17",
        "MSI Creator 15",
        "Acer ConceptD 7",
        "Razer Book 13",
        "Samsung Galaxy Book Pro 360",
        "LG Gram 17",

        // Segment 4: Cameras & Photography (20 items)
        "Canon EOS R5 Mirrorless Camera",
        "Canon EOS 5D Mark IV",
        "Nikon Z7 II",
        "Nikon D850 DSLR",
        "Sony Alpha A7 III",
        "Sony Alpha A6400",
        "Fujifilm X-T4",
        "Fujifilm X100V",
        "Panasonic Lumix GH5",
        "Olympus OM-D E-M1 Mark III",
        "Leica Q2 Full-Frame Compact",
        "GoPro HERO10 Black",
        "DJI Osmo Action",
        "Sigma 35mm f/1.4 Art Lens",
        "Tamron 28-75mm f/2.8 Di III VC Lens",
        "Canon EF 70-200mm f/2.8L IS III Lens",
        "Nikon AF-S NIKKOR 50mm f/1.8G Lens",
        "Sony FE 24-70mm f/2.8 GM Lens",
        "Manfrotto Tripod Pro",
        "Rode VideoMic Pro+",

        // Segment 5: Gaming & Consoles (20 items)
        "Sony PlayStation 5 Console",
        "Microsoft Xbox Series X",
        "Nintendo Switch OLED Model",
        "Nintendo Switch Lite",
        "Razer Kishi Mobile Game Controller",
        "Logitech G Pro Wireless Gaming Mouse",
        "Corsair K95 RGB Platinum Keyboard",
        "HyperX Cloud II Gaming Headset",
        "SteelSeries Rival 3 Gaming Mouse",
        "ASUS ROG Strix Scope Keyboard",
        "MSI Optix MAG272C Gaming Monitor",
        "BenQ ZOWIE XL2546K",
        "Acer Predator XB273K Monitor",
        "Alienware AW3420DW Curved Monitor",
        "Razer Tomahawk Gaming Desktop",
        "HP OMEN 25L Gaming Desktop",
        "MSI Trident X Plus",
        "NZXT H510 Elite",
        "Corsair iCUE 4000X RGB Case",
        "Logitech G502 HERO Gaming Mouse",

        // Segment 6: Smart Home & Devices (20 items)
        "Amazon Echo Dot (4th Gen)",
        "Amazon Echo Show 8",
        "Google Nest Hub Max",
        "Google Nest Mini",
        "Apple HomePod mini",
        "Philips Hue White & Color Ambiance Starter Kit",
        "LIFX Wi-Fi Smart LED Bulb",
        "TP-Link Kasa Smart Plug",
        "Ring Video Doorbell 4",
        "Arlo Pro 3 Wireless Security Camera",
        "Nest Cam Indoor",
        "Wyze Cam v3",
        "SimpliSafe Home Security System",
        "Eufy Security Smart Lock",
        "August Smart Lock Pro",
        "Ecobee SmartThermostat",
        "Samsung SmartThings Hub",
        "Rachio 3 Smart Sprinkler Controller",
        "Netatmo Weather Station",
        "Belkin WeMo Insight Switch",

        // Segment 7: Printers, Monitors, Networking & Others (20 items)
        "HP OfficeJet Pro 9015 All-in-One Printer",
        "Canon PIXMA TS9120 Wireless Printer",
        "Epson EcoTank ET-4760 Printer",
        "Brother HL-L2350DW Laser Printer",
        "Dell UltraSharp U2720Q 4K Monitor",
        "LG UltraFine 27UL850 Monitor",
        "Samsung Odyssey G7 Monitor",
        "Acer Nitro XV272U Monitor",
        "ASUS TUF Gaming VG27AQ Monitor",
        "BenQ PD2700U Designer Monitor",
        "Netgear Nighthawk AX12 Router",
        "TP-Link Archer AX6000 Router",
        "Asus RT-AX88U WiFi 6 Router",
        "Linksys Velop Mesh WiFi System",
        "D-Link AC3000 WiFi Router",
        "Ubiquiti UniFi Dream Machine",
        "Seagate 4TB External Hard Drive",
        "Western Digital 2TB Portable HDD",
        "SanDisk 1TB Portable SSD",
        "Corsair RM750x Power Supply",

        // Segment 8: Wearables & Gadgets (20 items)
        "Apple Watch Series 7",
        "Samsung Galaxy Watch 4",
        "Fitbit Versa 3",
        "Garmin Forerunner 245",
        "Huawei Watch GT 2 Pro",
        "Fossil Gen 5 Smartwatch",
        "Xiaomi Mi Band 6",
        "Amazfit Bip U Pro",
        "Sony SmartWatch 3",
        "Moto 360 Smartwatch",
        "Oculus Quest 2 VR Headset",
        "HTC Vive Pro 2 VR Headset",
        "Valve Index VR Kit",
        "DJI Mavic Air 2 Drone",
        "Parrot Anafi Drone",
        "Anker PowerCore 20000mAh",
        "RavPower 65W PD Charger",
        "Belkin BoostCharge Wireless Charger",
        "Logitech MX Master 3 Wireless Mouse",
        "Microsoft Surface Precision Mouse"
    ];

    $prices = [
        600000,
        500000,
        400000,
        300000,
        200000,
        150000,
        100000,
        90000,
        80000,
        50000,
        40000,
        10000,
        5000,
        750000,
        600000,
        1000000,
        1200000,
        1500000,
        2000000,
        2500000,
        3000000,
        3200000,
        3300000,
        3400000,
        3500000,
        1000000,
        1400000,
        1500000,
    ];

    foreach ($images_in_folder as $key => $value) {
        $segs = explode('.', $value);
        $first = $segs[0];
        $numb = (int) $first;
        if ($numb == 0) {
            continue;
        }
        if ($numb < 12) {
            continue;
        }
        if ($numb > 145) {
            continue;
        }

        $existingPro = \App\Models\Product::where('metric', $numb)->first();
        if ($existingPro != null) {
            continue;
        }

        $product = new \App\Models\Product();
        $product->name =  $productTitles[rand(0, count($productTitles) - 1)];
        $product->metric = $numb;
        $product->currency = 1;
        $product->description = null;
        shuffle($prices);
        $product->price_1 =  $prices[0];
        $product->price_2 =  $prices[1];
        $product->feature_photo = 'images/' . $value;
        $product->rates = 1;
        $date_added = Carbon::now();
        $product->date_added = $date_added->addDays(-rand(1, 600));
        $product->date_updated = $date_added->addDays(rand(1, 600));
        $product->user = 1;
        $product->category = rand(2, 12);
        $product->supplier = 1;
        $product->status = 1;
        $product->in_stock = 1;
        $product->keywords = '[{"id":"","min_qty":1,"max_qty":5,"price":"800"},{"id":"","min_qty":6,"max_qty":10,"price":"900"}]';
        $product->local_id = Utils::get_unique_text();
        $product->updated_at = $product->date_added;
        $product->created_at = $product->date_added;
        $product->has_colors = "Yes";
        $product->colors = '["Black","Blue"]';
        $product->has_sizes = "Yes";
        $product->sizes = '["12","43"]';
        $product->p_type = ['Yes', 'No'][rand(0, 1)];
        $product->save();

        //create next 5 images for the product
        for ($i = 1; $i < 6; $i++) {
            $img = new \App\Models\Image();
            $img->src = 'images/' . ($numb + ($i - 1)) . '.jpg';
            $img->thumbnail = 'images/' . ($numb + ($i - 1)) . '.jpg';
            $img->parent_id = 0;
            $img->size = 0;
            $img->administrator_id = 1;
            $img->type = 'product';
            $img->product_id = $product->id;
            $img->parent_endpoint = 'products';
            $img->note = 'Product image';
            $img->is_processed = 1;
            $img->parent_local_id = $numb;
            $img->save();
        }
    }


    return 'gen-products';
});
