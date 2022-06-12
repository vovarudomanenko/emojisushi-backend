<?php
namespace Layerok\PosterPos\Controllers;

use BackendMenu;
use Layerok\PosterPos\Classes\Exports\PosterCategoryExport;
use Layerok\PosterPos\Classes\Exports\PosterIngredientExport;
use Layerok\PosterPos\Classes\Exports\PosterProductsExport;
use Maatwebsite\Excel\Facades\Excel;


class Export extends \Backend\Classes\Controller
{
    public function __construct() {
        parent::__construct();

        BackendMenu::setContext('Layerok.PosterPos', 'export');
    }

    public function index()
    {

    }

    public function items() {
        $map = [
            'product' => PosterProductsExport::class,
            'category' => PosterCategoryExport::class,
            'ingredient' => PosterIngredientExport::class,
        ];
        $type = input('type');
        $exist = in_array($type, array_keys($map));
        if($type && $exist) {
            $class = $map[$type];
            return Excel::download(new $class(), 'poster_' . $type . date('Y-m-d_h-m-s') .'.xlsx');
        }

    }
}
