<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Orders';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());
        $grid->model()->orderBy('id', 'desc');
        $grid->quickSearch('customer_name')->placeholder('Search by customer name');

        $grid->column('id', __('ID'))->sortable();

        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return Utils::my_date_time($created_at);
            })->sortable();

        $grid->column('user', __('User'))
            ->display(function ($user) {
                $u = User::find($user);
                return $u ? $u->name : "Unknown";
            })->sortable()->hide();

        $grid->column('order_state', __('Order State'))
            ->sortable()
            ->display(function ($x) {
                $badge_color = "primary";
                if ($x == 1) {
                    $badge_color = "warning";
                } else if ($x == 2) {
                    $badge_color = "info";
                } else if ($x == 3) {
                    $badge_color = "success";
                } else if ($x == 4 || $x == 5) {
                    $badge_color = "danger";
                }
                $text = 'Pending';
                if ($x == 0) {
                    $text = 'Pending';
                } else if ($x == 1) {
                    $text = 'Processing';
                } else if ($x == 2) {
                    $text = 'Completed';
                } else if ($x == 3) {
                    $text = 'Canceled';
                } else {
                    $text = 'Failed';
                }
                return "<span class='badge bg-$badge_color'>$text</span>";
            });

        $grid->column('amount', __('Amount'))
            ->display(function ($amount) {
                return 'UGX ' . number_format($amount);
            })->sortable();

        $grid->column('payment_confirmation', __('Payment'))
            ->display(function ($payment_confirmation) {
                return empty($payment_confirmation) ? "Not Paid" : $payment_confirmation;
            })->sortable();

        $grid->column('mail', __('Mail'))->sortable()->hide();

        $grid->column('delivery_district', __('Delivery'))
            ->display(function ($delivery_district) {
                $delivery_district = DeliveryAddress::find($delivery_district);
                return $delivery_district ? $delivery_district->address : "Unknown";
            })->sortable();

        $grid->column('description', __('Description'))->hide();
        $grid->column('customer_name', __('Customer'))->sortable();
        $grid->column('customer_phone_number_1', __('Customer Contact'));
        $grid->column('customer_phone_number_2', __('Alternate Contact'))->hide();
        $grid->column('customer_address', __('Customer Address'));

        $grid->column('order_total', __('Total'))
            ->display(function ($order_total) {
                return 'UGX ' . number_format($order_total);
            })->sortable()->hide();

        // Action buttons for viewing and updating orders.
        $grid->column('view', __('View'))
            ->display(function () {
                $order = Order::find($this->id);
                $link = admin_url('orders/' . $order->id);
                return "<a href='$link' class='btn btn-info btn-sm'>View</a>";
            });

        $grid->column('update', __('Update'))
            ->display(function () {
                $order = Order::find($this->id);
                $link = admin_url('orders/' . $order->id . '/edit');
                return "<a href='$link' class='btn btn-warning btn-sm'>Update</a>";
            });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $order = Order::findOrFail($id);
        // Display custom view for order details.
        return view('order', ['order' => $order]);

        // If using the built-in Show, uncomment below:
        /*
        $show = new Show($order);
        $show->field('id', __('ID'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));
        $show->field('user', __('User'));
        $show->field('order_state', __('Order State'));
        $show->field('amount', __('Amount'))->as(function ($amount) {
            return 'UGX ' . number_format($amount);
        });
        $show->field('payment_confirmation', __('Payment Confirmation'));
        $show->field('mail', __('Mail'));
        $show->field('delivery_district', __('Delivery District'));
        $show->field('description', __('Description'));
        $show->field('customer_name', __('Customer Name'));
        $show->field('customer_phone_number_1', __('Customer Phone Number 1'));
        $show->field('customer_phone_number_2', __('Customer Phone Number 2'));
        $show->field('customer_address', __('Customer Address'));
        $show->field('order_total', __('Order Total'))->as(function ($order_total) {
            return 'UGX ' . number_format($order_total);
        });
        $show->field('order_details', __('Order Details'));
        $show->field('stripe_id', __('Stripe ID'));
        $show->field('stripe_url', __('Stripe URL'));
        $show->field('stripe_paid', __('Stripe Paid'));
        $show->field('delivery_method', __('Delivery Method'));
        $show->field('delivery_address_id', __('Delivery Address ID'));
        $show->field('delivery_address_details', __('Delivery Address Details'));
        $show->field('delivery_amount', __('Delivery Amount'))->as(function ($delivery_amount) {
            return 'UGX ' . number_format($delivery_amount);
        });
        $show->field('payable_amount', __('Payable Amount'))->as(function ($payable_amount) {
            return 'UGX ' . number_format($payable_amount);
        });
        return $show;
        */
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order());

        $form->display('id', __('ID'));

        $form->radio('order_state', __('Order State'))
            ->options([
                0 => 'Pending',
                1 => 'Processing',
                2 => 'Completed',
                3 => 'Canceled',
                4 => 'Failed',
            ]);

        $form->disableEditingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        return $form;
    }
}
