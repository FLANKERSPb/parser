<?php
/**
 *
 * @author MediaSfera <info@media-sfera.com>
 * @author FingliGroup <info@fingli.ru>
 * @author Vitaliy Moskalyuk <flanker@bk.ru>
 *
 * @note Данный код предоставлен в рамках оказания услуг, для выполнения поставленных задач по сбору и обработке данных. Переработка, адаптация и модификация ПО без разрешения правообладателя является нарушением исключительных прав.
 *
 */

namespace app\components\parser\news;


use app\components\mediasfera\MediasferaNewsParser;
use app\components\mediasfera\NewsPostWrapper;
use app\components\parser\NewsPostItem;
use app\components\parser\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @fullrss
 */
class ProvinceRuIvanovoParser extends MediasferaNewsParser implements ParserInterface
{
    public const USER_ID = 2;
    public const FEED_ID = 2;

    public const NEWS_LIMIT = 100;

    public const SITE_URL = 'https://www.province.ru/';
    //public const SITE_URL = 'https://www.province.ru/ivanovo/';
    public const NEWSLIST_URL = 'https://www.province.ru/ivanovo/component/obrss/astrakhan-id-provintsiya.feed';

    public const DATEFORMAT = 'D, d M Y H:i:s O';

    public const NEWSLIST_POST = '//rss/channel/item';
    public const NEWSLIST_TITLE = '//title';
    public const NEWSLIST_LINK = '//link';
    public const NEWSLIST_DATE = '//pubDate';
    public const NEWSLIST_CONTENT = '//yandex:full-text';
    public const NEWSLIST_IMG = '//image';

    public const ARTICLE_IMAGE = '.itemImageBlock .itemImage img';
    public const ARTICLE_BREAKPOINTS = [];

    protected static NewsPostWrapper $post;

    public static function run(): array
    {
        $posts = [];

        $listContent = self::getPage(self::NEWSLIST_URL);

        if(strpos($listContent, 'xmlns:yandex') === false) {
            $listContent = str_replace('<rss', '<rss xmlns:yandex="http://news.yandex.ru" ', $listContent);
        }

        $listCrawler = new Crawler($listContent);

        $listCrawler->filterXPath(self::NEWSLIST_POST)->slice(0, self::NEWS_LIMIT)->each(function (Crawler $node) use (&$posts) {

            self::$post = new NewsPostWrapper();

            self::$post->title = self::getNodeData('text', $node, self::NEWSLIST_TITLE);
            self::$post->original = self::getNodeData('text', $node, self::NEWSLIST_LINK);
            self::$post->createDate = self::getNodeDate('text', $node, self::NEWSLIST_DATE);
            self::$post->image = self::getNodeImage('text', $node, self::NEWSLIST_IMG);

            $content = self::getNodeData('text', $node, self::NEWSLIST_CONTENT);

            $contentCrawler = new Crawler('<body><div>' . $content . '</div></body>');

            self::parse($contentCrawler->filter('body > div'));

            $articleContent = self::getPage(self::$post->original);

            if (!empty($articleContent)) {

                $articleCrawler = new Crawler($articleContent);

                $image = self::getNodeImage('src', $articleCrawler, self::ARTICLE_IMAGE);

                if($image) {
                    self::$post->image = $image;
                }
            }

            $newsPost = self::$post->getNewsPost();

            foreach ($newsPost->items as $key => $item) {

                $text = ltrim(html_entity_decode($item->text), static::CHECK_CHARS);

                if($item->type == NewsPostItem::TYPE_TEXT) {
                    if(strpos($text, '«') === 0 && substr_count($text, ' - ')) {
                        $newsPost->items[$key]->type = NewsPostItem::TYPE_QUOTE;
                    }
                }
            }

            $posts[] = $newsPost;

        });

        return $posts;
    }
}
