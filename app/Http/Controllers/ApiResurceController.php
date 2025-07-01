<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\ChatHead;
use App\Models\ChatMessage;
use App\Models\CounsellingCentre;
use App\Models\Crop;
use App\Models\CropProtocol;
use App\Models\DeliveryAddress;
use App\Models\Event;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\Group;
use App\Models\Image;
use App\Models\Institution;
use App\Models\Job;
use App\Models\Location;
use App\Models\NewsPost;
use App\Models\Order;
use App\Models\OrderedItem;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class ApiResurceController extends Controller
{

    use ApiResponser;


    public function become_vendor(Request $request)
    {
        $administrator_id = $request->user;

        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (
            $request->first_name == null ||
            strlen($request->first_name) < 2
        ) {
            return $this->error('First name is missing.');
        }
        //validate all
        if (
            $request->last_name == null ||
            strlen($request->last_name) < 2
        ) {
            return $this->error('Last name is missing.');
        }

        //validate all
        if (
            $request->business_name == null ||
            strlen($request->business_name) < 2
        ) {
            return $this->error('Business name is missing.');
        }

        if (
            $request->business_license_number == null ||
            strlen($request->business_license_number) < 2
        ) {
            return $this->error('Business license number is missing.');
        }

        if (
            $request->business_license_issue_authority == null ||
            strlen($request->business_license_issue_authority) < 2
        ) {
            return $this->error('Business license issue authority is missing.');
        }

        if (
            $request->business_license_issue_date == null ||
            strlen($request->business_license_issue_date) < 2
        ) {
            return $this->error('Business license issue date is missing.');
        }

        if (
            $request->business_license_validity == null ||
            strlen($request->business_license_validity) < 2
        ) {
            return $this->error('Business license validity is missing.');
        }

        if (
            $request->business_address == null ||
            strlen($request->business_address) < 2
        ) {
            return $this->error('Business address is missing.');
        }

        if (
            $request->business_phone_number == null ||
            strlen($request->business_phone_number) < 2
        ) {
            return $this->error('Business phone number is missing.');
        }

        if (
            $request->business_whatsapp == null ||
            strlen($request->business_whatsapp) < 2
        ) {
            return $this->error('Business whatsapp is missing.');
        }

        if (
            $request->business_email == null ||
            strlen($request->business_email) < 2
        ) {
            return $this->error('Business email is missing.');
        }




        $msg = "";
        $u->first_name = $request->first_name;
        $u->last_name = $request->last_name;
        $u->nin = $request->campus_id;
        $u->business_name = $request->business_name;
        $u->business_license_number = $request->business_license_number;
        $u->business_license_issue_authority = $request->business_license_issue_authority;
        $u->business_license_issue_date = $request->business_license_issue_date;
        $u->business_license_validity = $request->business_license_validity;
        $u->business_address = $request->business_address;
        $u->business_phone_number = $request->business_phone_number;
        $u->business_whatsapp = $request->business_whatsapp;
        $u->business_email = $request->business_email;
        $u->business_cover_photo = $request->business_cover_photo;
        $u->business_cover_details = $request->business_cover_details;


        if ($u->status != 'Active') {
            $u->status = 'Pending';
        }

        $images = [];
        if (!empty($_FILES)) {
            $images = Utils::upload_images_2($_FILES, false);
        }
        if (!empty($images)) {
            $u->business_logo = 'images/' . $images[0];
        }

        $code = 1;
        try {
            $u->save();
            $msg = "Submitted successfully.";
            return $this->success($u, $msg, $code);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $code = 0;
            return $this->error($msg);
        }
        return $this->success(null, $msg, $code);
    }


    public function update_profile(Request $request)
    {
        $administrator_id = $request->user;

        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (
            $request->first_name == null ||
            strlen($request->first_name) < 2
        ) {
            return $this->error('First name is missing.');
        }
        //validate all
        if (
            $request->last_name == null ||
            strlen($request->last_name) < 2
        ) {
            return $this->error('Last name is missing.');
        }

        if (
            $request->phone_number_1 == null ||
            strlen($request->phone_number_1) < 5
        ) {
            return $this->error('Phone number is requried.');
        }

        $anotherUser = Administrator::where([
            'phone_number' => $request->phone_number_1
        ])->first();
        if ($anotherUser != null) {
            if ($anotherUser->id != $u->id) {
                return $this->error('Phone number is already taken.');
            }
        }

        $anotherUser = Administrator::where([
            'username' => $request->phone_number_1
        ])->first();
        if ($anotherUser != null) {
            if ($anotherUser->id != $u->id) {
                return $this->error('Phone number is already taken.');
            }
        }

        $anotherUser = Administrator::where([
            'email' => $request->phone_number_1
        ])->first();
        if ($anotherUser != null) {
            if ($anotherUser->id != $u->id) {
                return $this->error('Phone number is already taken.');
            }
        }

        if (
            $request->email != null &&
            strlen($request->email) > 5
        ) {
            $anotherUser = Administrator::where([
                'email' => $request->email
            ])->first();
            if ($anotherUser != null) {
                if ($anotherUser->id != $u->id) {
                    return $this->error('Email is already taken.');
                }
            }
            //check for username as well
            $anotherUser = Administrator::where([
                'username' => $request->email
            ])->first();
            if ($anotherUser != null) {
                if ($anotherUser->id != $u->id) {
                    return $this->error('Email is already taken.');
                }
            }
            //validate email
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('Invalid email address.');
            }
        }



        $msg = "";
        //first letter to upper case
        $u->first_name = $request->first_name;

        //change first letter to upper case
        $u->first_name = ucfirst($u->first_name);


        $u->last_name = ucfirst($request->last_name);
        $u->phone_number = $request->phone_number_1;
        $u->email = $request->email;
        $u->address = ucfirst($request->address);

        $images = [];
        if (!empty($_FILES)) {
            $images = Utils::upload_images_2($_FILES, false);
        }
        if (!empty($images)) {
            $u->avatar = 'images/' . $images[0];
        }

        $code = 1;
        try {
            $u->save();
            $msg = "Updated successfully.";
            return $this->success($u, $msg, $code);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $code = 0;
            return $this->error($msg);
        }
        return $this->success(null, $msg, $code);
    }


    public function delete_profile(Request $request)
    {
        $administrator_id = $request->user;

        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $u->status = 'Deleted';
        $u->save();
        return $this->success(null, $message = "Deleted successfully!", 1);

        if (
            $request->first_name == null ||
            strlen($request->first_name) < 2
        ) {
            return $this->error('First name is missing.');
        }
        //validate all
        if (
            $request->last_name == null ||
            strlen($request->last_name) < 2
        ) {
            return $this->error('Last name is missing.');
        }

        if (
            $request->phone_number_1 == null ||
            strlen($request->phone_number_1) < 5
        ) {
            return $this->error('Phone number is requried.');
        }

        $anotherUser = Administrator::where([
            'phone_number' => $request->phone_number_1
        ])->first();
        if ($anotherUser != null) {
            if ($anotherUser->id != $u->id) {
                return $this->error('Phone number is already taken.');
            }
        }

        $anotherUser = Administrator::where([
            'username' => $request->phone_number_1
        ])->first();
        if ($anotherUser != null) {
            if ($anotherUser->id != $u->id) {
                return $this->error('Phone number is already taken.');
            }
        }

        $anotherUser = Administrator::where([
            'email' => $request->phone_number_1
        ])->first();
        if ($anotherUser != null) {
            if ($anotherUser->id != $u->id) {
                return $this->error('Phone number is already taken.');
            }
        }

        if (
            $request->email != null &&
            strlen($request->email) > 5
        ) {
            $anotherUser = Administrator::where([
                'email' => $request->email
            ])->first();
            if ($anotherUser != null) {
                if ($anotherUser->id != $u->id) {
                    return $this->error('Email is already taken.');
                }
            }
            //check for username as well
            $anotherUser = Administrator::where([
                'username' => $request->email
            ])->first();
            if ($anotherUser != null) {
                if ($anotherUser->id != $u->id) {
                    return $this->error('Email is already taken.');
                }
            }
            //validate email
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('Invalid email address.');
            }
        }



        $msg = "";
        //first letter to upper case
        $u->first_name = $request->first_name;

        //change first letter to upper case
        $u->first_name = ucfirst($u->first_name);


        $u->last_name = ucfirst($request->last_name);
        $u->phone_number = $request->phone_number_1;
        $u->email = $request->email;
        $u->address = ucfirst($request->address);

        $images = [];
        if (!empty($_FILES)) {
            $images = Utils::upload_images_2($_FILES, false);
        }
        if (!empty($images)) {
            $u->avatar = 'images/' . $images[0];
        }

        $code = 1;
        try {
            $u->save();
            $msg = "Updated successfully.";
            return $this->success($u, $msg, $code);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $code = 0;
            return $this->error($msg);
        }
        return $this->success(null, $msg, $code);
    }

    public function password_change(Request $request)
    {
        $administrator_id = $request->user;

        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (
            $request->password == null ||
            strlen($request->password) < 2
        ) {
            return $this->error('Password is missing.');
        }

        //check if  current_password 
        if (
            $request->current_password == null ||
            strlen($request->current_password) < 2
        ) {
            return $this->error('Current password is missing.');
        }

        //check if  current_password
        if (
            !(password_verify($request->current_password, $u->password))
        ) {
            return $this->error('Current password is incorrect.');
        }

        $u->password = password_hash($request->password, PASSWORD_DEFAULT);
        $msg = "";
        $code = 1;
        try {
            $u->save();
            $msg = "Password changed successfully.";
            return $this->success($u, $msg, $code);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $code = 0;
            return $this->error($msg);
        }
        return $this->success(null, $msg, $code);
    }

    public function account_verification(Request $request)
    {
        $administrator_id = $request->user;
        $u = User::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if ($request->task == null) {
            return $this->error('Task is missing.');
        }

        if (
            $request->email == null ||
            strlen($request->email) < 2
        ) {
            return $this->error('Email is missing.');
        }

        $other_user = User::where([
            'email' => $request->email
        ])->first();

        if ($other_user != null) {
            if ($other_user->id != $u->id) {
                return $this->error('Email is already taken.');
            }
        }
        $other_user = User::where([
            'username' => $request->email
        ])->first();
        if ($other_user != null) {
            if ($other_user->id != $u->id) {
                return $this->error('Email is already taken.');
            }
        }

        if ($request->task == 'request_verification_code') {
            try {
                $u->send_verification_code($request->email);
            } catch (\Throwable $th) {
                return $this->error('Failed to send verification code because ' . $th->getMessage() . '.');
            }
            return $this->success($u, 'Verification code sent to your email address ' . $u->email . '.');
        } else if ($request->task == 'verify_code') {
            $code = $request->code;
            if ($code == null || strlen($code) < 3) {
                return $this->error('Code is required.');
            }
            if ($u->intro != $code) {
                return $this->error('Invalid code.');
            }
            $u->complete_profile = 'Yes';
            $u->email = $request->email;
            $u->username = $request->email;
            try {
                $u->save();
            } catch (\Throwable $th) {
                return $this->error('Failed to verify email because ' . $th->getMessage() . '.');
            }
            return $this->success($u, 'Email verified successfully.');
        }
        return $this->error('Task not found.');
    }



    public function upload_media(Request $request)
    {
        $administrator_id = $request->user;

        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (
            !isset($request->parent_local_id) ||
            $request->parent_local_id == null
        ) {
            return $this->error('Local parent ID is missing.');
        }

        //  strlen($request->parent_local_id) < 6
        if (
            strlen($request->parent_local_id) < 6
        ) {
            return $this->error('Local parent ID is too short.');
        }


        if (
            empty($_FILES)
        ) {
            return $this->error('No files found.');
        }



        $images = Utils::upload_images_2($_FILES, false);
        $_images = [];


        if (empty($images)) {
            return $this->error('Failed to upload files.');
        }

        $msg = "";
        foreach ($images as $src) {

            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  null;
            $img->parent_endpoint =  $request->parent_endpoint;
            $img->parent_local_id =  $request->parent_local_id;
            $img->type =  $request->type;
            $img->parent_id =  (int)($request->parent_id);
            $pro = Product::where(['local_id' => $img->parent_local_id])->first();
            $img->product_id =  null;
            if ($pro != null) {
                $img->product_id =  $pro->id;
            }
            $img->size = 0;
            $img->note = '';
            if (
                isset($request->note)
            ) {
                $img->note =  $request->note;
            }
            $img->save();
            $_images[] = $img;
        }

        return $this->success(
            null,
            count($_images) . " Files uploaded successfully."
        );
    }



    public function vendors(Request $r)
    {
        $vendors = Administrator::where([
            'user_type' => 'Vendor'
        ])->get();
        return $this->success($vendors, $message = "Success!", 200);
    }


    public function order(Request $r)
    {

        $order = Order::find($r->id);
        if ($order == null) {
            return $this->error('Order not found.');
        }

        if ($order->stripe_url == null || strlen($order->stripe_url) < 8) {
            /*   $order->create_payment_link();
            $order->save(); */
        }

        return $this->success($order, $message = "Success!", 200);
    }

    //product_get_by_id
    public function product_get_by_id(Request $r)
    {
        $product = Product::find($r->id);
        if ($product == null) {
            return $this->error('Product not found.');
        }
        return $this->success($product, $message = "Success!", 200);
    }

    //orders_get_by_id
    public function orders_get_by_id(Request $r)
    {
        $order = Order::find($r->id);
        if ($order == null) {
            return $this->error('Order not found.');
        }
        return $this->success($order, $message = "Success!", 200);
    }


    public function orders_get(Request $r)
    {

        $u = auth('api')->user();
        if ($u == null) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);
        }

        if ($u == null) {
            return $this->error('User not found.');
        }
        $orders = [];

        foreach (
            Order::where([
                'user' => $u->id
            ])->get() as $order
        ) {
            $items = $order->get_items();
            $order->items = json_encode($items);
            $orders[] = $order;
        }
        return $this->success($orders, $message = "Success!", 200);
    }


    public function orders_cancel(Request $r)
    {

        $u = auth('api')->user();
        if ($u == null) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);
        }

        if ($u == null) {
            return $this->error('User not found.');
        }

        $order = Order::find($r->id);
        if ($order == null) {
            return $this->error('Order not found.');
        }
        $order->delete();
        return $this->success(null, $message = "Cancelled successfully!", 200);
    }
    public function my_profile(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);
        }

        if ($u == null) {
            return $this->error('User not found.');
        }
        $data[] = $u;
        return $this->success($data, $message = "Success!", 200);
    }


    public function orders_create(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);
        }


        if ($u == null) {
            return $this->error('User not found.');
        }

        $items = [];
        try {
            $items = json_decode($r->items);
        } catch (\Throwable $th) {
            $items = [];
        }
        foreach ($items as $key => $value) {
            $p = Product::find($value->product_id);
            if ($p == null) {
                return $this->error("Product #" . $value->product_id . " not found.");
            }
        }


        $delivery = null;
        try {
            $delivery = json_decode($r->delivery);
        } catch (\Throwable $th) {
            $delivery = null;
        }

        if ($delivery == null) {
            return $this->error('Delivery information is missing.');
        }
        if ($delivery->customer_phone_number_1 == null) {
            $delivery->customer_phone_number_1 = $u->phone_number;
        }

        $order = new Order();
        $order->user = $u->id;
        $order->order_state = 0;
        $order->temporary_id = 0;
        $order->amount = 0;
        $order->order_total = 0;
        $order->payment_confirmation = '';
        $order->description = '';
        $order->mail = $u->email;
        $delivery_amount = 0;
        if ($delivery != null) {
            try {

                $order->order_details = json_encode($delivery);

                $del_loc = DeliveryAddress::find($delivery->delivery_district);
                if ($del_loc != null) {


                    $delivery_amount = (int)($del_loc->shipping_cost);

                    $order->date_created = $delivery->date_created;
                    $order->date_updated = $delivery->date_updated;
                    $order->mail = $delivery->mail;
                    $order->delivery_district = $delivery->delivery_district;
                    $order->description = $delivery->description;
                    $order->customer_name = $delivery->customer_name;
                    $order->customer_phone_number_1 = $delivery->customer_phone_number_1;
                    $order->customer_phone_number_2 = $delivery->customer_phone_number_2;
                    $order->customer_address = $delivery->customer_address;
                }
            } catch (\Throwable $th) {
            }
        }

        $order->save();


        $order_total = 0;
        foreach ($items as $key => $item) {
            $product = Product::find($item->product_id);
            if ($product == null) {
                return $this->error("Product #" . $item->product_id . " not found.");
            }
            $oi = new OrderedItem();
            $oi->order = $order->id;
            $oi->product = $item->product_id;
            $oi->qty = $item->product_quantity;
            $oi->amount = $product->price_1;
            $oi->color = $item->color;
            $oi->size = $item->size;
            $order_total += ($product->price_1 * $oi->qty);
            $oi->save();
        }
        $order->amount = $order_total + $delivery_amount;
        $order->order_total = $order->amount;


        $order->save();
        $order = Order::find($order->id);


        return $this->success($order, $message = "Submitted successfully!", 200);
    }



    public function orders_submit(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);
        }

        $items = [];
        try {
            $items = json_decode($r->items);
        } catch (\Throwable $th) {
            $items = [];
        }
        foreach ($items as $key => $value) {
            $p = Product::find($value->product_id);
            if ($p == null) {
                return $this->error("Product #" . $value->product_id . " not found.");
            }
        }

        if ($u == null) {
            return $this->error('User not found.');
        }

        $delivery = null;
        try {
            $delivery = json_decode($r->delivery);
        } catch (\Throwable $th) {
            $delivery = null;
        }

        if ($delivery == null) {
            return $this->error('Delivery information is missing.');
        }
        if ($delivery->phone_number == null) {
            return $this->error('Phone number is missing.');
        }

        $order = new Order();
        $order->user = $u->id;
        $order->order_state = 0;
        $order->temporary_id = 0;
        $order->amount = 0;
        $order->order_total = 0;
        $order->payment_confirmation = '';
        $order->description = '';
        $order->mail = $u->email;
        $order->date_created = Carbon::now();
        $order->date_updated = Carbon::now();
        if ($delivery != null) {
            try {
                $order->customer_phone_number_1 = $delivery->phone_number;
                $order->customer_phone_number_2 = $delivery->phone_number_2;
                $order->customer_name = $delivery->first_name . " " . $delivery->last_name;
                $order->customer_address = $delivery->current_address;
                $order->delivery_district = $delivery->current_address;
                $order->order_details = json_encode($delivery);
            } catch (\Throwable $th) {
            }
        }

        $order->save();


        $order_total = 0;
        foreach ($items as $key => $item) {
            $product = Product::find($item->product_id);
            if ($product == null) {
                return $this->error("Product #" . $item->product_id . " not found.");
            }
            $oi = new OrderedItem();
            $oi->order = $order->id;
            $oi->product = $item->product_id;
            $oi->qty = $item->product_quantity;
            $oi->amount = $product->price_1;
            $oi->color = $item->color;
            $oi->size = $item->size;
            $order_total += ($product->price_1 * $oi->qty);
            $oi->save();
        }
        $order->order_total = $order_total;
        $order->amount = $order_total;
        $order->save();

        /* if ($order->stripe_url == null || strlen($order->stripe_url) < 6) {
            $order->create_payment_link();
            $order->save();
        } */
        $order = Order::find($order->id);

        return $this->success($order, $message = "Submitted successfully!", 200);
    }



    public function product_create(Request $r)
    {

        $user_id = $r->user;
        $u = User::find($user_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        //local_id is required
        if (
            !isset($r->local_id) ||
            $r->local_id == null ||
            strlen($r->local_id) < 6
        ) {
            return $this->error('Local ID is missing.');
        }


        $isEdit = false;
        if (
            isset($r->is_edit) && $r->is_edit == 'Yes' && $r->id != null
            && $r->id > 0
        ) {
            $pro = Product::find($r->id);
            if ($pro == null) {
                $pro = new Product();
                $isEdit = false;
            } else {
                $isEdit = true;
            }
        } else {
            $pro = new Product();
        }

        if (!$isEdit) {
            $pro->feature_photo = 'no_image.jpg';
            $pro->user = $u->id;
            $pro->in_stock = 1;
            $pro->rates = 1;
        }


        if ($r->price_1 == null || strlen($r->price_1) < 1) {
            return $this->error('Price is missing.');
        }
        if ($r->price_2 == null || strlen($r->price_2) < 1) {
            return $this->error('Price is missing.');
        }
        $pro->price_1 = $r->price_1;
        $pro->price_2 = $r->price_2;


        $pro->name = $r->name;
        $pro->description = $r->description;
        $pro->local_id = $r->local_id;
        $pro->summary = $r->data;
        $pro->supplier = $r->supplier;
        $pro->metric = 1;
        $pro->status = 0;
        $pro->currency = 1;
        $pro->url = $r->url;


        $pro->has_sizes = $r->has_sizes;
        $pro->p_type = $r->p_type;

        $cat = ProductCategory::find($r->category);
        if ($cat == null) {
            return $this->error('Category not found.');
        }
        $pro->category = $cat->id;

        $pro->date_added = Carbon::now();
        $pro->date_updated = Carbon::now();
        $imgs = Image::where([
            'parent_local_id' => $pro->local_id
        ])->get();
        if ($imgs->count() > 0) {
            $pro->feature_photo = $imgs[0]->src;
        }
        if ($pro->save()) {
            foreach ($imgs as $key => $img) {
                $img->product_id = $pro->id;
                $img->save();
            }
            if ($isEdit) {
                return $this->success(null, $message = "Updated successfully!", 200);
            }
            return $this->success(null, $message = "Submitted successfully!", 200);
        } else {
            return $this->error('Failed to upload product.');
        }
    }



    public function locations(Request $r)
    {
        $items = Location::all();
        return $this->success(
            $items,
            $message = "Sussesfully",
            1
        );
    }

    public function crops(Request $r)
    {
        $items = [];

        foreach (Crop::all() as $key => $crop) {


            $protocols = CropProtocol::where([
                'crop_id' => $crop->id
            ])->get();
            $crop->protocols = json_encode($protocols);

            $items[] = $crop;
        }

        return $this->success(
            $items,
            $message = "Sussesfully",
            200
        );
    }

    public function garden_activities(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        $gardens = [];
        if ($u->isRole('agent')) {
            $gardens = GardenActivity::where([])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $gardens = GardenActivity::where(['user_id' => $u->id])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        }

        return $this->success(
            $gardens,
            $message = "Sussesfully",
            200
        );
    }

    public function gardens(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        $gardens = [];
        if ($u->isRole('agent')) {
            $gardens = Garden::where([])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $gardens = Garden::where(['user_id' => $u->id])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        }

        return $this->success(
            $gardens,
            $message = "Sussesfully",
            200
        );
    }



    public function people(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        return $this->success(
            Person::where(['administrator_id' => $u->id])
                ->limit(100)
                ->orderBy('id', 'desc')
                ->get(),
            $message = "Sussesfully",
            200
        );
    }
    public function jobs(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        return $this->success(
            Job::where([])
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get(),
            $message = "Sussesfully",
        );
    }

    public function garden_create(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->planting_date == null ||
            $r->crop_id == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }


        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        $obj = new Garden();
        $obj->name = $r->name;
        $obj->user_id = $u->id;
        $obj->status = $r->status;
        $obj->production_scale = $r->production_scale;
        $obj->planting_date = Carbon::parse($r->planting_date);
        $obj->land_occupied = $r->planting_date;
        $obj->crop_id = $r->crop_id;
        $obj->details = $r->details;
        $obj->photo = $image;
        $obj->save();


        return $this->success(null, $message = "Sussesfully created!", 200);
    }

    public function images_delete(Request $r)
    {
        $pro = Image::find($r->id);
        if ($pro == null) {
            return $this->error('Image not found.');
        }
        try {
            $pro->delete();
            return $this->success(null, $message = "Sussesfully deleted!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to delete image because ' . $th->getMessage());
        }
    }
    public function products_delete(Request $r)
    {
        $pro = Product::find($r->id);
        if ($pro == null) {
            return $this->error('Product not found.');
        }
        try {
            $pro->delete();
            return $this->success(null, $message = "Sussesfully deleted!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to delete product.');
        }
    }
    public function person_create(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->sex == null ||
            $r->subcounty_id == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $image = "";
        if (!empty($_FILES)) {
            try {
                $image = Utils::upload_images_2($_FILES, true);
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        $obj = new Person();
        $obj->id = $r->id;
        $obj->created_at = $r->created_at;
        $obj->association_id = $r->association_id;
        $obj->administrator_id = $u->id;
        $obj->group_id = $r->group_id;
        $obj->name = $r->name;
        $obj->address = $r->address;
        $obj->parish = $r->parish;
        $obj->village = $r->village;
        $obj->phone_number = $r->phone_number;
        $obj->email = $r->email;
        $obj->district_id = $r->district_id;
        $obj->subcounty_id = $r->subcounty_id;
        $obj->disability_id = $r->disability_id;
        $obj->phone_number_2 = $r->phone_number_2;
        $obj->dob = $r->dob;
        $obj->sex = $r->sex;
        $obj->education_level = $r->education_level;
        $obj->employment_status = $r->employment_status;
        $obj->has_caregiver = $r->has_caregiver;
        $obj->caregiver_name = $r->caregiver_name;
        $obj->caregiver_sex = $r->caregiver_sex;
        $obj->caregiver_phone_number = $r->caregiver_phone_number;
        $obj->caregiver_age = $r->caregiver_age;
        $obj->caregiver_relationship = $r->caregiver_relationship;
        $obj->photo = $image;
        $obj->save();


        return $this->success(null, $message = "Sussesfully registered!", 200);
    }

    public function groups()
    {
        return $this->success(Group::get_groups(), 'Success');
    }


    public function associations()
    {
        return $this->success(Association::where([])->orderby('id', 'desc')->get(), 'Success');
    }

    public function institutions()
    {
        return $this->success(Institution::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function service_providers()
    {
        return $this->success(ServiceProvider::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function counselling_centres()
    {
        return $this->success(CounsellingCentre::where([])->orderby('id', 'desc')->get(), 'Success');
    }


    public function products_1(Request $request)
    {
        //latest 1000 products without pagination
        $products = Product::where([])->limit(1000)->get();
        return $this->success($products, 'Success');
    }
    public function products(Request $request)
    {
        // Start building the query on active products
        $query = Product::where([]);

        // Filter by search keyword (in the name or description)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('name')) {
            $name = $request->input('name');
            $query->where(function ($q) use ($name) {
                $q->where('name', 'LIKE', "%{$name}%")
                    ->orWhere('description', 'LIKE', "%{$name}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price_1', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price_2', '<=', $request->input('max_price'));
        }

        // Filter by availability
        if ($request->filled('availability')) {
            $query->where('in_stock', $request->input('availability'));
        }

        // Sorting logic based on 'sort' parameter
        if ($request->filled('sort')) {
            $sort = $request->input('sort');
            if ($sort === "Newest") {
                $query->orderBy('created_at', 'DESC');
            } elseif ($sort === "Oldest") {
                $query->orderBy('created_at', 'ASC');
            } elseif ($sort === "High Price") {
                $query->orderBy('price_2', 'DESC');
            } elseif ($sort === "Low Price") {
                $query->orderBy('price_1', 'ASC');
            } else {
                // Fallback ordering
                $query->orderBy('id', 'DESC');
            }
        } else {
            // Default ordering
            $query->orderBy('id', 'DESC');
        }

        // Paginate results (default 16 per page)
        $perPage = $request->input('per_page', 28);
        $products = $query->paginate($perPage);

        return $this->success($products, 'Success');
    }

    public function categories()
    {
        $cats = [];
        foreach (
            ProductCategory::where([])
                ->orderby('id', 'desc')
                ->get() as $key => $cat
        ) {
            $cat->parent_text = $cat->category_text;
            $cats[] = $cat;
        }

        return $this->success($cats, 'Success');
    }

    public function events()
    {
        return $this->success(Event::where([])->orderby('id', 'desc')->get(), 'Success');
    }

    public function news_posts()
    {
        return $this->success(NewsPost::where([])->orderby('id', 'desc')->get(), 'Success');
    }


    public function chat_messages(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);
        }
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (isset($r->chat_head_id) && $r->chat_head_id != null) {
            $messages = ChatMessage::where([
                'chat_head_id' => $r->chat_head_id
            ])->get();
            return $this->success($messages, 'Success');
        }
        $messages = ChatMessage::where([
            'sender_id' => $u->id
        ])->orWhere([
            'receiver_id' => $u->id
        ])->get();
        return $this->success($messages, 'Success');
    }


    public function chat_heads(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            $administrator_id = Utils::get_user_id($r);
            $u = Administrator::find($administrator_id);
        }
        if ($u == null) {
            return $this->error('User not found.');
        }
        $chat_heads = ChatHead::where([
            'product_owner_id' => $u->id
        ])->orWhere([
            'customer_id' => $u->id
        ])->get();
        $chat_heads->append('customer_unread_messages_count');
        $chat_heads->append('product_owner_unread_messages_count');
        return $this->success($chat_heads, 'Success');
    }

    public function chat_head_delete(Request $r)
    {
        $head = ChatHead::find($r->chat_head_id);
        if ($head == null) {
            return $this->error('Chat head not found.');
        }

        $head->delete();
        ChatMessage::where([
            'chat_head_id' => $head->id
        ])->delete();

        return $this->success(null, 'Chats deleted successfully.');
    }

    public function chat_mark_as_read(Request $r)
    {
        $receiver = Administrator::find($r->receiver_id);
        if ($receiver == null) {
            return $this->error('Receiver not found.');
        }
        $chat_head = ChatHead::find($r->chat_head_id);
        if ($chat_head == null) {
            return $this->error('Chat head not found.');
        }
        $messages = ChatMessage::where([
            'chat_head_id' => $chat_head->id,
            'receiver_id' => $receiver->id,
            'status' => 'sent'
        ])->get();
        foreach ($messages as $key => $message) {
            $message->status = 'read';
            $message->save();
        }
        return $this->success($messages, 'Success');
    }

    public function chat_send(Request $r)
    {
        $sender = auth('api')->user();

        if ($sender == null) {
            $administrator_id = Utils::get_user_id($r);
            $sender = Administrator::find($administrator_id);
        }
        if ($sender == null) {
            return $this->error('User not found.');
        }
        $receiver = User::find($r->receiver_id);
        if ($receiver == null) {
            return $this->error('Receiver not found.');
        }
        if ($r->chat_head_id == null || strlen($r->chat_head_id) < 1) {
            return $this->error('Chat head ID is missing.');
        }

        $product_owner = null;
        $customer = null;



        $chat_head = ChatHead::find($r->chat_head_id);

        if ($chat_head == null) {
            return $this->error('Chat head not found.');
        }


        $chat_message = new ChatMessage();
        $chat_message->chat_head_id = $chat_head->id;
        $chat_message->sender_id = $sender->id;
        $chat_message->receiver_id = $receiver->id;
        $chat_message->sender_name = $sender->name;
        $chat_message->sender_photo = $sender->photo;
        $chat_message->receiver_name = $receiver->name;
        $chat_message->receiver_photo = $receiver->photo;
        $chat_message->body = $r->body;
        $chat_message->type = 'text';
        $chat_message->status = 'sent';
        $chat_message->save();
        $chat_head->last_message_body = $r->body;
        $chat_head->last_message_time = Carbon::now();
        $chat_head->last_message_status = 'sent';
        $chat_head->save();
        return $this->success($chat_message, 'Success');
    }

    public function chat_start(Request $r)
    {
        $sender = null;
        if ($sender == null) {
            $administrator_id = Utils::get_user_id($r);
            $sender = Administrator::find($administrator_id);
        }
        if ($sender == null) {
            return $this->error('User not found.');
        }
        $receiver = User::find($r->receiver_id);
        if ($receiver == null) {
            return $this->error('Receiver not found.');
        }
        $pro = Product::find($r->product_id);
        if ($pro == null) {
            return $this->error('Product not found.');
        }
        $product_owner = null;
        $customer = null;

        if ($pro->user == $sender->id) {
            $product_owner = $sender;
            $customer = $receiver;
        } else {
            $product_owner = $receiver;
            $customer = $sender;
        }

        $chat_head = ChatHead::where([
            'product_id' => $pro->id,
            'product_owner_id' => $product_owner->id,
            'customer_id' => $customer->id
        ])->first();
        if ($chat_head == null) {
            $chat_head = ChatHead::where([
                'product_id' => $pro->id,
                'customer_id' => $product_owner->id,
                'product_owner_id' => $customer->id
            ])->first();
        }

        if ($chat_head == null) {
            $chat_head = new ChatHead();
            $chat_head->product_id = $pro->id;
            $chat_head->product_owner_id = $product_owner->id;
            $chat_head->customer_id = $customer->id;
            $chat_head->product_name = $pro->name;
            $chat_head->product_photo = $pro->feature_photo;
            $chat_head->product_owner_name = $product_owner->name;
            $chat_head->product_owner_photo = $product_owner->avatar;
            $chat_head->customer_name = $customer->name;
            $chat_head->customer_photo = $customer->avatar;
            $chat_head->last_message_body = '';
            $chat_head->last_message_time = Carbon::now();
            $chat_head->last_message_status = 'sent';
            $chat_head->save();
        }
        /* 
Full texts
id
created_at
updated_at
product_id
product_name
product_photo
product_owner_id
product_owner_name
product_owner_photo
product_owner_last_seen
customer_id
customer_name
customer_photo
customer_last_seen
last_message_body
last_message_time
last_message_status

Edit Edit
Copy Copy

*/
        return $this->success($chat_head, 'Success');
    }


    public function index(Request $r, $model)
    {

        $className = "App\Models\\" . $model;
        $obj = new $className;

        if (isset($_POST['_method'])) {
            unset($_POST['_method']);
        }
        if (isset($_GET['_method'])) {
            unset($_GET['_method']);
        }

        $conditions = [];
        foreach ($_GET as $k => $v) {
            if (substr($k, 0, 2) == 'q_') {
                $conditions[substr($k, 2, strlen($k))] = trim($v);
            }
        }
        $is_private = true;
        if (isset($_GET['is_not_private'])) {
            $is_not_private = ((int)($_GET['is_not_private']));
            if ($is_not_private == 1) {
                $is_private = false;
            }
        }
        if ($is_private) {

            $u = auth('api')->user();
            $administrator_id = $u->id;

            if ($u == null) {
                return $this->error('User not found.');
            }
            $conditions['administrator_id'] = $administrator_id;
        }

        $items = [];
        $msg = "";

        try {
            $items = $className::where($conditions)->get();
            $msg = "Success";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        if ($success) {
            return $this->success($items, 'Success');
        } else {
            return $this->error($msg);
        }
    }





    public function delete(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);

        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item already deleted.",
            ]);
        }


        try {
            $obj->delete();
            $msg = "Deleted successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }


    public function update(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item not found.",
            ]);
        }


        unset($_POST['_method']);
        if (isset($_POST['online_id'])) {
            unset($_POST['online_id']);
        }

        foreach ($_POST as $key => $value) {
            $obj->$key = $value;
        }


        $success = false;
        $msg = "";
        try {
            $obj->save();
            $msg = "Updated successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }

    //delivery_addresses
    public function delivery_addresses(Request $r)
    {
        return $this->success(
            DeliveryAddress::where([])
                ->limit(100)
                ->orderBy('id', 'desc')
                ->get(),
            $message = "Sussesfully",
            200
        );
    }
}
