<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GenericImport implements ToCollection, WithHeadingRow
{
    public $data;

    public function collection(Collection $collection)
    {
        $this->data = $collection;
    }
}