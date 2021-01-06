<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Raymond J. Kolbe <rkolbe@gmail.com>
 * @copyright Copyright (c) 2012 University of Maine, 2016 Raymond J. Kolbe
 * @license	http://www.opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace DOMPDFModule\View\Renderer;

use DOMPDFModule\View\Model\PdfModel;
use Laminas\View\Exception\InvalidArgumentException;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Laminas\View\Resolver\ResolverInterface as Resolver;
use Dompdf\Dompdf;

class PdfRenderer implements Renderer
{
    /**
     * @var Dompdf|null
     */
    private ?Dompdf $dompdf = null;

    /**
     * @var Renderer|null
     */
    private ?Renderer $htmlRenderer = null;

    /**
     * @param Renderer $renderer
     * @return $this
     */
    public function setHtmlRenderer(Renderer $renderer): PdfRenderer
    {
        $this->htmlRenderer = $renderer;
        return $this;
    }

    /**
     * @return Renderer
     */
    public function getHtmlRenderer(): ?Renderer
    {
        return $this->htmlRenderer;
    }

    /**
     * @param Dompdf $dompdf
     * @return $this
     */
    public function setEngine(Dompdf $dompdf): PdfRenderer
    {
        $this->dompdf = $dompdf;
        return $this;
    }

    /**
     * @return Dompdf
     */
    public function getEngine(): ?Dompdf
    {
        return $this->dompdf;
    }

    /**
     * @param Resolver $resolver
     * @return $this
     */
    public function setResolver(Resolver $resolver): PdfRenderer
    {
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function render($nameOrModel, $values = null): ?string
    {
        if (!($nameOrModel instanceof PdfModel)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a PdfModel as the first argument; received "%s"',
                __METHOD__,
                (is_object($nameOrModel) ? get_class($nameOrModel) : gettype($nameOrModel))
            ));
        }

        $html = $this->getHtmlRenderer()->render($nameOrModel, $values);
        
        $paperSize = $nameOrModel->getOption('paperSize');
        $paperOrientation = $nameOrModel->getOption('paperOrientation');
        $basePath = $nameOrModel->getOption('basePath');
        
        $pdf = $this->getEngine();
        $pdf->setPaper($paperSize, $paperOrientation);
        $pdf->setBasePath($basePath);
        
        $pdf->loadHtml($html);
        $pdf->render();
        
        return $pdf->output();
    }
}
