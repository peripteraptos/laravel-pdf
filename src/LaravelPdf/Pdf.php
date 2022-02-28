<?php

namespace niklasravnsborg\LaravelPdf;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Mpdf;

/**
 * Laravel PDF: mPDF wrapper for Laravel 5
 *
 * @package laravel-pdf
 * @author Niklas Ravnsborg-Gjertsen
 */
class Pdf
{
    protected Mpdf\Mpdf $mpdf;
    protected Repository $config;
    protected Filesystem $files;
    protected Factory $view;
    protected bool $rendered = false;
    protected bool $showWarnings;
    protected string $public_path;

    public function __construct(Mpdf\Mpdf $mpdf, Repository $config, Filesystem $files, \Illuminate\View\Factory $view)
    {
        $this->config = $config;
        $this->mpdf = $mpdf;
        $this->config = $config;
        $this->files = $files;
        $this->view = $view;
    }

    public function getMpdf(): Mpdf\Mpdf
    {
        return $this->mpdf;
    }

    public function setPaper($paper, string $orientation = 'portrait'): self
    {
        $this->mpdf->setPaper($paper, $orientation);
        return $this;
    }

    /**
     * Encrypts and sets the PDF document permissions
     *
     * @param array $permisson Permissons e.g.: ['copy', 'print']
     * @param string $userPassword User password
     * @param string $ownerPassword Owner password
     *
     */
    public function setProtection($permisson, $userPassword = '', $ownerPassword = '')
    {
        if (func_get_args()[2] === NULL) {
            $ownerPassword = bin2hex(openssl_random_pseudo_bytes(8));
        };

        $this->mpdf->SetProtection($permisson, $userPassword, $ownerPassword);

        return $this;
    }

    /**
     * Save the PDF to a file
     *
     * @param $filename
     * @return Pdf
     */
    public function save($filename)
    {
        $this->files->put($filename, $this->mpdf->Output($filename, 'S'));
        return $this;
    }

    /**
     * Make the PDF downloadable by the user
     *
     * @param string $filename
     * @return Response
     */
    public function download($filename = 'document.pdf')
    {
        $output = $this->output();
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($output),
        ]);

    }

    /**
     * Output the PDF as a string.
     *
     * @return string The rendered PDF as string
     */

    public function output(array $options = []): string
    {
        return (string)$this->mpdf->Output('', 'S');;
    }


    /**
     * Load a View and convert to HTML
     * @param array<string, mixed> $data
     * @param array<string, mixed> $mergeData
     * @param string|null $encoding Not used yet
     */
    public function loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null): self
    {
        $html = $this->view->make($view, $data, $mergeData);
        return $this->loadHTML($html, $encoding);
    }

    /**
     * Load a HTML string
     *
     * @param string|null $encoding Not used yet
     */
    public function loadHTML(string $string, ?string $encoding = null): self
    {
        $this->mpdf->WriteHTML($string);
        return $this;
    }

    /**
     * Load a HTML file
     */
    public function loadFile(string $file): self
    {
        $this->mpdf->WriteHTML(File::get($file));
        return $this;
    }

    /**
     * Return a response with the PDF to show in the browser
     *
     * @param string $filename
     * @return
     */
    public function stream($filename = 'document.pdf')
    {
        $output = $this->output();
        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    protected function getConfig($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        } else {
            return Config::get('pdf.' . $key);
        }
    }
}
