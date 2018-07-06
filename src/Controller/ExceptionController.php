<?php
namespace App\Controller;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Twig\Environment;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Override de la gestion des pages d'erreur Symfony
 */
class ExceptionController extends BaseExceptionController
{
    /**
     * @param Environment $twig
     * @param bool        $debug Show error (false) or exception (true) pages by default
     */
    public function __construct(Environment $twig, bool $debug = null)
    {
        if($debug === null) {
            $debug = $twig->getGlobals()['app']->getDebug();
        }
        $this->twig = $twig;
        $this->debug = $debug;
    }

    /**
     * Converts an Exception to a Response.
     *
     * @param Request              $request   The request
     * @param FlattenException     $exception A FlattenException instance
     * @param DebugLoggerInterface $logger    A DebugLoggerInterface instance
     * @param string               $_format   The format to use for rendering (html, xml, ...)
     *
     * @return Response
     *
     * @throws \InvalidArgumentException When the exception template does not exist
     */
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null, $_format = 'html')
    {
        $currentContent = $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));
        $code           = $exception->getStatusCode();

        return new Response($this->twig->render(
                $this->findTemplate($request, $_format, $code, $this->debug),
                array(
                        'status_code'    => $code,
                        'status_text'    => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                        'exception'      => $exception,
                        'logger'         => $logger,
                        'currentContent' => $currentContent,
                )
        ));
    }

    /**
     * @param Request $request
     * @param string  $format
     * @param integer $code       An HTTP response status code
     * @param Boolean $debug
     *
     * @return TemplateReference
     */
    protected function findTemplate(Request $request, $format, $code, $debug)
    {
        // Template personnalisé ?
        $template = sprintf('@Twig/Exception/error%s.%s.twig', $code, $format);

        if ($this->templateExists($template)) {
            return $template;
        }

        // Template par défaut
        return parent::findTemplate($request, $format, $code, $debug);
    }
}