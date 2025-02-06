<?php

namespace App\Tests\Service\Embed;

use App\Service\Embed\EmbedVideo;
use App\Service\Embed\EmbedService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EmbedServiceTest extends KernelTestCase
{
    private EmbedService $embedService;

    public function setUp(): void
    {
        self::bootKernel();

        $this->embedService = static::getContainer()->get(EmbedService::class);
    }

    /**
     * @dataProvider provideUrls
     */
    public function testGetsEmbedData(string $url)
    {
        $embed = $this->embedService->getVideo($url);

        $this->assertInstanceOf(EmbedVideo::class, $embed);
        $this->assertNotEmpty($embed->src);
        $this->assertStringStartsWith('http', $embed->src);
        $this->assertNotEmpty($embed->thumbnail);
        $this->assertStringStartsWith('http', $embed->thumbnail);
    }

    public function provideUrls(): array
    {
        return [
            ['vimeo.com/814460460'],
            ['www.vimeo.com/943036493'],
            ['https://vimeo.com/1006675057?share=copy#t=0'],
            ['https://vimeo.com/1034003653'],
            ['https://vimeo.com/1047182474/5a2a1cb70b?share=copy'],
            ['youtube.com/watch?v=D14QGWH2CfY'],
            ['www.youtube.com/watch?si=hbrQ4ZazrdwccLTk&v=pdfGwxa5Kwc&feature=youtu.be'],
            ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            ['https://www.youtube.com/watch?v=oyYOBXDYGmY&ab_channel=Fes%21Cultura'],
            ['youtu.be/CLSXLA7jUsQ'],
            ['https://youtu.be/_crFO_jZ95o'],
            ['https://youtu.be/_URpSvDyod4?feature=shared'],
        ];
    }

    /**
     * @dataProvider provideNotUrls
     */
    public function testThrowsExceptionOnUnrecognized(string $string)
    {
        $this->expectException(\Exception::class);

        $this->embedService->getVideo($string);
    }

    public function provideNotUrls()
    {
        return [
            ['not a url'],
            ['media'],
            [''],
        ];
    }
}
