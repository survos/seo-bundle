<?php

declare(strict_types=1);

namespace Survos\SeoBundle\Twig\Extension;

use Survos\SeoBundle\Service\SeoService;
use Symfony\Component\String\AbstractString;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use Twig\TwigFunction;
use function Symfony\Component\String\u;

/**
 * SEO related Twig helpers.
 */
final class SeoExtension extends AbstractExtension
{

    const MAX_TITLE_LENGTH=80;
    public function __construct(
        private readonly SeoService $seoService,
    )
    {

    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('seo_title', fn(string $value) => $this->process('Title', $value)),
            new TwigFilter('seo_description', fn(string $value) => $this->process('Description', $value)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('seo_config', fn($key) => $this->seoService->getConfigValue($key) ?? "??$key"),
        ];

    }

    private function prepareStr(string $str): AbstractString
    {
        return u(strip_tags($str))->trim();
    }

    private function process(string $metaElement, string $value): string
    {
        $str = $this->prepareStr($value);
        $brandingStr = u((string)$this->seoService->getConfigValue('branding'));
        $length = $str->length();
        [$min, $max] = $this->seoService->getMinMax($metaElement);

        // Nominal case
        if ($length >= $min && $length <= $max) {
            // Is there enough place for the branding?
            if (($length + $brandingStr->length() <= self::MAX_TITLE_LENGTH)) {
                $str = $str->ensureEnd($brandingStr->toString());
            }
            return $str->toString();
        }

        // Title too short, we add the branding
        if ($length < $min) {
            $str = $str->ensureEnd($brandingStr->toString());
        }

        // Title too long, we cup
        if ($length > $max) {
            $str = $str->truncate($max);
        }

        return $str->toString();
    }

}
