<?php
/**
 * Стартовый шаблон для разработчика
 *
 * Данный класс предназначается для нужд шаблонизации
 *
 * @author FingliGroup <info@fingli.ru>
 * @author Roman Goncharenya <goncharenya@gmail.com>
 *
 * @note   Данный код предоставлен в рамках оказания услуг, для выполнения поставленных задач по сбору и обработке данных.
 * Переработка, адаптация и модификация ПО без разрешения правообладателя является нарушением исключительных прав.
 *
 */

namespace app\components\parser\news;

use app\components\parser\NewsPostItem;
use fingli\ParserCore\ParserCore;
use app\components\parser\ParserInterface;

// part 4
class CORE_NikatvRu_Parser extends ParserCore implements ParserInterface
{
    const USER_ID = 2;
    const FEED_ID = 2;
    // поддерживаемая версия ядра
    const FOR_CORE_VERSION = '1.14';
    // дебаг-режим (только для разработки) - выводит информацию о действиях парсера
    protected const DEBUG = false;

    public function __construct()
    {
        $this->config = [
            // режимы работы парсера:
            // rss - RSS витрина
            // desktop - обычный сайт HTML
            'mode'    => 'rss',

            // максимальное количество новостей, берушихся с витрины
            // (опционально)
            //            'itemsLimit' => 100

            // настройки сайта
            'site'    => [
                // протокол и домен
                // (обязательный)
                'url'         => 'https://nikatv.ru',

                // использовать юзер-агенты в http запросах.
                // (опционально)
                'user_agent'  => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/42.0',
                //                'user_agent'  => 'bot',

                // часовой пояс UTC.
                // Чтобы определить часовой пояс, нужно зайти на https://time.is/Moscow и выбрать ближайший крупный город к которому относится сайт
                // узнать UTC и прописать его в формате +XX00
                // Например, Москва: '+0300', Владивосток: '+1000'
                // (опционально)
                'time_zone'   => '+0300',

                // формат даты для HTML витрины и карточки
                // (см. https://www.php.net/manual/ru/datetime.format.php)
                // d - день
                // m - месяц
                // Y - полный год
                // y - год, две цифры
                // H - час
                // i - минуты
                'date_format' => 'd.m.Y H:i',

                // формат даты в RSS
                // (указывать только если он отличается от стандартного D, d M Y H:i:s O!)
                //                'date_format_rss' => 'D, d M Y H:i:s O',
            ],

            // настройки витрины (режим RSS)
            'rss'     => [
                // относительный URL где находится RSS
                // (обязательный)
                'url'           => '/rss/news',

                // css селектор для элемента витрины (желательно от корня)
                // (обязательный)
                'element'       => 'rss > channel > item',

                // css селектор для названия элемента (относительно элемента)
                // (обязательный)
                'element-title' => 'title',

                // css селектор для ссылки (относительно элемента)
                // (обязательный)
                'element-link'  => 'link',

                // css селектор для описания элемента (относительно элемента)
                // (заполняется только, если отсутствует в карточке)
                //                'element-description' => 'description',

                // css селектор для картинки элемента (относительно элемента)
                // (заполняется только, если отсутствует в карточке)
                //                'element-image' => 'enclosure[url]',

                // css селектор для даты элемента (относительно элемента)
                // (заполняется только, если отсутствует в карточке)
                'element-date'  => 'pubDate',
            ],

            // настройка карточки элемента
            'element' => [

                // css-селектор для контейнера карточки
                // (все дальнейшие пути строятся относительно этого контейнера)
                // (обязательный)
                'container'           => '.news.news-one',

                // css-селектор для основного текста
                // (для заполнения модели NewsPostItem)
                // (обязательный)
                'element-text'        => '.redactor',

                // css-селектор для получения даты создания новости
                // (заполняется только, если отсутствует в витрине)
                'element-date'        => '',

                // css селектор для описания элемента (относительно элемента)
                // (заполняется только, если отсутствует в витрине)
                'element-description' => '.redactor > p:first-of-type',

                // css селектор для получения картинки
                // !должен содержать конечный аттрибут src! (например: img.main-image[src])
                // (заполняется только, если отсутствует в витрине)
                'element-image'       => '.redactor img:first-of-type[src]',

                // css-селектор для цитаты
                // (если не заполнено, то по умолчанию берутся теги: blockquote и q)
                // (опционально)
                'element-quote'       => 'blockquote',

                // игнорируемые css-селекторы
                // (можно через запятую)
                // (опционально)
                'ignore-selectors'    => '.redactor > p:first-of-type, figcaption',
            ]
        ];

        parent::__construct();
    }

    public static function run()
    : array
    {
        $Parser = new self();

        $items = $Parser->getItems();
        $posts = $Parser->getCards(array_keys($items));

        if (!empty($posts))
        {
            foreach ($posts as $post)
            {
                if (!empty($post->items))
                {
                    foreach ($post->items as $postItem)
                    {
                        if ($postItem->type == NewsPostItem::TYPE_TEXT)
                        {
                            // вырезаем из текста большие зазоры
                            $postItem->text = preg_replace("/[\r\n ]{2,}/", "\n\n", $postItem->text);

                            // вырезаем текст, который найден в лиде
                            if (strpos($post->description, $postItem->text) !== false)
                            {
                                //                                $postItem->text = 'fake';
                                $post->description = $post->title;
                            }
                        }
                    }
                }
            }
        }

        return $posts;
    }
}