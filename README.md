# Laravel PDF: mPDF wrapper for Laravel 8

> Easily generate PDF documents from HTML right inside of Laravel using this mPDF wrapper.


## Installation

Require this package in your `composer.json` or install it by running:

```
composer require niklasravnsborg/laravel-pdf
```

Now, you should publish package's config file to your config directory by using following command:

```
php artisan vendor:publish
```

## Basic Usage

To use Laravel PDF add something like this to one of your controllers. You can pass data to a view in `/resources/views`.

```php
use PDF;

function generate_pdf() {
	$data = [
		'foo' => 'bar'
	];
	$pdf = PDF::loadView('pdf.document', $data);
	return $pdf->stream('document.pdf');
}
```

## Other methods

It is also possible to use the following methods on the `pdf` object:

`output()`: Outputs the PDF as a string.  
`save($filename)`: Save the PDF to a file  
`download($filename)`: Make the PDF downloadable by the user.  
`stream($filename)`: Return a response with the PDF to show in the browser.

## Config

If you have published config file, you can change the default settings in `config/pdf.php` file:

```php
return [
	'format'           => 'A4', // See https://mpdf.github.io/paging/page-size-orientation.html
	'author'           => 'John Doe',
	'subject'          => 'This Document will explain the whole universe.',
	'keywords'         => 'PDF, Laravel, Package, Peace', // Separate values with comma
	'creator'          => 'Laravel Pdf',
	'display_mode'     => 'fullpage'
];
```

To override this configuration on a per-file basis use the fourth parameter of the initializing call like this:

```php
PDF::loadView('pdf', $data, [], [
  'format' => 'A5-L'
])->save($pdfFilePath);
```

## Headers and Footers

If you want to have headers and footers that appear on every page, add them to your `<body>` tag like this:

```html
<htmlpageheader name="page-header">
	Your Header Content
</htmlpageheader>

<htmlpagefooter name="page-footer">
	Your Footer Content
</htmlpagefooter>
```

Now you just need to define them with the name attribute in your CSS:

```css
@page {
	header: page-header;
	footer: page-footer;
}
```

Inside of headers and footers `{PAGENO}` can be used to display the page number.

Find more information to `SetProtection()` here: https://mpdf.github.io/reference/mpdf-functions/setprotection.html

## Testing

To use the testing suite, you need some extensions and binaries for your local PHP. On macOS, you can install them like this:

```
brew install imagemagick ghostscript
pecl install imagick
```

## License

Laravel PDF is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
