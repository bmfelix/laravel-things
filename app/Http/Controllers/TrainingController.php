<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Helpers\Helper;


class TrainingController extends Controller
{

    private $helper;

    public function __construct()
    {
        $this->helper = new Helper();
    }

    public function index()
    {
        // Test database connection
        try {
            $pdo = DB::connection('ibm')->getPdo();

            $query = $pdo->prepare("UPDATE SFTYTRAIN.TRN_RECS SET PLANT = 5 WHERE ID = 4");
            $query->execute();

            $query = $pdo->prepare("SELECT * FROM SFTYTRAIN.TRN_RECS");
            $query->execute();
            while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {

                echo '<pre>';
                print_r($row);
                echo '</pre>';
            }
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            echo '</pre>';
            die;
        }
    }

    public function testHelpers()
    {
        $data = $this->helper->inNexusGroup('IT');

        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die;
    }
}
