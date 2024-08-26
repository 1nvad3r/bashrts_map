<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Интерактивная карта");
$APPLICATION->SetAdditionalCSS("/ru/map/map.css");

?>
    <style type="text/css">
        .dropdown-toggle::after {
            display: none
        }
    </style>

    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=fea6d11d-324d-4800-8968-a6bbef134b29"
            type="text/javascript"></script>
    <div class="container-map" id="main" style="opacity:1">
        <div id="containerYMapsID" style="position:relative;">
            <div id="YMapsID" style="height: 510px;"></div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var expanded_cstm = true;
            if (window.innerWidth < 768) {
                expanded_cstm = false;
                // $('html').css({'overflow':'hidden'});
            }
            if (window.innerWidth < 1440) {
                $('html').css({'overflow':'hidden'});
            }

            var filterMenu = [["менее 1 млн руб", "/images/map/1.png", true],
                ["от 1 до 3 млн руб", "/images/map/2.png", true],
                ["от 3 до 5 млн руб", "/images/map/3.png", true],
                ["от 5 млн руб и выше", "/images/map/4.png", true],
            ]

            var countAll = 0;
            var orgList = [];
            var summPlannedEstimateAmount = 0;

            ymaps.ready(init);

            $('#YMapsID').css({'height': ($(window).outerHeight() - $('#containerYMapsID')[0].offsetTop) + 'px'});
            $(window).resize(function () {
                $('#YMapsID').css({'height': ($(window).outerHeight() - $('#containerYMapsID')[0].offsetTop) + 'px'});
            });

            function init() {
                ListBoxLayout = ymaps.templateLayoutFactory.createClass(
                    "<button class='my-listbox-header btn btn-success dropdown-toggle' data-toggle='dropdown'>" +
                    "{{data.title}} <span class='caret {% if state.expanded %}caret-up{% else %}{% endif %}'></span>" +
                    "</button>" +
                    "<div id= 'my-listbox'" +
                    " class='legend2 container' role='menu' aria-labelledby='dropdownMenu'" +
                    " style='display: {% if state.expanded %}block{% else %}none{% endif %};'><div class='header-filter'>Задолженность:</div></div>", {

                        build: function () {
                            // Вызываем метод build родительского класса перед выполнением
                            // дополнительных действий.
                            ListBoxLayout.superclass.build.call(this);

                            this.childContainerElement = $('#my-listbox').get(0);
                            // Генерируем специальное событие, оповещающее элемент управления
                            // о смене контейнера дочерних элементов.
                            this.events.fire('childcontainerchange', {
                                newChildContainerElement: this.childContainerElement,
                                oldChildContainerElement: null
                            });
                        },

                        // Переопределяем интерфейсный метод, возвращающий ссылку на
                        // контейнер дочерних элементов.
                        getChildContainerElement: function () {
                            return this.childContainerElement;
                        },

                        clear: function () {
                            // Заставим элемент управления перед очисткой макета
                            // откреплять дочерние элементы от родительского.
                            // Это защитит нас от неожиданных ошибок,
                            // связанных с уничтожением dom-элементов в ранних версиях ie.
                            this.events.fire('childcontainerchange', {
                                newChildContainerElement: null,
                                oldChildContainerElement: this.childContainerElement
                            });
                            this.childContainerElement = null;
                            // Вызываем метод clear родительского класса после выполнения
                            // дополнительных действий.
                            ListBoxLayout.superclass.clear.call(this);
                        }
                    });

                ListBoxItemLayout = ymaps.templateLayoutFactory.createClass(
                    '<div class="row">' +
                    '<div class="col-xs-1 col-md-1"><img style="cursor: pointer; width: 3' +
                    '5px" src="{{data.image}}"></div>' +
                    '<div class="col-xs-8 col-md-9 {% if state.selected %}row-check{% else %}row-nocheck{% endif %}">{{data.content}}</div></div>'
                );

                var myMap = new ymaps.Map('YMapsID', {
                        // center: [55.76, 37.64], // Москва
                        center: [54.76161, 56.02111], // Уфа
                        zoom: 10,
                        controls: ['searchControl', 'geolocationControl']
                    }, {
                        searchControlProvider: 'yandex#search'
                    }),
                    objectManager = new ymaps.ObjectManager({
                        // Чтобы метки начали кластеризоваться, выставляем опцию.
                        clusterize: true,
                        // ObjectManager принимает те же опции, что и кластеризатор.
                        gridSize: 64,
                        // Макет метки кластера pieChart.
                        clusterIconLayout: "default#pieChart"
                    });

                myMap.geoObjects.add(objectManager);

                // Создадим элемент управления масштабом маленького размера и добавим его на карту.
                var zoomControl = new ymaps.control.ZoomControl({
                    options: {
                        // size: 'medium'
                    }
                });

                myMap.controls.add(zoomControl);


                var listBoxItems = filterMenu.map(function (title) {
                    return new ymaps.control.ListBoxItem({
                        data: {
                            content: title[0],
                            image: title[1],
                        },
                        state: {
                            selected: title[2]
                        }
                    })
                });

                var filterListControl = new ymaps.control.ListBox({
                    data: {
                        title: 'Фильтр'
                    },
                    items: listBoxItems,
                    state: {
                        expanded: expanded_cstm,
                        filters: listBoxItems.reduce(function (filters, filter) {
                            var preset = filter.data.get('content');
                            filters[preset] = filter.isSelected();
                            return filters;
                        }, {})
                    },
                    options: {
                        layout: ListBoxLayout,
                        itemLayout: ListBoxItemLayout,
                        collapseOnBlur: false
                    },
                });
                myMap.controls.add(filterListControl);

                // Добавим отслеживание изменения признака, выбран ли пункт списка.
                filterListControl.events.add(['select', 'deselect'], function (e) {
                    var listBoxItem = e.get('target');
                    var filters = ymaps.util.extend({}, filterListControl.state.get('filters'));
                    filters[listBoxItem.data.get('content')] = listBoxItem.isSelected();
                    filterListControl.state.set('filters', filters);
                });

                var filterMonitor = new ymaps.Monitor(filterListControl.state);
                filterMonitor.add('filters', function (filters) {
                    // Применим фильтр.
                    objectManager.setFilter(getFilterFunction(filters));
                });

                function getFilterFunction(categories) {
                    return function (obj) {
                        var content = obj.properties.hintContent;
                        return categories[content]
                    }
                }

                $.ajax({
                    url: "ajax.php"
                    // url: "data.json" // тестовые данные
                }).done(function (data) {
                    objectManager.add(data);
                });
            }
        });
    </script>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>