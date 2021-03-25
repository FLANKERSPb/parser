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

use fingli\ParserCore\ParserCore;
use app\components\parser\ParserInterface;

// part 4
class CORE_360tvRU_Parser extends ParserCore implements ParserInterface
{
    const USER_ID = 2;
    const FEED_ID = 2;
    // поддерживаемая версия ядра
    // (НЕ ИЗМЕНЯТЬ САМОСТОЯТЕЛЬНО!)
    const FOR_CORE_VERSION = '1.14';
    // дебаг-режим (только для разработки) - выводит информацию о действиях парсера
    // 0 - отключен
    // 1 - включен
    // 2 - включен (очень подробный режим)
    // 3 - режим "зануда"
    protected const DEBUG = 0;

    public function __construct()
    {
        $this->config = [
            // режимы работы парсера:
            // rss - RSS витрина
            // desktop - обычный сайт HTML
            'mode'    => 'desktop',

            // максимальное количество новостей, берушихся с витрины
            // ИСПОЛЬЗУЕТСЯ ТОЛЬКО В РЕЖИМЕ DEBUG
            // в остальных случаях жестко задается ядром
            //
            // не забывайте отключать лимит при сдаче парсера!
            //            'itemsLimit' => 10,

            // настройки сайта
            'site'    => [
                // протокол и домен
                // (обязательный)
                'url'         => 'https://360tv.ru',

                // кириллический URL
                //            'url_cyrillic' => 'https://интернет-портал-народнаяинициатива.рф',

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

                // пауза между запросами в секундах (включается только, если сайт начинает блокировку)
                //                'pause'       => 0,

                // заменяем переводы строк пробелами (есть сайты, где не ставят пробелы после перевода строки)
                // 'transform_new_line_to_space' => true

                // кодировка сайта (если определяется некорректно)
                //                'encoding'    => 'UTF-8',

                // отмена проверки на безопасность SSL со стороны CURL
                //                'insecure' => true,
            ],

            // настройки витрины (режим HTML)
            // !!! заполняется, только при отсутствии витрины RSS !!!
            'list'    => [
                // URL где находится витрина
                // (обязательный)
                'url'                 => '/news/',

                // URL для навигации по страницам
                // вместо $page - подставляется номер страницы
                // например: /vitrina/page/$page
                // (опциональный)
                //                'url-page'            => '/vitrina/page/$page',

                // css селектор для контейнера витрины
                // (обязательный)
                'container'           => '.tag-category',

                // css селектор для элемента витрины (относительно контейнера)
                // (обязательный)
                'element'             => '.g360_col',

                // ** дальнейшие css-селекторы указываются относительно element

                // css селектор для ссылки на элемент !должен содержать конечный аттрибут href!
                // (обязательный + должен быть обязательный атрибут, где хранится ссылка)
                'element-link'        => '.g360_grid-tile[href]',

                // css селектор для названия элемента
                // (опционально)
                'element-title'       => '.g360_grid-tile_title',

                // css селектор для описания элемента
                // (опционально)
                'element-description' => '',

                // css селектор !должен содержать конечный аттрибут src! для картинки элемента
                // (опционально)
                'element-image'       => '.g360_grid-tile_image img[src]',

                // css селектор для даты элемента
                // (опционально)
                //                'element-date'        => '.g360_grid-tile_date',
            ],

            // настройка карточки элемента
            // *** в CSS-селекторах можно указывать несколько селекторов через запятую (например, если сайт имеет несколько шаблонов карточки новости). Селекторы должны быть уникальны, иначе возможны коллизии
            'element' => [

                // css-селектор для контейнера карточки
                // (можно несколько через запятую, если есть разные шаблоны новости)
                // (обязательный)
                'container'           => 'article.content-block',

                // заголовок (если не получилось взять из витрины)
                'element-title'       => '',

                // ** дальнейшие css-селекторы указываются относительно container

                // css-селектор для основного текста * - данные внутри (картинки, ссылки) парсятся автоматически
                // (можно несколько через запятую, если есть разные шаблоны новости)
                // (обязательный)
                'element-text'        => '.news-item-text .news-text',

                // css-селектор даты создания новости
                // (опционально)
                'element-date'        => '.iv-pubdate',

                // css селектор для описания элемента
                // (опционально)
                'element-description' => '.news-item-text .news-lead',

                // css селектор для получения картинки
                // !должен содержать конечный аттрибут src! (например: img.main-image[src])
                // (опционально)
                'element-image'       => '',

                // css-селектор для цитаты
                // (если не заполнено, то по умолчанию берутся теги: blockquote и q)
                // (опционально)
                'element-quote'       => '.block-quote_text',

                // игнорируемые css-селекторы (будут вырезаться из результата)
                // (можно несколько через запятую)
                // (опционально)
                'ignore-selectors'    => '',

                // css-селекторы которые будут вставлятся в начало текста новости element-text (селекторы ищутся от корня, т.е. не зависят от container)
                // (опционально)
                'element-text-before' => '',

                // css-селекторы которые будут вставлятся в конец текста новости element-text (селекторы ищутся от корня, т.е. не зависят от container)
                // (опционально)
                'element-text-after'  => '',

                // протокол и домен для карточки элемента
                // (опциональный)
                'url'                 => '',
            ]
        ];

        parent::__construct();
    }

    //    protected function getCardTextHtml(string $html)
    //    : string {
    //        // добавляем css-селекторы в начало и/или в конец текста
    //        $html = $this->getHtmlWithInsertedSelectors($html);
    //
    //        // вырезаем игнорируемые теги
    //        $html = $this->getHtmlWithoutIgnoredSelectors($html);
    //
    //        // подменяем цитаты
    //        $html = $this->getHtmlWithSubstitutedQuotes($html);
    //
    //        $html = str_replace('<p></p>', '', $html);
    //        $html = str_replace('<p><br></p>', '', $html);
    //        $html = str_replace('<p>&nbsp;</p>', '', $html);
    //        $html = str_replace('<p> </p>', '', $html);
    //        $html = str_replace('<p> </p>', '', $html);
    //
    //        // оставляем только нужные теги
    //        $html = $this->stripTags($html, $this->allowedTags);
    //
    //        return $html;
    //    }

    public static function run()
    : array
    {
        $Parser = new self();

        $items = $Parser->getItems();
        $posts = $Parser->getCards(array_keys($items));

        //        if (!empty($posts))
        //        {
        //            foreach ($posts as $post)
        //            {
        //                if (!empty($post->items))
        //                {
        //                    foreach ($post->items as $postItem)
        //                    {
        //                        // вырезаем из текста большие зазоры
        //                        if ($postItem->type == NewsPostItem::TYPE_TEXT)
        //                        {
        //                            $postItem->text = preg_replace("/[\r\n ]{2,}/", "\n\n", $postItem->text);
        //                        }
        //                    }
        //                }
        //            }
        //        }

        // вырезаем дубли desc vs text
        //        if (!empty($posts))
        //        {
        //            foreach ($posts as $post)
        //            {
        //                $postItems = [];
        //
        //                if (!empty($post->items))
        //                {
        //                    foreach ($post->items as $postItem)
        //                    {
        //                        if (!empty($postItem->text) &&
        //                            strpos(trim($post->description), trim($postItem->text)) === false)
        //                        {
        //                            $postItems[] = $postItem;
        //                        }
        //                    }
        //                }
        //
        //                $post->items = $postItems;
        //            }
        //        }

        return $posts;
    }
}