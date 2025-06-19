<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\Candidate;
use App\Models\Garden;
use App\Models\Group;
use App\Models\Image;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Order;
use App\Models\Person;
use App\Models\Product;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Auth;
use SplFileObject;

class HomeController extends Controller
{
    public function index(Content $content)
    {



        $u = Auth::user();
        $content->header('Dashboard');
        $content->description('Description...');

        return $content
            ->title('Dashboard')
            ->description('Welcome to the dashboard')
            ->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $admins = User::where('user_type', 'admin')->count();
                    $vendors = User::where('user_type', 'vendor')->count();
                    $customers = User::where([])->count() - $admins - $vendors;
                    $box = new Box(
                        'System Users',
                        $admins . " Admins<br>"
                    );
                    $box->style('primary');
                    $box->solid();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {
                    $admins = User::where('user_type', 'admin')->count();
                    $vendors = User::where('user_type', 'vendor')->count();
                    $customers = User::where([])->count() - $admins - $vendors;
                    $box = new Box(
                        'System Users',
                        $vendors . " Vendors<br>"
                    );
                    $box->style('primary');
                    $box->solid();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {
                    $admins = User::where('user_type', 'admin')->count();
                    $vendors = User::where('user_type', 'vendor')->count();
                    $customers = User::where([])->count() - $admins - $vendors;
                    $box = new Box(
                        'System Users',
                        $customers . " Customers<br>"
                    );
                    $box->style('primary');
                    $box->solid();
                    $column->append($box);
                });

                $row->column(3, function (Column $column) {
                    //orders
                    $orders = Order::where([])->count();
                    $box = new Box(
                        'Orders',
                        $orders . " Orders<br>"
                    );
                    $box->style('info');
                    $box->solid();
                    $column->append($box);
                });
            });
    }
}
