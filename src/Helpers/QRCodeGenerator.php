<?php

namespace Saleh7\Zatca\Helpers;

use InvalidArgumentException;
use Saleh7\Zatca\Tag;
class QRCodeGenerator
{
    /**
     * @var Tag[] Array of Tag instances.
     */
    protected array $tags = [];

    /**
     * Private constructor.
     *
     * Filters the input to include only valid Tag instances.
     *
     * @param Tag[] $tags Array of Tag objects.
     *
     * @throws InvalidArgumentException if no valid Tag instances are provided.
     */
    private function __construct(array $tags)
    {
        $this->tags = array_filter($tags, function ($tag) {
            return $tag instanceof Tag;
        });

        if (count($this->tags) === 0) {
            throw new InvalidArgumentException('Malformed data structure: no valid Tag instances found.');
        }
    }

    /**
     * Encodes the TLV string into Base64.
     *
     * @return string Base64 encoded TLV string.
     */
    public function encodeBase64(): string
    {
        return base64_encode($this->encodeTLV());
    }

    /**
     * Create a QRCodeGenerator instance from an array of Tag objects.
     *
     * @param Tag[] $tags Array of Tag objects.
     *
     * @return QRCodeGenerator
     */
    public static function createFromTags(array $tags): QRCodeGenerator
    {
        return new self($tags);
    }

    /**
     * Encodes the tags into a TLV (Tag-Length-Value) formatted string.
     *
     * @return string TLV encoded string.
     */
    public function encodeTLV(): string
    {
        return implode('', array_map(function (Tag $tag) {
            return (string) $tag;
        }, $this->tags));
    }


}
