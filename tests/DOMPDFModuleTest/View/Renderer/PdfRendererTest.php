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
 * @copyright Copyright (c) 2017 Raymond J. Kolbe
 * @license	http://www.opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);

namespace DOMPDFModuleTest\View\Renderer;

use Dompdf\Dompdf;
use Laminas\View\Model\JsonModel;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;
use DOMPDFModuleTest\Framework\TestCase;
use DOMPDFModule\View\Model\PdfModel;
use DOMPDFModule\View\Renderer\PdfRenderer;
use PHPUnit\Framework\MockObject\MockObject;

class PdfRendererTest extends TestCase
{
    /**
     * @var RendererInterface|MockObject
     */
    private RendererInterface $htmlRenderer;

    /**
     * @var Dompdf|MockObject
     */
    private Dompdf $engine;

    /**
     * System under test.
     *
     * @var PdfRenderer
     */
    private PdfRenderer $renderer;

    public function testItHasAnHtmlRenderer(): void
    {
        $this->assertInstanceOf(\Laminas\View\Renderer\RendererInterface::class, $this->renderer->getHtmlRenderer());
    }

    public function testItHasAnEngine(): void
    {
        $this->assertInstanceOf(\Dompdf\Dompdf::class, $this->renderer->getEngine());
    }

    public function testItRendersAPdfModel(): void
    {
        $this->htmlRenderer->expects($this->once())->method('render');

        $this->engine->expects($this->once())->method('setPaper');
        $this->engine->expects($this->once())->method('setBasePath');
        $this->engine->expects($this->once())->method('loadHtml');
        $this->engine->expects($this->once())->method('render');
        $this->engine->expects($this->once())->method('output');

        $this->renderer->render(new PdfModel());
    }

    public function testItDoesNotRenderOtherModels(): void
    {
        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);

        $this->htmlRenderer->expects($this->never())->method('render');

        $this->engine->expects($this->never())->method('render');
        $this->engine->expects($this->never())->method('output');

        $this->renderer->render(new JsonModel());
    }

    public function testItDoesNotRenderNamedModels(): void
    {
        $this->expectException(\Laminas\View\Exception\InvalidArgumentException::class);

        $this->htmlRenderer->expects($this->never())->method('render');

        $this->engine->expects($this->never())->method('render');
        $this->engine->expects($this->never())->method('output');

        $this->renderer->render('named-model');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->htmlRenderer = $this->createMock(RendererInterface::class);
        $this->resolver = $this->createMock(ResolverInterface::class);
        $this->engine = $this->getMockBuilder(Dompdf::class)->disableOriginalConstructor()->getMock();

        $this->renderer = new PdfRenderer();
        $this->renderer->setHtmlRenderer($this->htmlRenderer);
        $this->renderer->setEngine($this->engine);
    }
}
