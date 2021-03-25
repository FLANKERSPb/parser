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
use DateTimeImmutable;
use DateTimeZone;

// part 4
class CORE_InternetPortalNarodnayainiciativaRf_Parser extends ParserCore implements ParserInterface
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
            //            'itemsLimit' => 1,

            // настройки сайта
            'site'    => [
                // протокол и домен
                // (обязательный)
                'url'          => 'https://xn-----7kcbaaappfa8aiaab6b5abfccnfswff8beep3kqn.xn--p1ai',
                //                'url'         => 'https://интернет-портал-народнаяинициатива.рф',

                // кириллический URL
                'url_cyrillic' => 'https://интернет-портал-народнаяинициатива.рф',

                // использовать юзер-агенты в http запросах.
                // (опционально)
                //                'user_agent'  => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/42.0',
                'user_agent'   => 'bot',

                // часовой пояс UTC.
                // Чтобы определить часовой пояс, нужно зайти на https://time.is/Moscow и выбрать ближайший крупный город к которому относится сайт
                // узнать UTC и прописать его в формате +XX00
                // Например, Москва: '+0300', Владивосток: '+1000'
                // (опционально)
                'time_zone'    => '+0000',

                // формат даты для HTML витрины и карточки
                // (см. https://www.php.net/manual/ru/datetime.format.php)
                // d - день
                // m - месяц
                // Y - полный год
                // y - год, две цифры
                // H - час
                // i - минуты
                'date_format'  => 'd.m.Y H:i',

                // формат даты в RSS
                // (указывать только если он отличается от стандартного D, d M Y H:i:s O!)
                //                'date_format_rss' => 'D, d M Y H:i:s O',

                // пауза между запросами в секундах (включается только, если сайт начинает блокировку)
                //                'pause'       => 0,
            ],

            // настройки витрины (режим RSS)
            'rss'     => [
                // относительный URL где находится RSS
                // (обязательный)
                'url'           => '/feed/',

                // css селектор для элемента витрины (желательно от корня)
                // (обязательный)
                'element'       => 'rss > channel > item',

                // ** дальнейшие css-селекторы указываются относительно element

                // css селектор для названия элемента
                // (обязательный)
                'element-title' => 'title',

                // css селектор для ссылки
                // (обязательный)
                'element-link'  => 'link',

                // css селектор для описания элемента
                // (опционально)
                //                'element-description' => 'description',

                // css селектор для картинки элемента
                // (опционально)
                'element-image' => '',

                // css селектор для даты элемента
                // (опционально)
                //                'element-date'  => 'pubDate',
            ],


            // настройки витрины (режим HTML)
            // !!! заполняется, только при отсутствии витрины RSS !!!
            'list'    => [
                // URL где находится витрина
                // (обязательный)
                'url'                 => '/',

                // URL для навигации по страницам
                // вместо $page - подставляется номер страницы
                // например: /vitrina/page/$page
                // (опциональный)
                //                'url-page'            => '/vitrina/page/$page',

                // css селектор для контейнера витрины
                // (обязательный)
                'container'           => '.mg-posts-sec-inner:first-child',

                // css селектор для элемента витрины (относительно контейнера)
                // (обязательный)
                'element'             => 'article',

                // ** дальнейшие css-селекторы указываются относительно element

                // css селектор для ссылки на элемент !должен содержать конечный аттрибут href!
                // (обязательный + должен быть обязательный атрибут, где хранится ссылка)
                'element-link'        => '.title a[href]',

                // css селектор для названия элемента
                // (опционально)
                'element-title'       => '.title',

                // css селектор для описания элемента
                // (опционально)
                'element-description' => '',

                // css селектор !должен содержать конечный аттрибут src! для картинки элемента
                // (опционально)
                'element-image'       => '.back-img[style]',

                // css селектор для даты элемента
                // (опционально)
                //                'element-date'        => '.mg-blog-date a',
            ],

            // настройка карточки элемента
            // *** в CSS-селекторах можно указывать несколько селекторов через запятую (например, если сайт имеет несколько шаблонов карточки новости). Селекторы должны быть уникальны, иначе возможны коллизии
            'element' => [

                // css-селектор для контейнера карточки
                // (можно несколько через запятую, если есть разные шаблоны новости)
                // (обязательный)
                'container'           => '#content',

                // ** дальнейшие css-селекторы указываются относительно container

                // css-селектор для основного текста * - данные внутри (картинки, ссылки) парсятся автоматически
                // (можно несколько через запятую, если есть разные шаблоны новости)
                // (обязательный)
                'element-text'        => 'article.single',

                // css-селектор даты создания новости
                // (опционально)
                'element-date'        => '.mg-blog-post-box .mg-header .mg-blog-date',

                // css селектор для описания элемента
                // (опционально)
                'element-description' => 'article.single p:first-of-type',

                // css селектор для получения картинки
                // !должен содержать конечный аттрибут src! (например: img.main-image[src])
                // (опционально)
                'element-image'       => '.post-image img[src]',

                // css-селектор для цитаты
                // (если не заполнено, то по умолчанию берутся теги: blockquote и q)
                // (опционально)
                'element-quote'       => '',

                // игнорируемые css-селекторы (будут вырезаться из результата)
                // (можно несколько через запятую)
                // (опционально)
                'ignore-selectors'    => 'article.single p:first-of-type, .navigation, .st-post-tags',

                // css-селекторы которые будут вставлятся в начало текста новости element-text (селекторы ищутся от корня)
                // (опционально)
                'element-text-before' => '',


                // протокол и домен для карточки элемента
                // (опциональный)
                //                'url'                 => 'https://xn-----7kcbaaappfa8aiaab6b5abfccnfswff8beep3kqn.xn--p1ai',
            ]
        ];

        parent::__construct();
    }

    protected
    function getDateFromText(string $date, DateTimeZone $timeZone
    )
    : ?DateTimeImmutable {
        [$monthWord, $day, $year] = explode(' ', $date);

        $month = $this->getDateWithNumMonth($monthWord);

        //        echo $day . ' ' . $month . ' ' . $year . ' ' . date('H') . ':' . date('i');

        //        print_r($timeZone);

        //        return DateTimeImmutable::createFromFormat('d m Y H:i', $day . ' ' . $month . ' ' . date('H') . ':' . date('i'), $timeZone);
        //        return DateTimeImmutable::createFromFormat('d m Y H:i', $day . ' ' . $month . ' ' . date('H') . ':' . date('i'), new DateTimeZone('UTC'));
        return DateTimeImmutable::createFromFormat('d m Y H:i', $day . ' ' . $month . ' ' . date('H') . ':' . date('i'), $timeZone);
    }

    private
    function getDateWithNumMonth(string $date
    )
    : ?string {
        $replaceMonth = [
            'января'   => '01',
            'февраля'  => '02',
            'марта'    => '03',
            'апреля'   => '04',
            'мая'      => '05',
            'июня'     => '06',
            'июля'     => '07',
            'августа'  => '08',
            'сентября' => '09',
            'октября'  => '10',
            'ноября'   => '11',
            'декабря'  => '12',
            'январь'   => '01',
            'февраль'  => '02',
            'март'     => '03',
            'апрель'   => '04',
            'май'      => '05',
            'июнь'     => '06',
            'июль'     => '07',
            'август'   => '08',
            'сентябрь' => '09',
            'октябрь'  => '10',
            'ноябрь'   => '11',
            'декабрь'  => '12',
            'янв'      => '01',
            'фев'      => '02',
            'мар'      => '03',
            'апр'      => '04',
            'июн'      => '06',
            'июл'      => '07',
            'авг'      => '08',
            'сен'      => '09',
            'сент'     => '09',
            'окт'      => '10',
            'ноя'      => '11',
            'дек'      => '12',
        ];

        // решаем вопрос с отсутствием года
        if (!preg_match('/\d{4}/', $date))
        {
            $date .= ' ' . date('Y');
        }

        $date = trim(str_ireplace(array_keys($replaceMonth), $replaceMonth, $date));

        return $date;
    }

    public static function run()
    : array
    {
        $Parser = new self();

        $items = $Parser->getItems();
        $posts = $Parser->getCards(array_keys($items));

        // корректируем время под текущее
        if (!empty($posts))
        {
            foreach ($posts as $post)
            {
                //                echo $post->createDate;
                //                $date = $post->createDate;
                //                $date->setTime(date('H'), date('i'));
                //                $post->createDate = $date;
            }
        }

        return $posts;
    }
}