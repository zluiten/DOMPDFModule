DOMPDFModule
============

Master: ![master](https://github.com/netiul/DOMPDFModule/workflows/Continuous%20Integration/badge.svg?branch=master)

The DOMPDF module integrates the DOMPDF library with Laminas with minimal effort on the consumer's end.

## Requirements
- [Laminas](https://getlaminas.org/)

## Installation
Installation of DOMPDFModule uses PHP Composer. For more information about
PHP Composer, please visit the official [PHP Composer site](http://getcomposer.org/).

#### Installation steps

1. `cd my/project/directory`
2. Add the module to the project by:

    composer require netiul/dompdf-module

3. open `my/project/directory/config/application.config.php` and add the following key to your `modules`:

   ```php
   'DOMPDFModule',
   ```
#### Configuration options
You can override options via the `dompdf_module` key in your local or global config files. See DOMPDFModule/config/module.config.php for config options.

## Usage

```php
<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use DOMPDFModule\View\Model\PdfModel;

class ReportController extends AbstractActionController
{
    public function monthlyReportPdfAction()
    {
        $pdf = new PdfModel();
        $pdf->setOption('fileName', 'monthly-report');            // "pdf" extension is automatically appended
        $pdf->setOption('display', PdfModel::DISPLAY_ATTACHMENT); // Triggers browser to prompt "save as" dialog
        $pdf->setOption('paperSize', 'a4');                       // Defaults to "8x11"
        $pdf->setOption('paperOrientation', 'landscape');         // Defaults to "portrait"
        
        // To set view variables
        $pdf->setVariables(array(
          'message' => 'Hello'
        ));
        
        return $pdf;
    }
}
```
## Development
So you want to contribute? Fantastic! Don't worry, it's easy. Local builds, tests, and code quality checks can be executed using [Docker](https://www.docker.com/).

### Quick Start
1. Install [Docker CE](https://www.docker.com/community-edition).
2. Run the following from your terminal:

```
docker build -t dino/dompdf-module .
docker run -v composer-cache:/var/lib/composer -v ${PWD}:/opt/app dino/dompdf-module
```

Super easy, right? Here's a quick walk through as to what's going on.

* `docker build -t dino/dompdf-module .` builds a docker image that will be used for each run (i.e. each time `docker run` is executed) and tags it with the name `dino/dompdf-module`.
* `docker run -v composer-cache:/var/lib/composer -v ${PWD}:/opt/app dino/dompdf-module` runs the default build in a new Docker container derived from the image tagged `dino/dompdf-module`. The root of the project and PHP Composer cache volume are mounted so that artifacts generated during the build process are available to you on your local machine.

**Note:** You only need to run the first command once in order to build the image. The second command is what executes the build (build, tests, code quality checks, etc.).
