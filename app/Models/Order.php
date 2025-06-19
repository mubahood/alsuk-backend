<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stripe\Customer;

class Order extends Model
{
    use HasFactory;
    //boot
    public static function boot()
    {
        parent::boot();
        //created
        self::created(function ($m) {
            try {
                self::send_mails($m);
            } catch (\Throwable $th) {
                //throw $th;
            }
        });

        //updated
        self::updated(function ($m) {
            try {
                self::send_mails($m);
            } catch (\Throwable $th) {
                //throw $th;
            }
        });


        self::deleting(function ($m) {
            try {
                $items = OrderedItem::where('order', $m->id)->get();
                foreach ($items as $item) {
                    $item->delete();
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        });
    }


    public static function send_mails($m)
    {
        return;
        $customer = User::find($m->user);
        if ($customer == null) {
            return;
        }
        $mail_body_admin = "";
        $mail_body_customer = "";

        $review_url = admin_url('orders/' . $m->id . '/edit');
        if ($m->order_state == 0) {
            $mail_body_admin = <<<EOD
        Dear Administrator, <br>
        A new order has been received. <br>
        Order ID: <b>#{$m->id}</b><br>
        Order Status: Pending <br>
        Total Amount: {$m->total} <br>
        Please review the order by clicking <a href="$review_url">here</a>. <br>
        <br>
        Please do not reply to this email. <br>
        EOD;

            $mail_body_customer = <<<EOD
        Dear Customer, <br>
        Thank you for your order. <br>
        Order ID: <b>#{$m->id}</b><br>
        Order Status: Pending <br>
        Total Amount: {$m->total} <br>
        we will notify you when your order is processed. <br>
        <br>
        Please do not reply to this email. <br>
        EOD;
        }


        echo $mail_body_admin;
        die();


        /* 
        $form->radio('order_state', __('Order State'))
        ->options([
            0 => 'Pending',
            1 => 'Processing',
            2 => 'Completed',
            3 => 'Canceled',
            4 => 'Failed',
        ]);    
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pending_mail_sent')->default('No')->nullable();
            $table->string('processing_mail_sent')->default('No')->nullable();
            $table->string('completed_mail_sent')->default('No')->nullable();
            $table->string('canceled_mail_sent')->default('No')->nullable();
            $table->string('failed_mail_sent')->default('No')->nullable();
            $table->bigInteger('sub_total')->default(0)->nullable();
            $table->bigInteger('tax')->default(0)->nullable();
            $table->bigInteger('discount')->default(0)->nullable();
            $table->bigInteger('delivery_fee')->default(0)->nullable();
        });*/
    }

    public function create_payment_link()
    {
        return;
        $stripe = env('STRIPE_KEY');
        if (($this->stripe_id != null) && (strlen($this->stripe_id) > 0)) {
            return;
        }


        $itmes = $this->get_items();
        $line_items = [];
        foreach ($itmes as $key => $item) {
            $pro = Product::find($item->product);
            if ($pro == null) {
                continue;
            }
            if ($pro->stripe_price == null || strlen($pro->stripe_price) < 3) {
                continue;
            }
            $line_items[] = [
                'price' => $pro->stripe_price,
                'quantity' => $item->qty,
            ];
        }
        if (count($line_items) < 1) {
            $this->delete();
            throw new \Exception("No items to create payment link");
            return;
        }
        $isSuccess = false;
        $resp = "";
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_KEY')
        );
        try {
            $resp = $stripe->paymentLinks->create([
                'currency' => 'cad',
                'line_items' => $line_items,
            ]);
            $isSuccess = true;
        } catch (\Throwable $th) {
            $isSuccess = false;
            $resp = $th->getMessage();
        }

        if ($isSuccess) {
            $this->stripe_id = $resp->id;
            $this->stripe_url = $resp->url;
            $this->stripe_paid = 'No';
            $this->save();
        }
    }
    public function get_items()
    {
        $items = [];
        foreach (
            OrderedItem::where([
                'order' => $this->id
            ])->get() as $_item
        ) {
            $pro = Product::find($_item->product);
            if ($pro == null) {
                continue;
            }
            if ($_item->pro == null) {
                continue;
            }
            $_item->product_name = $_item->pro->name;
            $_item->product_feature_photo = $_item->pro->feature_photo;
            $_item->product_price_1 = $_item->pro->price_1;
            $_item->product_quantity = $_item->qty;
            $_item->product_id = $_item->pro->id;
            $items[] = $_item;
        }
        return $items;
    }

    //belongs to customer
    public function customer()
    {
        return $this->belongsTo(User::class, 'user');
    }

    //get payment link
    public function payment_link()
    {
        if ($this->stripe_url != null && strlen($this->stripe_url) > 5) {
            return $this->stripe_url;
        }

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
    }
}
