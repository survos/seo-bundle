<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Survos\SeoBundle\Service\SeoService;

use function PHPUnit\Framework\assertEquals;

class SeoDataCollectorTest extends TestCase
{
    public function testValid(): void
    {
        $response = new Symfony\Component\HttpFoundation\Response();
        $html = file_get_contents(__DIR__.'/Fixtures/sandesa.html');
        $response->setContent($html ?: null);
        $config = [
            'minTitleLength' => SeoService::DEFAULT_MIN_TITLE,
            'maxTitleLength' => SeoService::DEFAULT_MAX_TITLE,
            'minDescriptionLength' => SeoService::DEFAULT_MIN_DESCRIPTION,
            'maxDescriptionLength' => SeoService::DEFAULT_MAX_DESCRIPTION,
        ];
        $seo = new SeoService($config);

        $request = new Symfony\Component\HttpFoundation\Request();
        $dataCollector = new Survos\SeoBundle\DataCollector\SeoCollector($seo);
        $dataCollector->collect($request, $response);
        $titleInfo = $dataCollector->getTitle();

        assertEquals($titleInfo['size'], 13);
        assertEquals($titleInfo['value'], 'Sandesa, Inc.');
        assertEquals($titleInfo['status'], 'yellow');

        $descriptionInfo = $dataCollector->getDescription();

        assertEquals($descriptionInfo['size'], 23);
        assertEquals($descriptionInfo['value'], 'Sandesa, a description.');
        assertEquals($descriptionInfo['status'], 'green');

        $html = file_get_contents(__DIR__.'/Fixtures/no-title.html') ?: null;
        $response->setContent($html);
        $dataCollector->reset();
        // Not the FQCN: getName() becomes the profiler panel's URL segment, so the
        // class name would put the panel at a url-encoded namespace path.
        assertEquals('survos_seo', $dataCollector->getName());
        $dataCollector->collect($request, $response);
        $titleInfo = $dataCollector->getTitle();
        assertEquals($titleInfo['size'], 0);
        assertEquals($titleInfo['status'], 'red');
    }
}
