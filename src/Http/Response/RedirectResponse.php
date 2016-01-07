<?php
/*
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible
 *  for everything that code will do.
 *
 * (c) Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace noFlash\CherryHttp\Http\Response;

/**
 * Represents response
 */
class RedirectResponse extends Response
{
    use AutogeneratedResponseTrait;

    /**
     * {@inheritdoc}
     */
    protected $statusCode = ResponseCode::TEMPORARY_REDIRECT;

    /**
     * {@inheritdoc}
     */
    protected $reasonPhrase = 'Temporary Redirect';

    /**
     * RedirectResponse constructor.
     */
    public function __construct()
    {
        $this->setHeader('Location', 'about:blank');
        $this->setHeader('Content-Type', 'text/html');
    }

    /**
     * Sets redirect location
     *
     * @param string $url Redirect location. Location is not validated because it can be almost anything.
     */
    public function setLocation($url)
    {
        $this->setHeader('Location', $url);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException RedirectResponse accepts (fo obvious reasons) 3xx codes only.
     */
    public function setStatus($code, $reasonPhrase = '')
    {
        if (ResponseCode::getGroupFromCode($code) !== ResponseCode::GROUP_REDIRECTION ||
            $code === ResponseCode::NOT_MODIFIED //TODO Read RFC and check if there're other codes to exclude
        ) {
            throw new \LogicException(__CLASS__ . ' accepts only redirect codes only.');
        }

        parent::setStatus($code, $reasonPhrase);
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        if (empty($this->body) && $this->hasHeader('location')) { //User can unset that header :D
            $redirectUrl = $this->getHeader('location')[0];
            $redirectHtml = "The document has been permanently <a href=\"$redirectUrl\">moved</a>.";

            return $this->generateAutomaticResponseContent(
                $this->statusCode,
                $this->reasonPhrase,
                null,
                $redirectHtml
            );
        }

        return parent::getBody();
    }
}