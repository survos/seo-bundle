<?php

declare(strict_types=1);

namespace Survos\SeoBundle\DataCollector;

use Survos\SeoBundle\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\String\u;

/**
 * Reports the page's <title> and meta description against the configured length
 * bounds, in the toolbar and in a panel.
 *
 * Original sketch from strangebuzz.com's data-collector article.
 */
final class SeoCollector extends AbstractDataCollector
{
    private const MAX_PANEL_WIDTH = 50;
    private const CLASS_ERROR = 'red';
    private const CLASS_WARNING = 'yellow';
    private const CLASS_OK = 'green';

    public function __construct(private readonly SeoService $seoService)
    {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        // Only HTML has a <title> to measure. Crawling a JSON or binary response finds
        // nothing and reports it as a missing title, which is a false alarm on every
        // API route in the app.
        if (!str_contains((string) $response->headers->get('Content-Type', 'text/html'), 'text/html')) {
            $this->data = ['applicable' => false];

            return;
        }

        $crawler = new Crawler((string) $response->getContent());

        $this->data = [
            'applicable' => true,
            'title' => $this->measure(
                $this->firstText($crawler->filter('title')),
                ...$this->seoService->getMinMax('Title'),
            ),
            'description' => $this->measure(
                $crawler->filterXPath('//meta[@name="description"]')->count() > 0
                    ? (string) $crawler->filterXPath('//meta[@name="description"]')->attr('content')
                    : null,
                ...$this->seoService->getMinMax('Description'),
            ),
        ];
    }

    public function isApplicable(): bool
    {
        return $this->data['applicable'] ?? false;
    }

    /** @return array<string, mixed> */
    public function getTitle(): array
    {
        return $this->data['title'] ?? [];
    }

    /** @return array<string, mixed> */
    public function getDescription(): array
    {
        return $this->data['description'] ?? [];
    }

    /** True when either measurement is outside its bounds — drives the toolbar colour. */
    public function hasProblem(): bool
    {
        foreach ([$this->getTitle(), $this->getDescription()] as $measurement) {
            if (self::CLASS_OK !== ($measurement['status'] ?? self::CLASS_OK)) {
                return true;
            }
        }

        return false;
    }

    public static function getTemplate(): ?string
    {
        return '@SurvosSeo/seo_collector.html.twig';
    }

    /**
     * AbstractDataCollector defaults this to static::class, which becomes the panel's
     * URL segment — so without the override the panel lives at a url-encoded FQCN.
     * Matches the id attribute on the data_collector tag.
     */
    public function getName(): string
    {
        return 'survos_seo';
    }

    /**
     * @return array{value: string|null, size: int, min: int, max: int, status: string}
     */
    private function measure(?string $value, int $min, int $max): array
    {
        if (null === $value || '' === trim($value)) {
            return ['value' => null, 'size' => 0, 'min' => $min, 'max' => $max, 'status' => self::CLASS_ERROR];
        }

        $string = u(trim($value));
        $size = $string->length();

        return [
            'value' => $string->wordwrap(self::MAX_PANEL_WIDTH)->toString(),
            'size' => $size,
            'min' => $min,
            'max' => $max,
            'status' => ($size >= $min && $size <= $max) ? self::CLASS_OK : self::CLASS_WARNING,
        ];
    }

    private function firstText(Crawler $crawler): ?string
    {
        return $crawler->count() > 0 ? $crawler->text() : null;
    }
}
