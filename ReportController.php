<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPJasper\PHPJasper;
use MPDF;


class ReportController extends Controller
{

    public function getDatabaseConfig()
    {
        return [
            'driver' => session('db_client')['driver'],
            'host' => session('db_client')['host'],
            'port' => session('db_client')['port'],
            'username' => session('db_client')['username'],
            'password' => session('db_client')['password'],
            'database' => session('db_client')['database'],
            'jdbc_dir' => base_path() . env('JDBC_DIR', '/vendor/lavela/phpjasper/bin/jasperstarter/jdbc')
        ];
    }

    public function mpdf()
    {
        $data = [
            'message' => ''
        ];

        $params = [
            'title'         => 'Pessoas',
            'orientation'   => 'L',
            'format'        => [200, 80],
            'margin_left'   => 5,
            'margin_right'  => 5,
            'margin_top'    => 5,
            'margin_bottom' => 5
        ];

        $pdf = MPDF::loadView('relatorios.cadastros.pessoas.listagem', $data, [], $params);

        return $pdf->stream('relatorio.pdf');
    }

    public function index()
    {

        $input = public_path() . '/reports/cadastros/pessoas/pessoas.jrxml';
        $output = public_path() . '/reports/cadastros/pessoas/' . time() . '_pessoas';

        $options = [
            'format' => ['pdf'],
            'locate' => 'en',
            'params' => [],
            'db_connection' => $this->getDatabaseConfig()
        ];

        $report = new PHPJasper;

        $report->process(
            $input,
            $output,
            $options
        )->execute();

        $file = $output . '.pdf';
        $path = $file;

        if (!file_exists($file)) {
            abort(404);
        }

        $file = file_get_contents($file);
        unlink($path);

        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition','inline; filename="cliente.pdf"');

    }

}
