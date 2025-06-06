<?php

/*
 * This file is part of BeSimpleSoapCommon.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapCommon\Mime;

use BeSimple\SoapCommon\Helper;

/**
 * Mime part. Everything must be UTF-8. Default charset for text is UTF-8.
 *
 * Headers:
 * - Content-Type
 * - Content-Transfer-Encoding
 * - Content-ID
 * - Content-Location
 * - Content-Description
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class Part extends PartHeader implements \Stringable
{
    /**
     * Encoding type base 64
     */
    final public const ENCODING_BASE64 = 'base64';

    /**
     * Encoding type binary
     */
    final public const ENCODING_BINARY = 'binary';

    /**
     * Encoding type eight bit
     */
    final public const ENCODING_EIGHT_BIT = '8bit';

    /**
     * Encoding type seven bit
     */
    final public const ENCODING_SEVEN_BIT = '7bit';

    /**
     * Encoding type quoted printable
     */
    final public const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    /**
     * Construct new mime object.
     *
     * @param mixed  $content     Content
     * @param string $contentType Content type
     * @param string $charset     Charset
     * @param string $encoding    Encoding
     * @param string $contentId   Content id
     *
     * @return void
     */
    public function __construct(protected mixed $content = null, $contentType = 'application/octet-stream', $charset = null, $encoding = self::ENCODING_BINARY, $contentId = null)
    {
        $this->setHeader('Content-Type', $contentType);
        if (null !== $charset) {
            $this->setHeader('Content-Type', 'charset', $charset);
        } else {
            $this->setHeader('Content-Type', 'charset', 'utf-8');
        }
        $this->setHeader('Content-Transfer-Encoding', $encoding);
        if (null === $contentId) {
            $contentId = $this->generateContentId();
        }
        $this->setHeader('Content-ID', '<' . $contentId . '>');
    }

    /**
     * __toString.
     */
    public function __toString(): string
    {
        return (string) $this->content;
    }

    /**
     * Get mime content.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set mime content.
     *
     * @param mixed $content Content to set
     */
    public function setContent(mixed $content): void
    {
        $this->content = $content;
    }

    /**
     * Get complete mime message of this object.
     *
     * @return string
     */
    public function getMessagePart()
    {
        return $this->generateHeaders() . "\r\n" . $this->generateBody();
    }

    /**
     * Generate body.
     *
     * @return string
     */
    protected function generateBody()
    {
        $encoding = strtolower((string) $this->getHeader('Content-Transfer-Encoding'));
        $charset = strtolower((string) $this->getHeader('Content-Type', 'charset'));
        if ($charset != 'utf-8') {
            $content = iconv('utf-8', $charset . '//TRANSLIT', (string) $this->content);
        } else {
            $content = $this->content;
        }
        return match ($encoding) {
            self::ENCODING_BASE64 => substr(chunk_split(base64_encode((string) $content), 76, "\r\n"), -2),
            self::ENCODING_QUOTED_PRINTABLE => quoted_printable_encode((string) $content),
            self::ENCODING_BINARY => $content,
            default => preg_replace("/\r\n|\r|\n/", "\r\n", (string) $content),
        };
    }

    /**
     * Returns a unique ID to be used for the Content-ID header.
     *
     * @return string
     */
    protected function generateContentId()
    {
        return 'urn:uuid:' . Helper::generateUUID();
    }
}
