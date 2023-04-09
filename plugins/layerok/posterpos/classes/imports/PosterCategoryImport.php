<?php

namespace Layerok\PosterPos\Classes\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use poster\src\PosterApi;

class PosterCategoryImport implements ToModel
{
    public $check = false;
    // 0 - poster_id
    // 1 - parent_category
    // 2 - name
    // 3 - translate name
    public $updatedCount = 0;
    public $errors = [];
    public function model(array $row)
    {
        $id =  $row[0];
        $parent_category = $row[1];
        $newName =  $row[3];

        if($id === 'Category ID') {
            // Пропускаем ряд с названиями колонок
            $this->check = true;
            return;
        }

        if($this->check && $newName) {
            PosterApi::init([
                'access_token' => env('POSTER_ACCESS_TOKEN')
            ]);
            $result = PosterApi::menu()->updateCategory([
                'category_id' => $id,
                'category_name' => $newName,
                'parent_category' => $parent_category
            ]);
            if(isset($result->error)) {
                $this->errors[$id][] = $result->message;
            } else {
                $this->updatedCount++;
            }
        }

    }
}
