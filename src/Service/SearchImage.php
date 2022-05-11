<?php
namespace App\Service;

use PhpQuery\PhpQuery;

class SearchImage
{
    public function getSearchImage($word): string
    {

    $key=$word;

    $page=file_get_contents("https://unsplash.com/s/photos/.$key");
    $pq=new PhpQuery;
    $pq->load_str($page);
    return $page;
        }
}