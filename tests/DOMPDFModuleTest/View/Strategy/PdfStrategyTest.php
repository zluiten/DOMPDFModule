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

namespace DOMPDFModuleTest\View\Strategy;

use Laminas\Stdlib\ResponseInterface;
use Laminas\View\Model\ViewModel;
use Laminas\View\Resolver\TemplatePathStack;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\ViewEvent;
use Laminas\Http\Response as HttpResponse;
use DOMPDFModuleTest\Framework\TestCase;
use DOMPDFModule\View\Model\PdfModel;
use DOMPDFModule\View\Renderer\PdfRenderer;
use DOMPDFModule\View\Strategy\PdfStrategy;

class PdfStrategyTest extends TestCase
{
    /**
     * @var ViewEvent
     */
    private ViewEvent $event;

    /**
     * @var PdfRenderer
     */
    private PdfRenderer $renderer;

    /**
     * @var TemplatePathStack
     */
    private TemplatePathStack $resolver;

    /**
     * @var HttpResponse
     */
    private HttpResponse $response;

    /**
     * System under test.
     *
     * @var PdfStrategy
     */
    private PdfStrategy $strategy;

    public function testSelectsRendererWhenProvidedPdfModel(): void
    {
        $this->event->setModel(new PdfModel());
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertSame($this->renderer, $result);
    }

    public function testDoesNotSelectRendererWhenNotProvidedPdfModel(): void
    {
        $this->event->setModel(new ViewModel());
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertNull($result);
    }

    public function testDoesNotRenderPdfWhenRenderMismatch(): void
    {
        $this->event->setRenderer(new PhpRenderer());
        $this->event->setResult('dummy result');

        $this->strategy->injectResponse($this->event);

        $this->assertNull($this->event->getResponse());
    }

    public function testDoesNotRenderPdfWhenResultIsNotString(): void
    {
        $this->event->setRenderer($this->renderer);
        $this->event->setResult(new \stdClass());

        $this->strategy->injectResponse($this->event);

        $this->assertNull($this->event->getResponse());
    }

    public function testItAddsApplicationPdfContentType()
    {
        $model = new PdfModel();
        $model->setTemplate('basic.phtml');

        $this->execute($this->strategy, $this->event, $model);

        $response = $this->event->getResponse();
        $this->assertHeaderEqualTo(
            $response,
            'Content-Type',
            'application/pdf',
            'content type'
        );
    }

    public function testItAddsAttachmentDispositionType(): void
    {
        $model = new PdfModel();
        $model->setTemplate('basic.phtml');
        $model->setOption('fileName', 'testPdfFileName');
        $model->setOption('display', PdfModel::DISPLAY_ATTACHMENT);

        $this->execute($this->strategy, $this->event, $model);

        $response = $this->event->getResponse();
        $this->assertHeaderEqualTo(
            $response,
            'Content-Disposition',
            'attachment; filename="testPdfFileName.pdf"',
            'content disposition'
        );
    }

    public function testItAddsInlineDispositionType(): void
    {
        $model = new PdfModel();
        $model->setTemplate('basic.phtml');
        $model->setOption('fileName', 'testPdfFileName');
        $model->setOption('display', PdfModel::DISPLAY_INLINE);

        $this->execute($this->strategy, $this->event, $model);

        $response = $this->event->getResponse();
        $this->assertHeaderEqualTo(
            $response,
            'Content-Disposition',
            'inline; filename="testPdfFileName.pdf"',
            'content disposition'
        );
    }

    public function testItAddsContentLength(): void
    {
        $model = new PdfModel();
        $model->setTemplate('basic.phtml');

        $this->execute($this->strategy, $this->event, $model);

        $response = $this->event->getResponse();
        $expectedContentLength = \strlen($response->getBody());
        $this->assertHeaderEqualTo(
            $response,
            'Content-Length',
            $expectedContentLength,
            'content length'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new PdfRenderer();
        $this->strategy = new PdfStrategy($this->renderer);
        $this->event    = new ViewEvent();
        $this->response = new HttpResponse();

        $this->resolver = new TemplatePathStack();
        $this->resolver->addPath(\dirname(__DIR__) . '/_templates');

        $htmlRenderer = new PhpRenderer();
        $htmlRenderer->setResolver($this->resolver);
        $this->renderer->setHtmlRenderer($htmlRenderer);
        $this->renderer->setEngine($this->getServiceManager()->get('DOMPDF'));
    }

    private function execute(PdfStrategy $strategy, ViewEvent $event, PdfModel $model): void
    {
        $event->setModel($model);
        $event->setResponse($this->response);
        $event->setRenderer($this->renderer);
        $event->setResult($this->renderer->render($model));

        $strategy->injectResponse($this->event);
    }

    private function assertHeaderEqualTo(ResponseInterface $response, $name, $expected, $message = ''): void
    {
        $headers = $response->getHeaders();
        $header = $headers->get($name);

        $this->assertEquals($header->getFieldValue(), $expected, $message);
    }
}
