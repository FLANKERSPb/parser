<?php


namespace app\components\parser\news;

use app\components\parser\NewsPost;
use app\components\parser\NewsPostItem;
use app\components\parser\ParserInterface;
use linslin\yii2\curl\Curl;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Парсер новостей с сайта https://ryazannews.ru/
 */
class RyazanNewsRuParser implements ParserInterface
{
    const USER_ID = 2;
    const FEED_ID = 2;
    const SITE_URL = 'https://ryazannews.ru';

    public static function run(): array
    {
        $parser = new self();
        return $parser->parse();
    }

    public function parse(): array
    {
        $posts = [];

        $urlNews = self::SITE_URL . '/feed/all';
        $newsPage = $this->getPageContent($urlNews);
        $crawler = new Crawler($newsPage);
        $newsList = $crawler->filterXPath("//item");
        foreach ($newsList as $newsItem) {
            $itemCrawler = new Crawler($newsItem);
            $title = $itemCrawler->filterXPath('//title')->text();
            $date = $itemCrawler->filterXPath('//pubDate')->text();
            $description = $itemCrawler->filterXPath('//description')->text();
            $url = $itemCrawler->filterXPath('//link')->text();

            $post = new NewsPost(
                self::class,
                $title,
                $description,
                $date,
                $url,
                null
            );

            $contentPage = $this->getPageContent($url);
            if (!$contentPage) {
                continue;
            }

            $newsCrawler = new Crawler(null, $url);
            $newsCrawler->addHtmlContent($contentPage, 'UTF-8');

            $header = $newsCrawler->filterXPath('//h1[@class="post-title"]');
            if ($header->getNode(0)) {
                $this->addItemPost($post, NewsPostItem::TYPE_HEADER, $header->text(), null, null, 1);
            }
            $img = $newsCrawler->filterXPath('//img[@class="img-responsive post-image"]');
            if ($img->getNode(0)) {
                $this->addItemPost($post, NewsPostItem::TYPE_IMAGE, null, $img->attr('src'));
            }

            $newContentCrawler = (new Crawler($newsCrawler->filterXPath("//div[@class='post-content']")->html()))->filterXPath('//body')->children();

            foreach ($newContentCrawler as $content) {
                foreach ($content->childNodes as $childNode) {
                     $nodeValue = $this->clearText($childNode->nodeValue);
                     if ($childNode->nodeName == 'a' && strpos($childNode->getAttribute('href'), 'http') !== false) {

                        $this->addItemPost($post, NewsPostItem::TYPE_LINK, $nodeValue, null, $childNode->getAttribute('href'));

                    } elseif ($nodeValue) {
                        $this->addItemPost($post, NewsPostItem::TYPE_TEXT, $nodeValue);
                    }
                }
            }

            $posts[] = $post;
        }

        return $posts;
    }

    /**
     * @param NewsPost $post
     * @param int $type
     * @param string|null $text
     * @param string|null $image
     * @param string|null $link
     * @param int|null $headerLevel
     * @param string|null $youtubeId
     */
    protected function addItemPost(NewsPost $post, int $type, string $text = null, string $image = null,
                                   string $link = null, int $headerLevel = null, string $youtubeId = null): void
    {
        $post->addItem(
            new NewsPostItem(
                $type,
                $text,
                $image,
                $link,
                $headerLevel,
                $youtubeId
            ));
    }

    /**
     *
     * @param string $uri
     *
     * @return string
     * @throws RuntimeException|\Exception
     */
    private function getPageContent(string $uri): string
    {
        $curl = new Curl();
        $curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);

        $result = $curl->get($uri);
        $responseInfo = $curl->getInfo();

        $httpCode = $responseInfo['http_code'] ?? null;

        if ($httpCode >= 200 && $httpCode < 400) {
            return $result;
        }

        throw new RuntimeException("Не удалось скачать страницу {$uri}, код ответа {$httpCode}");
    }

    /**
     *
     * @param string $text
     *
     * @return string
     */
    protected function clearText(string $text): string
    {
        $text = trim($text);
        $text = htmlentities($text);
        $text = str_replace("&nbsp;",'',$text);
        $text = html_entity_decode($text);
        return $text;
    }
}