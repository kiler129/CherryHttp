<?php
/**
 * This file is part of CherryHttp project.
 * You are using it at your own risk and you are fully responsible for everything that code will do.
 *
 * Copyright (c) 2016 Grzegorz Zdanowski <grzegorz@noflash.pl>
 *
 * For the full copyright and license information, please view the LICENSE file distributed with this source code.
 */

namespace noFlash\CherryHttp\Http\Response;

/**
 * Represents generic error response
 */
class ErrorResponse extends Response
{
    use AutogeneratedResponseTrait;

    /**
     * {@inheritdoc}
     */
    protected $statusCode = ResponseCode::INTERNAL_SERVER_ERROR;

    /**
     * {@inheritdoc}
     */
    protected $reasonPhrase = 'Internal Server Error';

    /**
     * @var string Human-readable error explanation
     */
    private $explanation = 'No further details are available.';

    /**
     * ErrorResponse constructor.
     */
    public function __construct()
    {
        $this->setHeader('Content-Type', 'text/html');
    }

    /**
     * Returns short, human-readable error explanation.
     *
     * @return string
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Defines short error explanation.
     * It will be used in autogenerated response if no body is set on response.
     *
     * @param string $explanation
     */
    public function setExplanation($explanation)
    {
        $this->explanation = (string)$explanation;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException RedirectResponse accepts (fo obvious reasons) 3xx codes only.
     */
    public function setStatus($code, $reasonPhrase = '')
    {
        $codeGroup = ResponseCode::getGroupFromCode($code);

        if ($codeGroup !== ResponseCode::GROUP_CLIENT_ERROR && $codeGroup !== ResponseCode::GROUP_SERVER_ERROR) {
            throw new \LogicException(__CLASS__ . ' accepts error codes only (4xx & 5xx).');
        }

        parent::setStatus($code, $reasonPhrase);
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        if (empty($this->body)) {
            return $this->generateAutomaticResponseContent(
                $this->statusCode,
                $this->reasonPhrase,
                null,
                $this->explanation
            );
        }

        return parent::getBody();
    }
}
